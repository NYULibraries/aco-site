/* jshint laxcomma: true, laxbreak: true, unused: false */

module.exports = function( grunt ) {

    var _ = require('underscore');

    function transformHTML ( buildPath, task ) {

        try {

            var hogan = require('hogan')
              , conf = grunt.file.readJSON( __dirname + '/source/json/conf.json')
              , pages = grunt.file.readJSON( __dirname + '/source/json/pages.json')
              , widgets = grunt.file.readJSON(__dirname + '/source/json/widgets.json')
              , uncompileTemplate = grunt.file.read( __dirname + '/source/views/' + task + '.mustache' )
              , source = pages[task]
              , matchWidgetsRegEx = "data-script='(.*)'"
              , matchWidgets = uncompileTemplate.match( matchWidgetsRegEx )
              , javascriptTagOpen = '<script>'
              , javascriptTagClose = '</script>'
              , template = hogan.compile( uncompileTemplate )
              , environment = conf.environment
              , partials = {}
              , menus = []
              , navbar = []
              , toJSON = ''
              , javascriptString = ''
              , javascriptString = ''
              , handlebarsTemplate = ''
              , links = ''
              , closure = '';
            
            if ( matchWidgets && matchWidgets[0] ) {
                
                toJSON = matchWidgets[0];
                
                toJSON = toJSON.replace(/'/g, '').replace(/data-script=/g, '');

                toJSON = JSON.parse( toJSON );

                _.each( toJSON.js, function ( js ) {
                    if ( grunt.file.isFile ( 'build/js/' + js ) ) {
                        javascriptString += javascriptTagOpen + grunt.file.read( 'build/js/' + js ) + javascriptTagClose;
                    }
                });
                
                _.each( toJSON.hbs, function ( hbs ) {
                	
                    var handlebarsTagOpen = '<script id="'+ hbs.id +'" type="text/x-handlebars-template">'
                      , handlebarsTagClose = '</script>';

                    if ( grunt.file.isFile ( 'source/views/' + hbs.template ) ) {
                        handlebarsTemplate += handlebarsTagOpen + grunt.file.read( 'source/views/' + hbs.template ) + handlebarsTagClose;
                    }

                });

            }
            
            closure += handlebarsTemplate + javascriptString;
            
            source.closure = closure;
            
            // build the menu object
            _.each( pages, function ( page, index ) {
                if ( _.isArray( pages[index].menu ) ) {
                    _.each( pages[index].menu, function ( menu ) {

                       menus[menu.weight] = {
                            label : menu.label
                         ,  route : pages[index].route.replace('/index.html', '/')
                         ,  page : index
                         ,  weight : menu.weight
                        };
                       
                    });
                }
            });
            
            // this spaghetti maps the widgets to the taks and 
            // load data Object if type is not local
            if ( source.content ) {
              _.each( source.content, function ( content, a ) {
                _.each( source.content[a], function ( pane, b ) {
                  if ( _.isArray( source.content[a][b].widgets ) ) {
                    _.each( source.content[a][b].widgets, function ( widget, c ) {

                      var spaghetti = {};

                      spaghetti[widget] = widgets[source.content[a][b].widgets[c]][source.content[a][b].language_code];

                      if ( spaghetti[widget].sourceType == 'json' ) {
                        spaghetti[widget].data = grunt.file.readJSON( __dirname + '/' + spaghetti[widget].source );   
                      }

                      source.content[a][b].widgets[c] = spaghetti;

                    });
                  }
                });
              });
            }
            
            source.menus = menus;

            source.appRoot = conf[environment].appRoot;  
            
            source.discovery = conf.discovery;

            source.appName = conf.appName;
            source.appOGDescription = conf.appOGDescription;
            source.appOGImage = conf.appOGImage;
            source.appOGUrl = conf.appOGUrl;
            source.appUrl = conf[environment].appUrl;
            
            source.partners = widgets.partners;  
            
            if ( conf[environment].sass.build === 'external' ) {
            	source.css = "<link href='"+ source.appUrl + "/css/style.css' rel='stylesheet' type='text/css'>";	
            }

            else {
                source.css = "<style>" + grunt.file.read(__dirname + '/build/css/style.css') + "</style>";
            }

            grunt.file.recurse( __dirname + '/source/views/' , function callback(abspath, rootdir, subdir, filename) {

              if ( filename.match(".mustache") && task + '.mustache' !== filename ) {

                  var name = filename.replace(".mustache", "")
                    , partial = grunt.file.read(abspath)
                    , matchWidgetsRegEx = "data-script='(.*)'"
                    , matchWidgets = partial.match( matchWidgetsRegEx )
                    , toJSON = ''
                    , javascriptString = ''
                    , javascriptTagOpen = '<script>'
                    , javascriptTagClose = '</script>'     
                    , closure = ''                  
                  
                  if ( ! _.find(_.keys(pages), name) ) {
                  
                      if ( matchWidgets && matchWidgets[0] ) {
                
                          toJSON = matchWidgets[0]
                
                          toJSON = toJSON.replace(/'/g, '').replace(/data-script=/g, '')

                          toJSON = JSON.parse( toJSON )

                          _.each( toJSON.js, function ( js ) {
           
                              if ( grunt.file.isFile ( 'build/js/' + js ) ) {
                                  javascriptString += javascriptTagOpen + grunt.file.read( 'build/js/' + js ) + javascriptTagClose
                              }
                          })
                
                       }                  
                  
                      partials[name] = partial + javascriptString
                  
                  }
                  
              }

            })
        
            grunt.file.recurse( __dirname + '/source/views/' , function callback(abspath, rootdir, subdir, filename) {
              if ( filename.match(".hbs") ) {
                  grunt.file.write( 'build/js/' + filename, grunt.file.read( abspath ) )
              }
            })
            
            // write file
            grunt.file.write( buildPath, template.render( source, partials ) )
            
            grunt.log.write('Transforming ' + task + ' template into HTML ').ok()
            
        }

        catch (err) {  
            
            grunt.log.write('Transforming template into HTML. See ' + err.description + ' ').error();  
            
            console.log( err );
        }        
        

  }
  
  function curlConfiguration () {

      var conf = grunt.file.readJSON( __dirname + '/source/json/conf.json');
      
      return conf[conf.environment].curl;
      
  }
  
  function sassConfiguration () {

      var conf = grunt.file.readJSON( __dirname + '/source/json/conf.json');
      
      return {
          dist : {
              options : conf[conf.environment].sass.options
            , files: {
                 'build/css/style.css' : __dirname + '/source/sass/style.scss'
              }
          }
      };

  }
  
  function copyConfiguration () {
	  return {
	      main : {
	        expand : true ,
	        cwd : 'source/images',
	        src : '**/*',
	        dest : 'build/images',
	      }
      };
  }
  
  function cleanConfiguration () {
      return [ 
	      , __dirname + '/build/images', 
	      , __dirname + '/build/css'
	  ];  
  }
  
  function watchConfiguration () {
	  return {
	        files: [
	            __dirname + '/source/js/*.js'
	          , __dirname + '/source/json/*.json'            
	          , __dirname + '/source/sass/*.scss'
	          , __dirname + '/source/views/*.mustache'
	        ],
	        tasks: [
	            'clean'
	          , 'copy'
	          , 'uglify'
	          , 'sass'
	          , 'writeHTML'
	        ]
	    };
  }
  
  function uglifyConfiguration () {
	  
	  function targetsCallback() {

		    var targets = {};
		     
		    grunt.file.recurse( __dirname + '/source/js/' , function callback(abspath, rootdir, subdir, filename) {
		          var name;
		          
		          if ( filename.match('.js') ) {
		          
		              name = filename.replace('.js', '');

		              targets['build/js/' + name + '.min.js'] = abspath
		          }
		          
		    })
		  
		    return targets;

      }
	  
	  return {
	      options : {
	        banner : '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
	        compress : true,
	        preserveComments : false
	      },
	      my_target: {
	          files : targetsCallback()
	      }
      };
  }

  // Project configuration.
    grunt.initConfig({
        pkg : grunt.file.readJSON('package.json')
      , curl : curlConfiguration()
      , clean : cleanConfiguration()
      , copy : copyConfiguration()
      , uglify : uglifyConfiguration()
      , sass : sassConfiguration()
      , watch : watchConfiguration()
    });
  
    grunt.loadNpmTasks('grunt-curl');
  
    grunt.loadNpmTasks('grunt-contrib-jshint');

    grunt.loadNpmTasks('grunt-contrib-clean');
    
    grunt.loadNpmTasks('grunt-contrib-copy');
    
    grunt.loadNpmTasks('grunt-contrib-uglify');
  
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');
    
 
    
    grunt.registerTask('writeHTML', 'writeHTML', function() {
    
        var pages = grunt.file.readJSON(__dirname + '/source/json/pages.json'); 
    
        try {  
        
          _.each( pages, function ( element, index ) {
              transformHTML( __dirname + '/build' + pages[index].route , index);
          });

        }
        
        catch (err) {  
            grunt.log.write("Unknown error: " + err.description).error();  
        }

    });

    grunt.registerTask('default', ['clean', 'copy', 'curl',  'uglify', 'sass', 'writeHTML']);

};