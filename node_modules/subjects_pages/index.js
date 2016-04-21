function subjects_pages (callback , options) {
  var _ = require('underscore');
  var request = require('request');
  var fs = require('fs') ;
  var conf = JSON.parse(fs.readFileSync(__dirname + '/conf.json', 'utf8'));
  var environment = options.parent_conf.environment;
  if ( environment !== 'production' ) environment = '*';    
    var template = __dirname + '/subjects_pages.mustache' ;
    if ( conf.page ) data = conf.page ;
    if ( data.content ) {
      data.sources = [];
      var sources = data.content.items.sources ;
      var sources_length = data.content.items.sources.length ;
      for ( var i = 0; i < sources_length; i++ ) {
        data.sources.push( { 'src' : conf[environment].sources[sources[i]], 'source' : sources[i] });  
      }
    }
    request( conf[environment].request.drupal_subjects.src, function (error, response, body) {
      if ( !error && response.statusCode == 200 ) {
        var drupal_subjects = JSON.parse( body ) ;
        var length = drupal_subjects.length ;
        var drupal_terms = [];
        for ( var i = 0; i < length; i++ ) {
          drupal_terms[drupal_subjects[i].raw_value] = drupal_subjects[i];
      	  data.tid = drupal_subjects[i].raw_value ;
      	  
      	  data.label = drupal_subjects[i].value ;
      	  
      	  callback ( {
            route: '/subjects/' + drupal_subjects[i].raw_value + '/index.html',
            template: template,
            data: data
          } ) ;      	  
        }
    }
  });
}
exports.subjects_pages = subjects_pages;
