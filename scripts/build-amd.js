#!/usr/bin/env node
/**
 * Minimal AMD build script for qbank_tagassistant, mirroring the two things Moodle core's
 * real Grunt "amd" task does to amd/src files: (1) inject the module id as the first define()
 * argument, (2) minify with a sourcemap. Run via `npm run build:amd`.
 */
'use strict';

const fs = require('fs');
const path = require('path');
const terser = require('terser');

const component = 'qbank_tagassistant';
const modulename = 'tag_chips';
const srcpath = path.join(__dirname, '..', 'amd', 'src', `${modulename}.js`);
const buildpath = path.join(__dirname, '..', 'amd', 'build', `${modulename}.min.js`);
const mappath = `${buildpath}.map`;

async function build() {
    const src = fs.readFileSync(srcpath, 'utf8');
    const named = src.replace(/^define\(\[/, `define("${component}/${modulename}", [`);

    const result = await terser.minify(named, {
        compress: true,
        mangle: true,
        sourceMap: {
            filename: `${modulename}.min.js`,
            url: `${modulename}.min.js.map`,
        },
    });

    if (result.error) {
        throw result.error;
    }

    fs.writeFileSync(buildpath, result.code + '\n');
    fs.writeFileSync(mappath, result.map);
    console.log(`Built ${buildpath}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(1);
});
