
var grunt = require('grunt') ;

function project () {
  var projectConfiguration ;
  var projectConfigurationFile = __dirname + '/source/json/conf.json' ;
  if ( grunt.file.isFile ( projectConfigurationFile ) ) {
    projectConfiguration = grunt.file.readJSON ( projectConfigurationFile ) ;
  }
  return projectConfiguration ;
}

function htmlminify () {
  var htmlminifyConfiguration = {} ;
  var htmlminifyConfigurationFile = __dirname + '/source/json/htmlminify.json' ;
  if ( grunt.file.isFile ( htmlminifyConfigurationFile ) ) {
    htmlminifyConfiguration = grunt.file.readJSON ( htmlminifyConfigurationFile ) ;
    htmlminifyConfiguration = htmlminifyConfiguration.htmlminify
  }
  return htmlminifyConfiguration ;
}

function curl () {
  if ( grunt.file.isFile( __dirname + '/source/json/curl.json' ) ) {
	var curlConfiguration = grunt.file.readJSON( __dirname + '/source/json/curl.json' ) ;
	return curlConfiguration.curl ;
  }
}

function js () {

  var js_conf;

  if ( grunt.file.isFile( __dirname + '/source/json/js.json' ) ) {
	js_conf = grunt.file.readJSON( __dirname + '/source/json/js.json' ) ;
  }
	  
  else {
    // default JS configuration
    js_conf = {
      js : {
        build : "external", // options: inline,  external
        style : "expanded" // options: compressed, expanded
	  }
    };  
  }
  
  return js_conf ;

}

/** merge with compass */
function sass () {
  
  var sass_conf;
  
  if ( grunt.file.isFile( __dirname + '/source/json/sass.json' ) ) {
    sass_conf = grunt.file.readJSON( __dirname + '/source/json/sass.json' ) ;  
  }
  
  else {
	// default SASS configuration
    sass_conf = {
      sass : {
        build : "external", // options: inline,  external
	    // build : "external", // options: inline,  external
	    // for options; see: https://github.com/gruntjs/grunt-contrib-sass
	    options : {
          style : "expanded", // options: nested, compact, compressed, expanded
          debugInfo : false,
          lineNumbers : true,
          trace: false
        }
      }
    };  
  }
  
  return {
    dist: {
      options: sass_conf.sass.options,
      files: { 'build/css/style.css': __dirname + '/source/sass/style.scss' },
      build : sass_conf.sass.build
    }
  } ;

}

function compass () {
	
  var projectConfiguration = project () ;
  
  // outputStyle: nested, expanded, compact, compressed
  // noLineComments: true, false
  // httpPath: String , default to "/"

  var compass_conf;
	  
  if ( grunt.file.isFile ( __dirname + '/source/json/compass.json' ) ) {
    compass_conf = grunt.file.readJSON ( __dirname + '/source/json/compass.json' ) ;  
  }
	
  return {
	dist: {
	  options: {
        basePath: __dirname,
        sassDir: __dirname + '/source/sass',
        outputStyle: 'expanded',
        imagesDir: 'images',
        javascriptsDir: 'js',
        cssDir: 'build/css',
        httpPath: projectConfiguration.appRoot
      }
    }
  }

}

function copy () {
  return {
    main: {
      files: [
       { expand: true, cwd: 'source/images', src: '**/*', dest: 'build/images'},
       { expand: true, cwd: 'source', src: 'robots.txt', dest: 'build'},
       ]
    }
  };
}

function clean () {
  return [ 
    __dirname + '/build/*',
    __dirname + '/source/json/datasources/*.json' 
  ];
}

function watch () {
  return {
    files: [
      __dirname + '/source/js/*.js',
      __dirname + '/source/json/*.json',
      __dirname + '/source/sass/*.scss',
      __dirname + '/source/views/*.mustache',
      __dirname + '/source/views/*.hbs'
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
  function targetsCallback() {
    var targets = {};
    grunt.file.recurse(__dirname + '/source/js/', function callback (abspath, rootdir, subdir, filename) {
      if ( filename.match('.js') ) {
        var name = filename.replace('.js', '');
        targets['build/js/' + name + '.min.js'] = abspath;
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
