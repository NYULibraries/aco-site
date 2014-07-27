module.exports = function(grunt) {

    function transformHTML(buildPath, task) {

        grunt.log.write('Transforming ' + task + ' template into HTML ').ok();  

        var hogan = require('hogan');

        var conf = grunt.file.readJSON('conf.json'); 

        var source = conf.pages[task];
            source.discovery = conf.discovery;
            source.appName = conf.appName;
            source.appUrl = conf.appUrl;
            source.css = grunt.file.read(__dirname + '/build/css/style.css');

        // compile template
        var template = hogan.compile( grunt.file.read(__dirname + '/source/views/' + task + '.mustache') );

        // write file
        grunt.file.write(buildPath, template.render( source, { 
            'head' : grunt.file.read(__dirname + '/source/views/head.mustache')
          , 'header' : grunt.file.read(__dirname + '/source/views/header.mustache')
          , 'partners' : grunt.file.read(__dirname + '/source/views/partners.mustache')
          , 'footer' : grunt.file.read(__dirname + '/source/views/footer.mustache')          
        }))
    }

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
        compress: true,
        preserveComments: false        
      },
      my_target: {
          files : {
              'build/js/book.min.js' : __dirname + '/source/js/book.js',
              'build/js/browse.min.js' : __dirname + '/source/js/browse.js',
              'build/js/collections.min.js' : __dirname + '/source/js/collections.js' ,
              'build/js/front.min.js' : __dirname + '/source/js/front.js',
              'build/js/search.min.js' : __dirname + '/source/js/search.js',
              'build/js/series.min.js' : __dirname + '/source/js/series.js',
              'build/js/crossframe.min.js' : __dirname + '/source/js/crossframe.js'
          }
      }
    },
    sass: {
        dist: {
            options: {
                style: 'expanded'
            },
            files: {
               'build/css/style.css' : __dirname + '/source/sass/style.scss',
               'build/css/book.css' : __dirname + '/source/sass/book.scss',               
            }
        }
    },
    watch: {
        files: [
            __dirname + '/source/js/*.js'
          , __dirname + '/source/sass/*.scss'
          , __dirname + '/source/views/*.mustache'
        ],
        tasks: [
            'uglify'
          , 'sass'
          , 'front'
          , 'browse'
          , 'about'
          , 'series'
          , 'collections'
          , 'search'
          , 'book'          
        ]
    }
      
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
  
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('front', 'front', function() {
        transformHTML(__dirname + '/build/index.html', 'front');
    });  
  
    grunt.registerTask('browse', 'browse', function() {
        transformHTML(__dirname + '/build/browse/index.html', 'browse');    
    });  
  
    // A very basic default task.
    grunt.registerTask('about', 'about', function() {
        transformHTML(__dirname + '/build/about/index.html', 'about');
    });  
    
    grunt.registerTask('series', 'series', function() {
        transformHTML(__dirname + '/build/series/index.html', 'series');    
    });
    
    grunt.registerTask('collections', 'collections', function() {
        transformHTML(__dirname + '/build/collections/index.html', 'collections');    
    });
    
    grunt.registerTask('search', 'search', function() {
        transformHTML(__dirname + '/build/search/index.html', 'search');    
    });
    
    grunt.registerTask('book', 'book', function() {
        transformHTML(__dirname + '/build/book/index.html', 'book');    
    });    

    grunt.registerTask('default', ['uglify', 'sass', 'front', 'browse', 'about', 'series', 'collections', 'search', 'book']);

};