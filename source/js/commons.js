YUI().use('get', 'node', function (Y) {

<<<<<<< HEAD
    var widgets = Y.all('.widget')
=======
    var widgets = Y.all('.widget');
>>>>>>> 3fa4891d93a230336192adc1dbc139d259fd43aa

    if ( widgets.size() ) {

        widgets.each(function (node) {

            var data = node.getData()
              , body = Y.one('body')
              , files;

<<<<<<< HEAD
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
=======
            node.addClass( data.name );
            
            if (data.script) {
                files = JSON.parse(data.script);
                Y.Array.each( files.js, function(i) {
                    Y.Get.js( body.getAttribute("data-app") + '/js/' + i, function (err) {
                        if (err) {
                            Y.log('Error loading JS: ' + err[0].error, 'error');
                        }
                        else {
                            Y.log('Widget loaded successfully!');
                        }
                    });
                });
            }
        });

    }

});
>>>>>>> 3fa4891d93a230336192adc1dbc139d259fd43aa
