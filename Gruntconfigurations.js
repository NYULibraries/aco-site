const grunt = require('grunt');

function project () {
  const projectConfigurationFile = `${__dirname}/source/json/conf.js`;
  if (grunt.file.isFile(projectConfigurationFile)) {
    return require(projectConfigurationFile);
  }
}

function htmlminify () {
  let htmlminifyConfiguration = {};
  let htmlminifyConfigurationFile = `${__dirname}/source/json/htmlminify.json`;
  if ( grunt.file.isFile(htmlminifyConfigurationFile)) {
    htmlminifyConfiguration = grunt.file.readJSON(htmlminifyConfigurationFile);
    htmlminifyConfiguration = htmlminifyConfiguration.htmlminify;
  }
  return htmlminifyConfiguration ;
}

function curl () {
  const curlConfigurationPath = `${__dirname}/source/json/curl.js`;
  if (grunt.file.isFile(curlConfigurationPath)) {
    return require(curlConfigurationPath).curl;
  }
}

function js () {
  let js_conf;
  if (grunt.file.isFile(`${__dirname}/source/json/js.json`)) {
    js_conf = grunt.file.readJSON(`${__dirname}/source/json/js.json`);
  }
  else {
    // default JS configuration
    js_conf = {
      js: {
        build: 'external', // options: inline,  external
        style: 'expanded' // options: compressed, expanded
      }
    };
  }
  return js_conf;
}

/** merge with compass */
function sass () {
  let sass_conf;
  if (grunt.file.isFile(`${__dirname}/source/json/sass.json`)) {
    sass_conf = grunt.file.readJSON(`${__dirname}/source/json/sass.json`);
  }
  else {
    sass_conf = { // default SASS configuration
      sass: {
        build: 'external', // options: inline,  external
        options: { // for options; see: https://github.com/gruntjs/grunt-contrib-sass
          style: 'expanded', // options: nested, compact, compressed, expanded
          debugInfo: false,
          lineNumbers: true,
          trace: false
        }
      }
    };
  }
  return {
    dist: {
      options: sass_conf.sass.options,
      files: {
        'build/css/style.css': `${__dirname}/source/sass/style.scss`
      },
      build: sass_conf.sass.build
    }
  };
}

function compass () {
  var projectConfiguration = project();
  return {
    dist: {
      options: {
        basePath: __dirname,
        sassDir:`${__dirname}/source/sass`,
        outputStyle: 'expanded',
        imagesDir: 'images',
        javascriptsDir: 'js',
        cssDir: 'build/css',
        httpPath: projectConfiguration.appRoot
      }
    }
  };
}

function copy() {
  return {
    main: {
      files: [
        {
          expand: true,
          cwd: 'source/images',
          src: '**/*',
          dest: 'build/images'
        },
        {
          expand: true,
          cwd: 'source',
          src: 'robots.txt',
          dest: 'build'
        }
      ]
    }
  };
}

function clean () {
  return [
    `${__dirname}/build/*`,
    `${__dirname}/source/json/datasources/*.json`
  ];
}

function watch () {
  return {
    files: [
      `${__dirname}/source/js/*.js`,
      `${__dirname}/source/json/*.json`,
      `${__dirname}/source/sass/*.scss`,
      `${__dirname}/source/views/*.mustache`,
      `${__dirname}/source/views/*.hbs`
    ],
    tasks: [
      'clean',
      'copy',
      'uglify',
      'compass',
      'writeHTML'
    ]
  };
}

function uglify () {
  function targetsCallback () {
    let targets = {};
    grunt.file.recurse(`${__dirname}/source/js/`, (abspath, rootdir, subdir, filename) => {
      if (filename.match('.js')) {
        targets[`build/js/${filename.replace('.js', '')}.min.js`] = abspath;
      }
    });
    return targets;
  }
  return {
    options: {
      banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
      compress: {}, // https://github.com/gruntjs/grunt-contrib-uglify/issues/298#issuecomment-74161370
      preserveComments: false
    },
    my_target: {
      files: targetsCallback()
    }
  };
}

exports.curl = curl;
exports.sass = sass;
exports.copy = copy;
exports.clean = clean;
exports.watch = watch;
exports.uglify = uglify;
exports.js = js;
exports.compass = compass;
exports.project = project;
exports.htmlminify = htmlminify;
