#!/bin/sh
# Container start-up: apply any new migrations, warm the config and view
# caches, then start whatever the container was asked to run (the web server by
# default, or the queue worker / scheduler when compose passes a command).
# `set -e` stops everything if a step fails, so a broken migration never
# serves traffic.
#
# Only one container should run migrations at a time. The web container does
# (the default); compose sets RUN_MIGRATIONS=false on the queue worker and the
# scheduler, which wait for the web container to be healthy anyway.
set -e

cd /app

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

php artisan config:cache
php artisan view:cache

exec docker-php-entrypoint "$@"
