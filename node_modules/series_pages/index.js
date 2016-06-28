function series_pages ( callback , options ) {

  var _ = require('underscore') ;

  var request = require('request') ;
  
  var getSlug = require('speakingurl');

  var fs = require('fs') ;
  
  var Entities = require('html-entities').AllHtmlEntities;
  
  var entities = new Entities();

  var conf = JSON.parse( fs.readFileSync ( __dirname + '/conf.json', 'utf8' ) ) ;
    
  var environment = options.parent_conf.environment ;
  
  var x = '?wt=json&fl=*&fq=bundle:dlts_series&sm_collection_code:awdl&rows=1000' ;
  
  if ( environment !== 'production' ) environment = '*' ;
    
    var template = __dirname + '/series_pages.mustache' ;
    
    var src = conf[environment].request.series.src + x; 
    
    var data = {} ;
    
    request ( src , function ( error, response, body ) {
      if ( ! error && response.statusCode == 200 ) {
        
    	  var series = JSON.parse( body ) ;
        
        _.each ( series.response.docs , function ( doc ) {

          var sources , sources_length ;
          
          var identifier = doc.ss_identifier ;
          
          /** hardcoded AWDL here for now */
          if ( ! _.has ( data, identifier ) && doc.bs_status && _.contains( doc.sm_series_code, 'awdl' ) ) {

            if ( conf.page ) data[identifier] = conf.page ;

            data[identifier].data = JSON.parse( doc.zs_data ) ;

            data[identifier].label = doc.label ;

            data[identifier].identifier = doc.ss_identifier ;              	

            data[identifier].hash = doc.hash ;

            data[identifier].series_code = doc.sm_series_code ;  
            
            data[identifier].sources = [] ;

            if ( data[identifier].content ) {

              sources = data[identifier].content.items.sources ;

              sources_length = data[identifier].content.items.sources.length ;

              for ( var i = 0; i < sources_length; i++ ) {
                data[identifier].sources.push( { 'src' : conf[environment].sources[sources[i]], 'source' : sources[i] } ) ;  
              }
              
              data[identifier].content.items.identifier = doc.ss_identifier ;

              _.extend( data[identifier].content.items.fq , [ 
                { 'filter' : 'sm_series_identifier', 'value' : doc.ss_identifier } ,
                { 'filter' : 'is_ispartof_series', 'value' : 1 }
              ] ) ;
              
              callback ( { route: '/series/' + getSlug ( entities.decode ( data[identifier].label ) ) + '/index.html', template: template, data: data[identifier] } ) ;              

            }
          }
        });
      }
    });

}

exports.series_pages = series_pages;
