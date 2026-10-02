#!/usr/bin/env bash
# Regenerates the brand assets in public/ from the sources in this folder.
# Needs Google Chrome (or Edge: set CHROME) and Node; no npm packages.
set -euo pipefail
cd "$(dirname "$0")/../.."

CHROME="${CHROME:-/c/Program Files/Google/Chrome/Application/chrome.exe}"
ROOT="$(pwd -W 2>/dev/null || pwd)"
TMP=resources/brand/tmp
mkdir -p "$TMP" public/images

screenshot() { # screenshot <page> <width> <height> <out> [extra chrome flags...]
    local page=$1 width=$2 height=$3 out=$4
    shift 4
    "$CHROME" --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 \
        --virtual-time-budget=5000 "$@" --window-size="$width,$height" \
        --screenshot="$ROOT/$out" "file:///$ROOT/$page" >/dev/null 2>&1
}

# Headless Chrome lays out at least ~500px wide and crops the screenshot to the
# window size from the top-left corner, so icons are pinned there at exact size.
icon() { # icon <svg> <size> <out> [transparent]
    local svg=$1 size=$2 out=$3 page="$TMP/icon-$2.html" flags=()
    printf '<!doctype html><body style="margin:0;background:transparent"><img src="%s" style="position:absolute;top:0;left:0;width:%spx;height:%spx">' \
        "file:///$ROOT/$svg" "$size" "$size" > "$page"
    [ "${4:-}" = transparent ] && flags=(--default-background-color=00000000)
    screenshot "$page" "$size" "$size" "$out" "${flags[@]}"
}

cp resources/brand/mark.svg public/favicon.svg

for size in 16 32 48; do
    icon resources/brand/mark.svg "$size" "$TMP/favicon-$size.png" transparent
done
node resources/brand/make-ico.mjs public/favicon.ico "$TMP/favicon-16.png" "$TMP/favicon-32.png" "$TMP/favicon-48.png"

icon resources/brand/touch-icon.svg 180 public/apple-touch-icon.png
icon resources/brand/touch-icon.svg 192 public/images/icon-192.png
icon resources/brand/touch-icon.svg 512 public/images/icon-512.png
screenshot resources/brand/og-image.html 1200 630 public/images/og-default.png

echo "Brand assets written to public/."
