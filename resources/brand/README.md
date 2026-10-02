# Brand assets

The Golden Court mark is a gold padel ball with a white star: the sport, the
ratings, and padel's "golden point". `mark.svg` is the source of truth; the
Vue component `resources/js/components/AppLogoIcon.vue` draws the same shape.

| File | Purpose |
| --- | --- |
| `mark.svg` | The mark, copied to `public/favicon.svg` |
| `touch-icon.svg` | The mark on white, for Apple touch and manifest icons |
| `og-image.html` | The 1200×630 sharing card |
| `make-ico.mjs` | Wraps PNGs in `favicon.ico` with Node built-ins only |
| `build.sh` | Regenerates everything in `public/` |

Regenerate after changing the mark (Chrome and Node required; set `CHROME`
to use Edge instead):

```sh
bash resources/brand/build.sh
```

Colours: gold `#eaa000` with a `#ffd162` to `#a86a00` radial gradient,
near-black `#1d1713`, warm grey `#69625d`.
