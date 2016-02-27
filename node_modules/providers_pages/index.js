function providers_pages(callback , options) {
  var _ = require('underscore');
  var request = require('request');
  var fs = require('fs') ;
  var conf = JSON.parse(fs.readFileSync(__dirname + '/conf.json', 'utf8'));
  var environment = options.parent_conf.environment;
  if (environment !== 'production') environment = '*';    
    var template = __dirname + '/providers_pages.mustache' ;
    if (conf.page) data = conf.page ;
    if (data.content) {
      data.sources = [];
      var sources = data.content.items.sources;
      var sources_length = data.content.items.sources.length;
      for ( var i = 0; i < sources_length; i++ ) {
        data.sources.push({ 'src' : conf[environment].sources[sources[i]], 'source' : sources[i]});  
      }
    }
    request(conf[environment].request.drupal_providers.src, function (error, response, body) {
      if (!error && response.statusCode == 200) {
        var drupal_providers = JSON.parse(body);
        var length = drupal_providers.length;
        var drupal_terms = [];
        var i = 0;
        for (i; i < length; i++) {
          drupal_terms[drupal_providers[i].raw_value] = drupal_providers[i];
      	  data.tid = drupal_providers[i].raw_value;
      	  data.label = drupal_providers[i].value;
      	  callback ({
            route: '/providers/' + drupal_providers[i].raw_value + '/index.html',
            template: template,
            data: data
          });
        }
      }
    }
  );
}

exports.providers_pages = providers_pages;
