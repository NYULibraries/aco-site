YUI().use(
  'node',
  'anim',
  'crossframe',
  'router',
  'event-resize',
  'querystring-parse',
  function (Y) {
    'use strict';

    var body = Y.one('body');

    var widget = Y.one('.widget.book');

    var appRoot = body.getAttribute('data-appRoot');

    var params = {
      'lang' : 'en'
    };

    if (window.location.search.length) {
      params = Y.QueryString.parse(window.location.search.replace(/\?/i, ''));
    }

    body.addClass('io-loading');

    function calculateAvailableHeight() {
      var loadingAnimation = '';
      var loaderHeight = 0;
      var siblings = widget.siblings();
      var viewport = Y.DOM.viewportRegion();
      var availableHeight = viewport.height;

      // Push the iframe down 5px to make up for the 5 pixels
      // space created by the curved corners of the browser?
      // Not elegant but to consider.
      // availableHeight += 5;
      siblings.each(function(node) {
        availableHeight = availableHeight - node.get('offsetHeight');
      });

      if (body.hasClass('io-loading')) {
        loadingAnimation = Y.one('.bubblingG');
        loaderHeight = loadingAnimation.get('offsetHeight');
      }

      return availableHeight + loaderHeight;
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
      var src = widget.getAttribute('data-sourceUrl');
      var identifier = request.params.identifier;
      var page = (request.params.page) ? request.params.page : 1;
      widget.setAttribute('data-identifier', identifier);
      if (request.src === 'replace') {
        src = src + '/books/' + request.params.identifier + '/' + page + '?embed=1&lang=' + params.lang;
        widget.set('src', src);
      }
    }

    var router = new Y.Router({
      root: appRoot,
      routes: [
        {
          path: '/book/:identifier/:page',
          callbacks: requestReplaceLoadBook
        },
        {
          path: '/book/:identifier',
          callbacks: requestReplaceLoadBook
        }
      ]
    });

    Y.on('windowresize', resizeBookView);

    Y.on('button:button-fullscreen:on', hideSiblings);

    Y.on('button:button-fullscreen:off', showSiblings);

    Y.on('viewer:sequence:change', data => {
      const identifier = widget.getAttribute('data-identifier');
      router.save(`/book/${identifier}/${data.sequence}`);
    });

    Y.on('viewer:sequence:increase', data => {
      const identifier = widget.getAttribute('data-identifier');
      router.save(`/book/${identifier}/${data.sequence}`);
    });

    Y.on('viewer:sequence:decrease', data => {
      const identifier = widget.getAttribute('data-identifier');
      router.save(`/book/${identifier}/${data.sequence}`);
    });

    Y.on('change:option:multivolume', data => {
      const args = data.split('/');
      if ((typeof args[2] !=='undefined') && (typeof args[3] !=='undefined')) {
        const identifier = args[3];
        window.location = `${appRoot}/book/${identifier}/1`;
      }
    });

  // DLTS Viewer fires a crossframe:message when the
  // metadata pane in the book page is updated. We
  // listen for this event and use it to update
  // <title> [HTML element] with the title of the book.
  // [WCAG2.0AA compliance](https://www.w3.org/WAI/WCAG21/Understanding/page-titled.html)
  Y.on('display:load', data => {
    document.title = data.title + ': ' + Y.one('meta[property="og:site_name"]').get('content');
  });

  window.addEventListener('message', function (event) {
    console.log('message')
    var data = JSON.parse(event.data)
    console.log(data.fire)
    if (data.fire) {
      Y.fire(data.fire, data.message)
    }
  }, false)

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
