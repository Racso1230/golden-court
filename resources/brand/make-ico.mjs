// Wraps PNG images in a single .ico file using only Node built-ins.
// Usage: node resources/brand/make-ico.mjs out.ico a.png b.png ...
import { readFileSync, writeFileSync } from 'node:fs';

const [out, ...inputs] = process.argv.slice(2);

if (!out || inputs.length === 0) {
    console.error('Usage: node make-ico.mjs out.ico image.png [image.png ...]');
    process.exit(1);
}

const images = inputs.map((path) => {
    const data = readFileSync(path);
    // PNG IHDR: width and height are big-endian uint32 at bytes 16 and 20.
    return {
        data,
        width: data.readUInt32BE(16),
        height: data.readUInt32BE(20),
    };
});

const header = Buffer.alloc(6);
header.writeUInt16LE(0, 0); // reserved
header.writeUInt16LE(1, 2); // type: icon
header.writeUInt16LE(images.length, 4);

let offset = 6 + 16 * images.length;
const entries = images.map(({ data, width, height }) => {
    const entry = Buffer.alloc(16);
    entry.writeUInt8(width >= 256 ? 0 : width, 0);
    entry.writeUInt8(height >= 256 ? 0 : height, 1);
    entry.writeUInt8(0, 2); // palette size
    entry.writeUInt8(0, 3); // reserved
    entry.writeUInt16LE(1, 4); // colour planes
    entry.writeUInt16LE(32, 6); // bits per pixel
    entry.writeUInt32LE(data.length, 8);
    entry.writeUInt32LE(offset, 12);
    offset += data.length;
    return entry;
});

writeFileSync(
    out,
    Buffer.concat([header, ...entries, ...images.map(({ data }) => data)]),
);
console.log(`Wrote ${out} with ${images.length} image(s).`);
