function subjects (callback , options) {
  var _ = require('underscore');
  var request = require('request');
  var fs = require('fs') ;
  var data = {};
  var conf = JSON.parse( fs.readFileSync ( __dirname + '/conf.json', 'utf8' ) );
  var environment = options.parent_conf.environment;
  if ( environment !== 'production' ) environment = '*';  
  var template = __dirname + '/subjects.mustache' ;
  if ( conf.page ) {
  	data = conf.page ;
  } 
  data.terms = [];
  request(conf[environment].request.drupal_subjects.src, function (error, response, body) {
    if ( !error && response.statusCode == 200 ) {
      var drupal_subjects = JSON.parse(body);
      var length = drupal_subjects.length;
      var drupal_terms = [];
      for ( var i = 0; i < length; i++ ) {
        drupal_terms[drupal_subjects[i].raw_value] = drupal_subjects[i];
      }
      request(conf[environment].request.subjects.src, function (error, response, body) {
        if ( !error && response.statusCode == 200 ) {
          var subjects = JSON.parse(body);
          _.each ( subjects.facet_counts.facet_fields.im_field_subject , function (doc, index) {
            /** Apache Solr response includes the values and the count of the values in pairs */
            if ( ( index + 1 ) % 2 && drupal_terms[doc] ) data.terms.push({tid:doc, label : drupal_terms[doc].value}); 
          });
          callback ( {
            route: '/subjects/index.html',
            template: template,
            data: data
          } ) ;          
        }
      });
    }
  });
}

exports.subjects = subjects;
