module.exports = function(grunt) {

  function transformHTML(buildPath, task) {

        try {  

            var hogan = require('hogan');

            var conf = grunt.file.readJSON('conf.json'); 

            var source = conf.pages[task];
                source.discovery = conf.discovery;
                source.appName = conf.appName;
                source.appUrl = conf.appUrl;

                // later on for prod
                // source.css = grunt.file.read(__dirname + '/build/css/style.css');

            // compile template
            var template = hogan.compile( grunt.file.read(__dirname + '/source/views/' + task + '.mustache') );
        
            var partials = {}
        
            grunt.file.recurse( __dirname + '/source/views/' , function callback(abspath, rootdir, subdir, filename) {
              if (filename.match(".mustache") && task + '.mustache' !== filename) {
                  var name = filename.replace(".mustache", "");
                  partials[name] = grunt.file.read(abspath)
              }
            })
        
            grunt.file.recurse( __dirname + '/source/views/' , function callback(abspath, rootdir, subdir, filename) {
        
              if ( filename.match(".hbs") ) {
                  grunt.file.write( 'build/js/' + filename, grunt.file.read( abspath ) )
              
              }
            })
            
            // write file
            grunt.file.write( buildPath, template.render( source, partials ) )
            
            grunt.log.write('Transforming ' + task + ' template into HTML ').ok();  
        }

        catch (err) {  
            grunt.log.write('Transforming ' + task + ' template into HTML. See ' + err.description + ' ').error();  
        }        
        

  }

  function targetsCallback() {

    var targets = {};
     
    grunt.file.recurse( __dirname + '/source/js/' , function callback(abspath, rootdir, subdir, filename) {
          
          if (filename.match(".js")) {
          
              var name = filename.replace(".js", "");

              targets['build/js/'+ name +'.min.js'] = abspath
          }
          
    })
  
    return targets;

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
          files : targetsCallback()
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
          , 'writeHTML'          
        ]
    }
      
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
  
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');
    
    grunt.registerTask('writeHTML', 'writeHTML', function() {
    
        var conf = grunt.file.readJSON('conf.json'); 
    
        try {  
            
            Object.keys(conf.pages).forEach(function (key) {
                transformHTML( __dirname + '/build' + conf.pages[key].path , key);
            });

        }
        catch (err) {  
            grunt.log.write("Unknown error: " + err.description).error();  
        }

    });  

    grunt.registerTask('default', ['uglify', 'sass', 'writeHTML']);

};