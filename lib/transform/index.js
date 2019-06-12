module.exports = async function (configuration) {
  const { basename, extname, normalize } = require('path');
  const Handlebars = require('handlebars');
  const _ = require('underscore');
  const grunt = require('grunt');
  const htmlminify = require('html-minifier').minify;
  /** project root directory */
  const root = normalize(__dirname + '/../..');
  /** grunt task configurations */
  const Gruntconfigurations = require(`${root}/Gruntconfigurations`);
  /** register Handlebars helpers */
  _.each(require('../handlebars-helpers'), (helper, key) => {
    Handlebars.registerHelper(key, helper);
  });
  try {
    /**
     * information about how to render the CSS in this project
     * see: https://github.com/gruntjs/grunt-contrib-sass
     */
    const sassConfiguration = Gruntconfigurations.sass();
    /**
     * information about how to render the JS files in this project
     */
    const jsConfiguration = Gruntconfigurations.js();
    const widgets = grunt.file.readJSON(`${root}/source/json/widgets.json`);
    const uncompileTemplate = grunt.file.read(configuration.template);
    const matchWidgetsRegEx = "data-script='(.*)'";
    const matchWidgets = uncompileTemplate.match(matchWidgetsRegEx);
    const handlebarsTemplate = Handlebars.compile(uncompileTemplate);
    var partials = {};
    var toJSON = '';

    /** merge project and page properties */
    let source = _.extend(configuration.data, require(`${root}/source/json/conf.js`));

    source.widgets = {};

    /** array to hold the menu object */
    source.menus = [];

    /** string that holds JavaScript and handlebars templates */
    source.closure = '';

    if (matchWidgets && matchWidgets[0]) {
      toJSON = matchWidgets[0];
      toJSON = toJSON.replace(/'/g, '').replace(/data-script=/g, '');
      toJSON = JSON.parse(toJSON);
      /** append all the templates to the body */
      _.each(toJSON.hbs, (hbs) => {
        if (grunt.file.isFile(`${root}/source/views/${hbs.template}`)) {
          source.closure += `<script id="${hbs.id}" type="text/x-handlebars-template">${grunt.file.read(`${root}/source/views/${hbs.template}`)}</script>`;
        }
      });
      /** JS files */
      _.each(toJSON.js, js => {
    	/**
    	 * the main app Uglify the JavaScript files and copy them from: /source/js to
    	 * /build/js folder along with the source files. We allow to configure the
    	 * app to use: compressed or expanded (default to expanded for development
    	 * purposes). The app can also be configure to host the JavaScript files
    	 * "inline" or "external" (default to expanded for development
    	 * purposes).
    	 *
    	 * In production enviorments we want to set the app to use the compressed
    	 * Javascript file and host it inline (in the HTML body of the page)
    	 *
    	 * in order to use Javascripts files, the file must be specify using data script at
    	 * the source element. See example:
    	 *
    	 * data-script='{ "js" : [ "crossframe.js", "book.js" ] }'
    	 *
    	 */
      if (jsConfiguration.js.style === 'compressed') {
    	  var js_filename = `${basename(js, extname(js))}.min${extname(js)}`;
        if (grunt.file.isFile(`${root}/build/js/${js_filename}`)) {
        	 source.closure += `<script>${grunt.file.read(`${root}/build/js/${js_filename}`)}</script>`;
        }
      }
    	else {
        // aof1: this looks wrong
        if (grunt.file.isFile(`${root}/build/js/${js}`)) {
          source.closure += `<script src="${source.appUrl}/js/${js}" defer></script>`;
        }
    	}
    });
  }

    /** CSS / SASS */
    if (sassConfiguration.dist.build === 'external') {
      source.css = `<link href="${source.appUrl}/css/style.css?${Date.now()}" rel="stylesheet" type="text/css">`;
    }
    else {
      source.css = `<style>${grunt.file.read(`${root}/build/css/style.css`)}</style>`;
    }

    /** build the menu object */
    _.each(configuration.pages, (page, index) => {
      if (_.isArray(configuration.pages[index].menu)) {
        _.each(configuration.pages[index].menu, menu => {
          source.menus[menu.weight] = {
            label: menu.label,
            status: 'active',
            route: configuration.pages[index].route.replace('/index.html', ''),
            page: index,
            weight: menu.weight
          };
        });
      }
    });

    /** clean the menu object of empty values that can "exist" becuase of weight */
    source.menus = _.reject(source.menus, menu => {
      return _.isUndefined(menu);
    });

    _.each(widgets, (widget, name) => {
      source.widgets[name] = {};
      _.extend(source.widgets[name], widget); // this looks wrong
      if (widget.sourceType === 'json') {
    	  source.widgets[name].data = grunt.file.readJSON(`${root}/${widget.source}`);
      }
      else if (widget.sourceType === 'iframe') {
        source.widgets[name].data = { source: source.widgets[name].source };
      }
    });

    /** this spaghetti maps the widgets to the task and load data Object if type is not local. */
    if (source.content) {
      _.each(source.content, (content, a) => {
        _.each(source.content[a], (pane, b) => {
          if (_.isArray(source.content[a][b].widgets)) {
            source.content[a][b].raw = [];
            _.each ( source.content[a][b].widgets, (widget, c) => {
              let spaghetti = {};
              if (widgets[source.content[a][b].widgets[c]].sourceType === 'json') {
                spaghetti = {
                  label: widget,
                  widget: widgets[source.content[a][b].widgets[c]],
                  data: grunt.file.readJSON(`${root}/${widgets[source.content[a][b].widgets[c]].source}`)
                };
              }
              /** if you care about placement in specific scenario */
              source.content[a][b][widget] = spaghetti;
              /** as array to loop by weight */
              source.content[a][b].raw.push(spaghetti);
            });
          }
        });
      });
    }

    grunt.file.recurse(`${root}/source/views/`, async (abspath, rootdir, subdir, filename) => {
      if (filename.match('.mustache') && configuration.template !== filename) {
        var name = filename.replace('.mustache', '');
        var partial = grunt.file.read(abspath);
        var matchWidgetsRegEx = "data-script='(.*)'";
        var matchWidgets = partial.match(matchWidgetsRegEx);
        var toJSON = '';
        var javascriptString = '';
        var closure = '';
        if (!_.find(_.keys(configuration.pages), name)) {
          if (matchWidgets && matchWidgets[0]) {
            toJSON = matchWidgets[0];
            toJSON = toJSON.replace(/'/g, '').replace(/data-script=/g, '');
            toJSON = JSON.parse(toJSON);
            _.each(toJSON.js, js => {
              if (jsConfiguration.js.style == 'compressed') {
            	  const js_filename = `${basename(js, extname(js))}.min${extname(js)}`;
                if (grunt.file.isFile(`${root}/build/js/${js_filename}`)) {
                  javascriptString += `<script>${grunt.file.read(`${root}/build/js/${js_filename}`)}</script>`;
                }
              }
              else {
                if (grunt.file.isFile(`${root}/build/js/${js}`) ) {
                  javascriptString += `<script src="${source.appUrl}/js/${js}"></script>`;
                }
              }
            });
          }
          partials[name] = `${partial}${javascriptString}`;
        }
      }
    });

    grunt.file.recurse(`${root}/source/views/`, async (abspath, rootdir, subdir, filename ) => {
      if (extname(filename) === '.hbs') {
        await grunt.file.write(`${root}/build/js/filename`, await grunt.file.read(abspath));
      }
    });

    _.each(partials, (partial, key) => {
      Handlebars.registerPartial(key, partial);
    });

    await grunt.file.write(`${root}/source/json/datasources/${configuration.task}.json`, JSON.stringify(source));

    /** write HTML file */
    await grunt.file.write(`${root}/build/${configuration.route}`, htmlminify(handlebarsTemplate(source), Gruntconfigurations.htmlminify()));

    grunt.log.write(`Transforming ${configuration.route}`).ok();

  }

  catch (err) {
    grunt.log.write(`Transforming ${configuration.task} into HTML fail. See ${err.description}`).error();
    console.log(err);
  }

}
