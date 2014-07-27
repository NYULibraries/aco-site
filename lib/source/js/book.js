YUI().use(
    'node'
  , 'anim'
  , 'crossframe'
  , 'router'
  , 'json-stringify'
  , function (Y) {
  
    'use strict'

    var body = Y.one('body')
      , topOffsetHeight = Y.one('.home-menu').get('offsetHeight')
      , iframe = Y.one('iframe')
      , match = location.pathname.match(/\/book\/(.*)\/(.*)/)
      , match_page = location.pathname.match(/\/book\/(.*)\/(.*)\/(.*)/)
      , viewport = Y.DOM.viewportRegion()
      , src
      , router = new Y.Router(
        {
            root : '/aco',
            routes: [
                {
                  path: 'book/1', 
                  callbacks: function () { 

                  }
                }
            ]
        }
      )
      , x

    iframe.setStyles({
      top: topOffsetHeight,
      height: viewport.height - topOffsetHeight
    })

    if (
         match_page
         &&
         match_page[3]
      ) 
    {
      
      x = match_page
      
      src = body.getAttribute("data-sourceUrl") + '/books/' + match_page[2] + '/' +  match_page[3];

    }
    
    else if (
         match 
         &&
         match[2]
      ) 
    {
      x = match 
      
      src = body.getAttribute("data-sourceUrl") + '/books/' + match[2] + '/1';
      
    }    
    
    Y.log(src)
    
    iframe.set('src', src)
    
    Y.on('button:button-fullscreen:on', function(e) {
        Y.one('.header').setStyles({
          display: 'none',
          visibility: 'hidden'          
        })
        
        Y.one('#book').setStyles({
          top: 0,
          height: viewport.height
        })
    });
    
    Y.on('openlayers:change', function(data) {        
        router.save('/book/' + x[1] + '/' + x[2] + '/' + data.sequence)
    });    
    
    Y.on('button:button-fullscreen:off', function(e, data) {
        
        Y.one('.header').setStyles({
          display: 'initial',
          visibility: 'visible'          
        })
        
        Y.one('#book').setStyles({
          top: topOffsetHeight,
          height: viewport.height - topOffsetHeight
        })
                
    });    
    
    // https://github.com/josephj/yui3-crossframe
    iframe.on('load', function() {  
    
        var frameName = 'frames["book"]'
          , message   = body.getAttribute("data-app") + '/css/book.css'
          , config    = {
              "eventType"   : "crossframe:css",
              "callback"    : function (o) {

                  iframe.setStyles({ visibility: 'visible' })

                  var anim = new Y.Anim({
                    node: '#book',
                    to: { opacity: 1 },
                    duration: 0.2
                  });
                  
                  anim.run();
                  
                  Y.one('.loading').removeClass('active')
                  
              }
          };

        Y.CrossFrame.postMessage(frameName, message, config)

        Y.Global.on("crossframe:message", function (o, data, callback) {
            
            var message = JSON.parse(data.message);
             
            Y.fire(message.fire, message.data );
            
        })
    
    })

})