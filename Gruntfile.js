module.exports = function(grunt) {

    function transformHTML( buildPath, task ) {
    	
        try {
        
            var hogan = require('hogan')
              , _ = require('underscore')
              , conf = grunt.file.readJSON('conf.json')
              , source = conf.pages[task]
              , content
              , navbar = []
              , template = hogan.compile( grunt.file.read( __dirname + '/source/views/' + task + '.mustache' ) )
              , partials = {};
              
            // this spaghetti maps the widgets to the taks and load data Object if type is not local
            if ( source.content ) {
              _.each( source.content, function ( content, a ) {
                _.each( source.content[a], function ( pane, b ) {
                  if ( _.isArray( source.content[a][b].widgets ) ) {
                    _.each( source.content[a][b].widgets, function ( widget, c ) {

                      var spaghetti = {}

                      spaghetti[widget] = conf.widgets[source.content[a][b].widgets[c]][source.content[a][b].language_code]

                      if ( spaghetti[widget].sourceType == 'json' ) {
                        spaghetti[widget].data = grunt.file.readJSON( __dirname + '/' + spaghetti[widget].source )   
                      }

                      source.content[a][b].widgets[c] = spaghetti
                      
                    })
                  }
                })
              })
            }

            source.appRoot = conf.appRoot;
            
            source.discovery = conf.discovery;
            
            source.appName = conf.appName;
            
            source.appUrl = conf.appUrl;
            
            source.partners = conf.partners;  
            
            // source.widgets = conf.widgets;
            
            // this is working
            // source.recentlyAddedTitles = source.widgets.recentlyAddedTitles
           
           // later on for prod
           // source.css = grunt.file.read(__dirname + '/build/css/style.css');
            
           Object.keys( conf.pages ).forEach( function ( key ) {

                if ( 
                    conf.pages[key].menu && 
                    conf.pages[key].menu.indexOf( 'navbar' )  
                ) {

                    navbar.push( { 
                        title: conf.pages[key].title,
                        route: conf.pages[key].route
                    } )

                }

            })

            grunt.file.recurse( __dirname + '/source/views/' , function callback(abspath, rootdir, subdir, filename) {
              if ( filename.match(".mustache") && task + '.mustache' !== filename ) {
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
            
            grunt.log.write('Transforming ' + task + ' template into HTML ').ok()
            
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
    
    curl: {
        'recentlyAddedTitlesEN': {
            src: 'http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select?wt=json&fq=hash:iy26sh&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&fq=ss_language:en&fl=ss_embedded,title,type,ss_collection_identifier,ss_identifer,ss_representative_image,teaser,sm_field_author,sm_field_title,ss_language,sm_field_publication_date_text,sm_field_publication_location,sm_field_publisher,sm_vid_Terms,tm_vid_1_names,sm_ar_title,sm_ar_author,sm_ar_publisher,sm_ar_publication_location,sm_ar_subjects,sm_ar_publication_date,sm_ar_partner,sm_field_partner&rows=10',
            dest: 'source/json/recentlyAddedTitlesEN.json'
        },
        'recentlyAddedTitlesAR': {
            src: 'http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select?wt=json&fq=hash:iy26sh&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&fq=ss_language:en&fl=ss_embedded,title,type,ss_collection_identifier,ss_identifer,ss_representative_image,teaser,sm_field_author,sm_field_title,ss_language,sm_field_publication_date_text,sm_field_publication_location,sm_field_publisher,sm_vid_Terms,tm_vid_1_names,sm_ar_title,sm_ar_author,sm_ar_publisher,sm_ar_publication_location,sm_ar_subjects,sm_ar_publication_date,sm_ar_partner,sm_field_partner&rows=10',
            dest: 'source/json/recentlyAddedTitlesAR.json'
        }        
    },
    
    clean: [ 
      __dirname + '/build/images', 
      __dirname + '/build/css'
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
  
    grunt.loadNpmTasks('grunt-curl');
  
    grunt.loadNpmTasks('grunt-contrib-jshint');

    grunt.loadNpmTasks('grunt-contrib-clean');
    
    grunt.loadNpmTasks('grunt-contrib-copy');
    
    grunt.loadNpmTasks('grunt-contrib-uglify');
  
    grunt.loadNpmTasks('grunt-contrib-sass');
    
    grunt.loadNpmTasks('grunt-contrib-watch');
    
    grunt.registerTask('writeHTML', 'writeHTML', function() {
    
        var conf = grunt.file.readJSON('conf.json'); 
    
        try {  
            
            Object.keys(conf.pages).forEach(function (key) {
                transformHTML( __dirname + '/build' + conf.pages[key].route , key);
            });

        }
        catch (err) {  
            grunt.log.write("Unknown error: " + err.description).error();  
        }

    });  

    grunt.registerTask('default', ['curl', 'clean', 'copy', 'uglify', 'sass', 'writeHTML']);

};