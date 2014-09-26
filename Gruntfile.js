module.exports = function(grunt) {

    var _ = require('underscore')

    function transformHTML( buildPath, task ) {

        try {

            var hogan = require('hogan')
              , conf = grunt.file.readJSON( __dirname + '/source/json/conf.json')
              , pages = grunt.file.readJSON( __dirname + '/source/json/pages.json')
              , widgets = grunt.file.readJSON(__dirname + '/source/json/widgets.json')
              , source = pages[task]
              , navbar = []
              , uncompileTemplate = grunt.file.read( __dirname + '/source/views/' + task + '.mustache' )
              , template
              , partials = {}
              , menus = []
              , matchWidgetsRegEx = "data-script='(.*)'"
              , matchWidgets = uncompileTemplate.match( matchWidgetsRegEx )
              , toJSON = ''
              , javascriptString = ''
              , javascriptTagOpen = '<script>'
              , javascriptTagClose = '</script>'
              , javascriptString = ''
              , handlebarsTemplate = ''              
              , closure = ''
              
            if ( matchWidgets && matchWidgets[0] ) {
                
                toJSON = matchWidgets[0]
                
                toJSON = toJSON.replace(/'/g, '').replace(/data-script=/g, '')

                toJSON = JSON.parse( toJSON )

                _.each( toJSON.js, function ( js ) {
                    if ( grunt.file.isFile ( 'build/js/' + js ) ) {
                        javascriptString += javascriptTagOpen + grunt.file.read( 'build/js/' + js ) + javascriptTagClose
                    }
                })
                
                _.each( toJSON.hbs, function ( hbs ) {
                
                    var handlebarsTagOpen = '<script id="hbs_'+ hbs.id +'" type="text/x-handlebars-template">'
                      , handlebarsTagClose = '</script>'

                    if ( grunt.file.isFile ( 'source/views/' + hbs.template ) ) {
                        handlebarsTemplate += handlebarsTagOpen + grunt.file.read( 'source/views/' + hbs.template ) + handlebarsTagClose
                    }

                })

            }
            
            closure += handlebarsTemplate + javascriptString
            
            source.closure = closure
            
            template = hogan.compile( uncompileTemplate )
            
            // build the menu object
            _.each( pages, function ( page, index ) {
                if ( _.isArray( pages[index].menu ) ) {
                    _.each( pages[index].menu, function ( menu ) {

                       menus[menu.weight] = {
                            label : menu.label
                         ,  route : pages[index].route.replace('/index.html', '')
                         ,  page : index
                         ,  weight : menu.weight
                        }
                    })
                }
            })
            
            // this spaghetti maps the widgets to the taks and load data Object if type is not local
            if ( source.content ) {
              _.each( source.content, function ( content, a ) {
                _.each( source.content[a], function ( pane, b ) {
                  if ( _.isArray( source.content[a][b].widgets ) ) {
                    _.each( source.content[a][b].widgets, function ( widget, c ) {

                      var spaghetti = {}

                      spaghetti[widget] = widgets[source.content[a][b].widgets[c]][source.content[a][b].language_code]

                      if ( spaghetti[widget].sourceType == 'json' ) {
                        spaghetti[widget].data = grunt.file.readJSON( __dirname + '/' + spaghetti[widget].source )   
                      }

                      source.content[a][b].widgets[c] = spaghetti

                    })
                  }
                })
              })
            }
            
            source.menus = menus;

            source.appRoot = conf.appRoot;
            
            source.discovery = conf.discovery;

            source.appName = conf.appName;
            
            source.appUrl = conf.appUrl;
            
            source.partners = widgets.partners;  
            
           // later on for prod
           source.css = grunt.file.read(__dirname + '/build/css/style.css');

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

  function targetsCallback() {

    var targets = {};
     
    grunt.file.recurse( __dirname + '/source/js/' , function callback(abspath, rootdir, subdir, filename) {
          var name;
          
          if (filename.match(".js")) {
          
              name = filename.replace('.js', '');

              targets['build/js/' + name + '.min.js'] = abspath
          }
          
    })
  
    return targets;

  }

  // Project configuration.
  grunt.initConfig({
    
	pkg: grunt.file.readJSON('package.json'),
    
    curl: {
        'recentlyAddedTitlesEN': {
            src: 'http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select?wt=json&fq=hash:iy26sh&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&fq=!ss_language:ar&fl=ss_embedded,title,type,ss_collection_identifier,ss_identifer,ss_representative_image,teaser,sm_field_author,sm_field_title,ss_language,sm_field_publication_date_text,sm_field_publication_location,sm_field_publisher,sm_vid_Terms,tm_vid_1_names,sm_ar_title,sm_ar_author,sm_ar_publisher,sm_ar_publication_location,sm_ar_subjects,sm_ar_publication_date,sm_ar_partner,sm_field_partner&rows=5&&sort=ds_changed%20desc',
            dest: 'source/json/datasources/recentlyAddedTitlesEN.json'
        },
        'recentlyAddedTitlesAR': {
            src: 'http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select?wt=json&fq=hash:iy26sh&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&fq=ss_language:ar&fl=ss_embedded,title,type,ss_collection_identifier,ss_identifer,ss_representative_image,teaser,sm_field_author,sm_field_title,ss_language,sm_field_publication_date_text,sm_field_publication_location,sm_field_publisher,sm_vid_Terms,tm_vid_1_names,sm_ar_title,sm_ar_author,sm_ar_publisher,sm_ar_publication_location,sm_ar_subjects,sm_ar_publication_date,sm_ar_partner,sm_field_partner&rows=5&sort=ds_changed%20desc',
            dest: 'source/json/datasources/recentlyAddedTitlesAR.json'
        },
        'subject': {
        	src: 'http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select?wt=json&fq=hash:iy26sh&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&rows=0&facet=true&facet.field=im_field_subject',
        	dest: 'source/json/datasources/subject.json'
        },
        'drupalSubjecs' : {
        	src: 'http://alpha-user:dlts2010@dev-dl-pa.home.nyu.edu/books/sources/field/field_subject',
        	dest: 'source/json/datasources/subjectsList.json'        	
        	
        }
    },
    
    clean: [ 
      , __dirname + '/build/images', 
      , __dirname + '/build/css'
      , __dirname + 'source/json/datasources'
    ],
    copy: {
      main: {
        expand: true ,
        cwd: 'source/images',
        src: '**/*',
        dest: 'build/images',
      },
    },    
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
                style: 'compressed'
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
    }
      
    });
  
    grunt.loadNpmTasks('grunt-curl');
  
    grunt.loadNpmTasks('grunt-contrib-jshint');

    grunt.loadNpmTasks('grunt-contrib-clean');
    
    grunt.loadNpmTasks('grunt-contrib-copy');
    
    grunt.loadNpmTasks('grunt-contrib-uglify');
  
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');
    
    // far from ideal
    grunt.registerTask('massageDataSource', 'massageDataSource', function() {
    	
    	var subjects_source = grunt.file.readJSON( __dirname + '/source/json/datasources/subject.json' )
    	  , drupal_subjects_source = grunt.file.readJSON( __dirname + '/source/json/datasources/subjectsList.json' )
    	  , terms = subjects_source.facet_counts.facet_fields.im_field_subject
    	  , subjects = []
    	  , z = []
    	  , subjects_source_map = []
    	  , gaga = []
          , jsonScriptTagOpen = '<script id="subjecsList" type="application/json">'
          , jsonScriptTagClose = '</script>'     
          
        _.each( drupal_subjects_source , function ( subject, index ) {
        	subjects.push( { term : subject.value, tid : subject.raw_value })
        })
        	  
        _.each( _.filter( terms, function ( term ) { return _.isString( term ) } ), function ( subject, index ) {
        	 
        	 var z = _.findWhere( subjects, { tid :  subject });
        	 
        	 if ( z ) {
        	     subjects_source_map.push ( z )
        	 }
        	     
        })
        
   	    grunt.file.write( __dirname + '/source/views/subjectsList.mustache', jsonScriptTagOpen + JSON.stringify( subjects_source_map ) + jsonScriptTagClose  )
   	    
        grunt.file.write(__dirname + '/source/json/datasources/subject.json', JSON.stringify( subjects_source_map ) )   	    

    })
    
    grunt.registerTask('writeHTML', 'writeHTML', function() {
    
        var pages = grunt.file.readJSON(__dirname + '/source/json/pages.json'); 
    
        try {  
        
          _.each( pages, function ( element, index ) {
            transformHTML( __dirname + '/build' + pages[index].route , index);
          })

        }
        
        catch (err) {  
            grunt.log.write("Unknown error: " + err.description).error();  
        }

    });  

    grunt.registerTask('default', ['clean', 'copy', 'curl', 'massageDataSource', 'uglify', 'sass', 'writeHTML']);

};