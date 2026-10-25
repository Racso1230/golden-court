# syntax=docker/dockerfile:1

# Production image for the Golden Court concept site.
# Stages: PHP packages -> front-end build -> SSR server -> the image that runs.
# ~/stack/compose.yaml on the VPS builds two images from this file: `runtime`
# (the site, which the queue worker and scheduler containers reuse with a
# different command) and `ssr` (the server-side rendering process). Caddy sits
# in front and handles HTTPS; PostgreSQL runs in its own container.

# ---- 1. PHP packages --------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Install first without the app code so Docker can cache this slow step.
# --ignore-platform-reqs is safe: the image that runs the code (stage 4) has
# the right PHP and extensions.
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction \
    --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts --ignore-platform-reqs

# ---- 2. Front-end build -----------------------------------------------------
# The Wayfinder Vite plugin runs `php artisan` while building, so this stage
# needs PHP as well as Node. It is thrown away afterwards; only the built
# files are kept.
FROM php:8.5-cli-bookworm AS assets
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci
COPY --from=vendor /app ./

# Throwaway values so Laravel can boot during the build. They are not copied
# into the final image; the real APP_KEY is supplied when the container runs.
ENV APP_ENV=production \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    LARAVEL_BYPASS_ENV_CHECK=1
# The browser bundle, then the server-side rendering bundle.
RUN npm run build && npm run build:ssr
# Keep only what the render server needs at run time (vue and friends are
# regular dependencies, the build tools are not).
RUN npm prune --omit=dev --ignore-scripts

# ---- 3. Server-side rendering ----------------------------------------------
# Node runs the SSR bundle (Inertia 3 names it after the entry file, so it is
# bootstrap/ssr/app.js here). It has no public port: only the site container
# calls it, over the private Docker network (its HTTP API includes /shutdown).
FROM node:22-bookworm-slim AS ssr
WORKDIR /app
COPY --from=assets /app/package.json ./package.json
COPY --from=assets --chown=node:node /app/node_modules ./node_modules
COPY --from=assets --chown=node:node /app/bootstrap/ssr ./bootstrap/ssr
USER node
EXPOSE 13714
CMD ["node", "bootstrap/ssr/app.js"]

# ---- 4. The image that runs -------------------------------------------------
FROM dunglas/frankenphp:1-php8.5-bookworm AS runtime

# Refuse to build on a PHP that still has the PHP2Shell flaws
# (CVE-2026-109618 / CVE-2026-109619, fixed in 8.5.12).
RUN php -r 'if (version_compare(PHP_VERSION, "8.5.12", "<")) { fwrite(STDERR, "PHP " . PHP_VERSION . " is older than 8.5.12 (PHP2Shell). Pull a newer base image.\n"); exit(1); }'

# pdo_pgsql talks to PostgreSQL. (The cube and earthdistance extensions the
# search uses live in the database server, and the migrations enable them.)
RUN install-php-extensions pdo_pgsql opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "expose_php=Off" > "$PHP_INI_DIR/conf.d/zz-hardening.ini"

# Plain HTTP on 8080 inside the container. The Caddy proxy in front of it
# handles HTTPS, so this port is never exposed to the internet.
ENV SERVER_NAME=:8080

# Run as a normal user, not root. Caddy (inside FrankenPHP) needs somewhere
# writable for its state.
RUN useradd --system --uid 10001 --no-create-home app \
    && mkdir -p /data/caddy /config/caddy \
    && chown -R app:app /data/caddy /config/caddy

WORKDIR /app
COPY --from=vendor --chown=app:app /app /app
COPY --from=assets --chown=app:app /app/public/build /app/public/build
# Inertia only calls the render server when it can find the SSR bundle in the
# site's own base path. The bundle is not run here (the `ssr` container does
# that); it just has to exist for Inertia to treat rendering as available.
COPY --from=assets --chown=app:app /app/bootstrap/ssr /app/bootstrap/ssr
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R app:app storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

USER app
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan package:discover --ansi

EXPOSE 8080

# The entrypoint applies migrations and warms the caches, then hands over to
# the base image's own start-up (or to the command compose gives the queue
# worker and scheduler containers).
ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
