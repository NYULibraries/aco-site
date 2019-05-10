/*
 * grunt-contrib-writeHTML
 * https://github.com/NYULibraries/awdl_site
 *
 * Copyright (c) 2014 New York University, contributors
 * Licensed under the MIT license.
 */

'use strict';

module.exports = async function (grunt) {
  async function writeHTML () {
    try {
      console.log('We are doing something here');
    }
    catch (e) {
      console.log (e);
      grunt.log.error();
      grunt.fail.warn('Unable to Yay!', e);
    }
  }
  grunt.registerTask('writeHTML', 'Write HTML files from pages.js file.', async function () {
    var path = require('path');
    var _ = require('underscore');
    var fs = require('fs') ;
    /** @TODO: find if there is a more elegant solution to this */
    var root = path.normalize(__dirname + '/../../..');
    var transform = require('transform');

    async function source (sourcePath) {
      try {
        const pageFn = require(sourcePath);
        let response = await pageFn();
        return response;
      } catch(e) {
        console.log(e);
      }
    }

    /** force task into asynchronous mode and grab a handle to the "done" function. */
    var done = this.async();

    try {
      var pages = {};
      /** load configuration */
      const projectConfigurationFile = `${root}/source/json/conf.js`;
      let conf = require(projectConfigurationFile);

      if (grunt.file.isDir(`${root}/source/json/pages`)) {
        var sources = fs.readdirSync(`${root}/source/json/pages`);
        for (var i = 0; i < sources.length; i++) {
          if (sources[i].match('.js')) {
            pages[path.basename(sources[i], '.js')] = await source(`${root}/source/json/pages/${sources[i]}`);
          }
        }
      }

      /** copy source JavaScript files to build */
      grunt.file.recurse(`${root}/source/js/`, function callback (abspath, rootdir, subdir, filename) {
        if (filename.match('.js')) {
          grunt.file.copy(abspath, `build/js/${filename}`);
        }
      });

      /** iterate pages and transform in HTML */
      _.each (pages, (element, index) => {
        element.task = index;
        element.pages = pages;
        /** all we need to construct this HTML page it's in the page Object */
        transform({
          route: element.route,
          template: `${root}/source/views/${index}.mustache`,
          data: element
        });
      });
    }
    catch (err) {
      console.log(err);
      grunt.log.write('Unknown error').error();
    }
  });
};