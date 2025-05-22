/* jshint laxcomma: true */
YUI().use( 'node', function (Y) {

  'use strict';
  
  var html = Y.one('html');
  
  if ( html.hasClass('nojs') ) {
    html.removeClass('nojs');
  }
    
});
