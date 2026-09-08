#!/usr/bin/env node
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Minimal AMD build script for qbank_tagassistant, mirroring the two things Moodle core's
 * real Grunt "amd" task does to amd/src files: (1) inject the module id as the first define()
 * argument, (2) minify with a sourcemap. Run via `npm run build:amd`.
 *
 * @copyright  2026 TKorner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
    const named = src.replace(/define\(\[/, `define("${component}/${modulename}", [`);

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
