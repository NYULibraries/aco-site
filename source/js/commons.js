YUI().use(
    'get'
  , 'node'
  , 'anim'
  , function (Y) {

    'use strict'

    var widgets = Y.all('.widget')
      , js_loaded = []
      , body = Y.one('body')
      
      
    function onClick ( event ) {

        var currentTarget = event.currentTarget
        
        currentTarget.toggleClass('collapsed')
        
        Y.log( currentTarget.getData() )
        
    }
      
    body.delegate('click', onClick, '.navbar-toggle');

    if ( widgets.size() ) {

        widgets.each( function ( node ) {

            var data = node.getData()
              , files;

            node.addClass( data.name )
            
            if ( data.script ) {
                files = JSON.parse( data.script )
                Y.Array.each ( files.js, function ( file ) {
                    js_loaded.push( file )
                    if ( ! file.indexOf ( file ) ) {
                        Y.Get.js( body.getAttribute('data-app') + '/js/' + file, function ( err ) {
                            if ( err ) {
                                Y.log('Error loading JS: ' + err[0].error, 'error')
                            }
                            else {
                                Y.log('Widget ' + file + ' loaded successfully!')
                            }
                        })
                    }
              })
          }
        })
    }
})