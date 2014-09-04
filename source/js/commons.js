YUI().use('get', 'node', function (Y) {

    var widgets = Y.all('.widget')

    if ( widgets.size() ) {

        widgets.each(function (node) {

            var data = node.getData()
              , body = Y.one('body')
              , files;

            node.addClass( data.name )
            
            if (data.script) {

                files = JSON.parse(data.script)

                Y.Array.each( files.js, function(i) {
                    Y.Get.js( body.getAttribute("data-app") + '/js/' + i, function (err) {
                        if (err) {
                            Y.log('Error loading JS: ' + err[0].error, 'error')
                        }
                        else {
                            Y.log('Widget loaded successfully!')
                        }
                
                    })
                
                })
            }
            
        })

    }

})