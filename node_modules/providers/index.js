function providers(callback , options) {
  var _ = require('underscore');
  var request = require('request');
  var fs = require('fs') ;
  var data = {};
  var conf = JSON.parse(fs.readFileSync(__dirname + '/conf.json', 'utf8'));
  var environment = options.parent_conf.environment;
  if (environment !== 'production') environment = '*';  
  var template = __dirname + '/providers.mustache';
  if (conf.page) data = conf.page;
  data.terms = [];
  request(conf[environment].request.drupal_providers.src, function(error, response, body) {
    if ( !error && response.statusCode == 200 ) {
      var drupal_providers = JSON.parse(body);
      var length = drupal_providers.length;
      var drupal_terms = [];
      for (var i = 0; i < length; i++) {
         drupal_terms.push({'label' : drupal_providers[i].value, 'nid' : drupal_providers[i].raw_value});
      }
      request(conf[environment].request.providers.src, function(error, response, body) {
        if (!error && response.statusCode == 200) {
          var providers = JSON.parse(body);
          _.each(providers.facet_counts.facet_fields.sm_collection_partner_label, function(label, index) {
            /** Apache Solr response includes the values and the count of the values in pairs */
        	  if ((index + 1) % 2) {
        	    var count = providers.facet_counts.facet_fields.sm_collection_partner_label[index + 1];
        	    if (count > 0) {
        	      data.terms.push(_.findWhere(drupal_terms, {label: label}));
        	    }
           	} 
          })
          callback ({
            route: '/providers/index.html',
            template: template,
            data: data
          });
        }
      });
    }
  });
}

exports.providers = providers;
