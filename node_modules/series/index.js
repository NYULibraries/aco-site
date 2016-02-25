function series ( callback , options ) {
  var _ = require('underscore');
  var request = require('request');
  var fs = require('fs');
  var getSlug = require('speakingurl');
  var conf = JSON.parse( fs.readFileSync ( __dirname + '/conf.json', 'utf8' ) ) ;
  var environment = options.parent_conf.environment ;
  var Entities = require('html-entities').AllHtmlEntities;
  var entities = new Entities();
  if ( environment !== 'production' ) environment = '*' ;
  /** Source URL template */
  var compiled = _.template("<%= discovery %>?fl=*&fq=bundle:dlts_series&fq=sm_series_code:<%= collectionCode %>&sort=ss_series_label%20asc&rows=1000&wt=json");

  /** Build URL */
  var src = compiled ( { 
      collectionCode : options.parent_conf.collectionCode , 
	  discovery : conf[environment].request.series.src
  } ) ;
  
  var template = __dirname + '/series.mustache' ;
    
  var data = {} ;
    
  request ( src , function ( error, response, body ) {

    if ( ! error && response.statusCode == 200 ) {

      if ( conf.page ) data = conf.page ;
      
      var series = JSON.parse( body )
        , sources = data.content.items.sources
        , sources_length = sources.length ;
      
      data.sources = [] ;
      
      data.series = [] ;
      
      for ( var i = 0; i < sources_length; i++ ) {
        data.sources.push ( { 'src' : conf[environment].sources[sources[i]], 'source' : sources[i] } ) ;  
      }

      _.each ( series.response.docs , function ( doc ) {
    	  
        var identifier = doc.ss_identifier ;
     
        if ( doc.bs_status ) {
        	
          data.series.push( {
        	identifier : doc.ss_identifier ,
        	entity_id : doc.entity_id,
        	label : doc.label ,
        	route : getSlug ( entities.decode ( doc.label ) ) ,
        	series_code : doc.sm_series_code ,
        	data : JSON.parse( doc.zs_data ) ,
          } ) ;
          
        }
          
      } ) ;

      callback ( { route: '/series/index.html', template: template, data: data } ) ;

    }
  } ) ;
}

exports.series = series;
