YUI().use(
    'node'
  , 'event'
  , 'io'
  , 'handlebars'
  , 'json-parse'
  , 'jsonp'
  , 'paginator'
  , 'jsonp-url'
  , 'gallery-paginator'
  , function (Y) {

    'use strict'

    var body = Y.one('body')
      , container = Y.one('[data-name="items"]')
      , data = container.getData()      
      , transactions = []
      , appRoot = body.getAttribute("data-app")
      , templates = {}
      , handlebarsTemplates = []
      , paginator = 1

    function onFailure() {
        Y.log('onFailure')
    }
    
    function onTimeout() {
        onFailure()
    }
    
    function initPaginator( totalRecords, rowsPerPage ) {
        
    	function update ( state ) {
	
	        var node = Y.one('[data-name="items"]')
	     
    		this.setPage(state.page, true)
		
	    	this.setRowsPerPage(state.rowsPerPage, true)
		
    		initRequest ( { 
		        container : node
	    	  , start : state.page * state.rowsPerPage
    		  , rows : state.rowsPerPage 
		    } ) 
				
	    }        
        
        var paginatorConfiguration = {
                totalRecords: totalRecords
              , rowsPerPage: rowsPerPage
              , template: '{FirstPageLink} {PageLinks} {NextPageLink}'        
            }
          , paginator = new Y.Paginator( paginatorConfiguration )

        paginator.on( 'changeRequest', update )
               
        paginator.render('#paginator')
    }    

    function onSuccess ( response, args ) {

        try {

            var node = args.container
              , data = node.getData()
              , resultsnum = Y.one('.resultsnum')
              , numfound = parseInt(response.response.numFound, 10)
              , numfoundNode = resultsnum.one('.numfound')
              , start = parseInt(response.response.start, 10)
              , startNode = resultsnum.one('.start')
              , docslengthNode = resultsnum.one('.docslength')
              , docslength = parseInt(response.response.docs.length, 10)

            // first transaction; enable paginator
            if ( transactions.length < 1 ) initPaginator( numfound, docslength )

            // store called to avoid making the request multiple times
            transactions.push ( this.url )

            // for now, map this at Solr level and fix img to be absolute paths
            response.response.docs.forEach ( function ( element, index ) {
            	response.response.docs[index].appRoot = appRoot
            	response.response.docs[index].identifier = element.ss_identifer
            	response.response.docs[index].app = element.ss_collection_identifier
            })

            node.setAttribute( "data-numFound", numfound)

            node.setAttribute( "data-start", start)

            node.setAttribute( "data-docsLength", docslength )
            
            startNode.set('innerHTML', start + 1)

            docslengthNode.set('innerHTML', docslength)
            
            numfoundNode.set('innerHTML', numfound)
            
            // render HTML and append to container
            node.append(
              templates.items({
                items : response.response.docs
              })
            )

            body.removeClass('io-loading')

        }

        catch (e) {

            Y.log('something went wrong. error')

        }

    }
    
    function initRequest ( options ) {
    
        var rows = 10
          , start = 0
          , language = 'und'
          , discoveryURL = "http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select"
          , fl = [ 
                   'ss_embedded'
                 , 'title'
                 , 'type'
                 , 'ss_collection_identifier'
                 , 'ss_identifer'
                 , 'ss_representative_image'
                 , 'teaser'
                 , 'sm_field_title'
                 , 'ss_language'
                 , 'sm_field_publication_date_text'
                 , 'sm_field_publication_location'
                 , 'sm_field_publisher'
                 , 'sm_vid_Terms'
                 , 'tm_vid_1_names'
                 , 'sm_ar_title'
                 , 'sm_ar_author'
                 , 'sm_ar_publisher'
                 , 'sm_ar_publication_location'
                 , 'sm_ar_subjects'
                 , 'sm_ar_publication_date'
                 , 'sm_ar_partner'
                 , 'sm_field_partner'
            ]
            
        if ( options.language ) {
          language = options.language
        }

        if ( options.start ) {
          start = parseInt( options.start, 10 )
        }

        if ( options.rows ) {
          rows = parseInt( options.rows, 10 )
        }

        var datasourceURLs = discoveryURL + "?"
                           + "wt=json"
                           + "&json.wrf=callback={callback}"
                           + "&fq=hash:iy26sh"
                           + "&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050"
                           + "&fq=ss_language:" + language                           
                           + "&fl=" + fl.join()
                           + "&rows=" + rows
                           + "&start=" + start    
                       
        body.addClass('io-loading')
        
        options.container.empty()

        // make the first request
        Y.jsonp( datasourceURLs, {
            on: {
                success: onSuccess,
                failure: onFailure,
                timeout: onTimeout
            },
            args: options,
            timeout: 3000
        })
    
    }
    
    // prod will take care of this task
    if ( data.script ) {

          var files = JSON.parse( data.script )
          
          Y.Array.each( files.hbs, function( source ) {
        	  
            Y.Object.each( source, function( file, key ) {
          	    
            	handlebarsTemplates.push(file)
            	
                    Y.io( body.getAttribute("data-app") + '/js/' + file, {
  			            sync: false,
  			            on: {

                            success: function( transactionId, response ) {

                                Y.log ("Handlebars: retrieve file: " + file );

                                templates[key] = Y.Handlebars.compile ( response.responseText )
  
         				    },
         				    
                            failure:function() {

  					            throw "Handlebars: failed to retrieve url: " + url

  				            }
  				            
    			        },
  			            context: this
                    })

            })
  		  
        })

    }

    initRequest ( { container : container } )

})