YUI().use(
  'node', 'anim', 'crossframe', 'router', 'event-resize',
  function(Y) {

    'use strict';

    var body = Y.one('body'),
      widget = Y.one('.widget.book'),
      appRoot = body.getAttribute("data-appRoot");

    body.addClass('io-loading');

    function calculateAvailableHeight() {

      var siblings = widget.siblings(),
        viewport = Y.DOM.viewportRegion(),
        availableHeight = viewport.height;
      // Push the iframe down 5px to make up for the 5 pixels 
      // space created by the curved corners of the browser?
      // Not elegant but to consider. 
     // availableHeight += 5;
      siblings.each(function(node) {
        availableHeight = availableHeight - node.get('offsetHeight');
      });

      if (body.hasClass('io-loading')) {
        var loadingAnimation = Y.one('.bubblingG'),
          loaderHeight = loadingAnimation.get('offsetHeight');
        availableHeight = availableHeight + loaderHeight;
      }

      return availableHeight;
    }

    function resizeBookView() {
      widget.setStyles({
        height: calculateAvailableHeight()
      });
    }

    function hideSiblings() {

      widget.siblings().each(function(node) {
        node.addClass('hiddenSiblings');
      });

      resizeBookView();
    }

    function showSiblings() {

      widget.siblings().each(function(node) {
        node.removeClass('hiddenSiblings');
      });

      resizeBookView();
    }

    function requestReplaceLoadBook(request) {

      var src = widget.getAttribute('data-sourceUrl'),
        bookTheme = widget.getAttribute('data-bookTheme'),
        identifier = request.params.identifier,
        page = (request.params.page) ? request.params.page : 1;

      widget.setAttribute('data-identifier', identifier);

      if (request.src === 'replace') {

        src = src + '/books/' + request.params.identifier + '/' + page;

        if (bookTheme) {
          src = src + '?ctheme=' + bookTheme;
        }

        widget.set('src', src);

      }

    }

    var router = new Y.Router({
      root: appRoot,
      routes: [{
        path: '/book/:identifier/:page',
        callbacks: requestReplaceLoadBook
      }, {
        path: '/book/:identifier',
        callbacks: requestReplaceLoadBook
      }]
    });

    Y.on('windowresize', resizeBookView);

    Y.on('button:button-fullscreen:on', hideSiblings);

    Y.on('button:button-fullscreen:off', showSiblings);

    Y.on('openlayers:change', function(data) {
      router.save('/book/' + widget.getAttribute('data-identifier') + '/' + data.sequence);
    });

    Y.Global.on("crossframe:message", function(o, data) {

      var message = JSON.parse(data.message);

      Y.fire(message.fire, message.data);

    });

    widget.on('load', function() {

      var anim = new Y.Anim({
        node: this,
        to: {
          opacity: 1
        },
        duration: 0.3
      });

      resizeBookView();

      anim.run();

      body.removeClass('io-loading');

    });

    resizeBookView();


    // initial request
    router.replace(router.getPath());

  });