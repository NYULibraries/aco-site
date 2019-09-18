/**
 * task
 *
 * Copyright (c) 2014 New York University, contributors
 * Licensed under the MIT license.
 */

'use strict';

module.exports = async function (grunt) {
  grunt.registerTask('writeHTML', 'Write HTML files', async function () {
    const { basename, extname, normalize } = require('path');
    const { readdirSync } = require('fs');
    const root = normalize(__dirname + '/../..');
    const transform = require('../transform');
    /** force task into asynchronous mode */
    this.async();
    try {
      let pages = {};
      if (grunt.file.isDir(`${root}/source/json/pages`)) {
        let sources = readdirSync(`${root}/source/json/pages`);
        while (sources.length) {
          let item = sources.pop();
          if (extname(item) === '.js') {
            pages[item] = await require(`${root}/source/json/pages/${item}`)();
          }
        }
      }
      /** copy source JavaScript files to build */
      grunt.file.recurse(`${root}/source/js/`, (abspath, rootdir, subdir, filename) => {
        if (extname(filename) === '.js') {
          grunt.file.copy(abspath, `build/js/${filename}`);
        }
      });

      /** iterate pages and transform in HTML */
      for (const page in pages) {
        try {
          const task = basename(page, '.js');
          await transform({
            route: pages[page].route,
            template: `${root}/source/views/${task}.mustache`,
            data: pages[page],
            pages: pages,
            task: task
          });
        }
        catch (err) {
          throw err;
        }
      }
    }
    catch (err) {
      console.log(err);
      grunt.log.write('Unknown error').error();
    }
  });

};