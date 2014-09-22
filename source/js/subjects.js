YUI().use(
    'node'
  , function (Y) {
	
    'use strict';

    // match returns an array
	
    var match = location.pathname.match(/\/subject\/(.*)/);
	
    Y.one(".subjectname").setContent(match[1]);
	
    Y.all(".subcat-nav a").each(function (node) {
	
        var bigSrc = node.get('href');
		
        if ( bigSrc == window.location ) {
	        node.addClass('active');
        }
	});
})