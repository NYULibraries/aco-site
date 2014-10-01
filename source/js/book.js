YUI().use(
    'node'
  , 'anim'
  , 'crossframe'
  , 'router'
  , 'event-resize'
  , 'json-stringify'
  , function (Y) {
  
    'use strict';
    
    var body = Y.one('body')
      , widget = Y.one('.widget.book')
      , match_book = location.pathname.match(/\/book\/(.*)/)
      , match_book_page = location.pathname.match(/\/book\/(.*)\/(.*)/)
      , sourceUrl = widget.getAttribute("data-sourceUrl")
      , appRoot = body.getAttribute("data-appRoot")
      , bookTheme = widget.getAttribute("data-bookTheme")
      , src = ''
      , identifier = '';
      
    body.addClass('io-loading');
      
    function calculateAvailableHeight() {
      
        var siblings = widget.siblings()
          , viewport = Y.DOM.viewportRegion()
          , availableHeight = viewport.height;
      
        siblings.each(function(node) {
            availableHeight = availableHeight - node.get('offsetHeight');
        });
          
        return availableHeight;
    }
      
    function hideSiblings() {
        widget.siblings().each(function(node) {
            node.setStyles( { 'display' : 'none', 'visibility' : 'hidden' })
        });
    }
      
    function showSiblings() {
        widget.siblings().each(function(node) {
            node.setStyles( { 'display' : 'initial', 'visibility' : 'visible' })
        });
    }      

    if (
         match_book_page
         &&
         match_book_page[2]
      ) 
    {
    
      identifier = match_book_page[1];
      
      src = widget.getAttribute("data-sourceUrl") + '/books/' + identifier + '/' +  match_book_page[2];

    }
    
    else if (
         match_book
         &&
         match_book[1]
    ) 
    {
    
      identifier = match_book[1];
          
      src = sourceUrl + '/books/' + identifier + '/1';
    }    
    src = src + "?ctheme=" + bookTheme;
    var router = new Y.Router({
        root : appRoot,
        routes: [
            {
                path: '*', 
                callbacks: function () {
                    Y.log('TIME: ' + new Date() + '. At some point this will work and make sure the browser back button works as expected');
                }
            }
        ]
    });
    
    widget.setStyles({ height : calculateAvailableHeight() });
    
    widget.set('src', src);

    Y.on('windowresize', function( ) {
        widget.setStyles({ height : calculateAvailableHeight() });
    });
    
    Y.on('button:button-fullscreen:on', function() {
        hideSiblings();
        widget.setStyles({ height: calculateAvailableHeight() });
    });

    Y.on('button:button-fullscreen:off', function() {
         showSiblings();
         widget.setStyles({ height: calculateAvailableHeight() });
    });    
    
    Y.on('openlayers:change', function(data) {        
        router.save('/book/' + identifier + '/' + data.sequence);
    });        
    
    widget.on('load', function() {
    
        var frameName = 'frames["book"]'
          , message   = body.getAttribute("data-app") + '/css/book.css'
          , config    = {
              "eventType"   : "crossframe:css",
              "callback"    : function () {
                  var anim = new Y.Anim({
                    node: '.widget.book',
                    to: { opacity: 1 },
                    duration: 0.2
                  });
                  
                  anim.run();
                  
                  body.removeClass('io-loading');
                  
              }
        };
        
        Y.CrossFrame.postMessage(frameName, message, config);

        Y.Global.on("crossframe:message", function (o, data) {

            var message = JSON.parse(data.message);
             
            Y.fire(message.fire, message.data );
            
        });
    
    });

});