YUI().use(
    'node'
  , 'event'
  , 'io'
  , 'handlebars'
  , 'json-parse'
  , 'jsonp'
  , 'paginator'
  , 'jsonp-url'
  , 'router'    
  , 'querystring-parse'
  , 'gallery-paginator'
  , function (Y) {

    'use strict'

    var body = Y.one('body')
      , QueryString = ( Y.QueryString.parse( location.search, '?') )
      , container = Y.one('[data-name="items"]')
      , app = body.getAttribute('data-app')
      , appRoot = body.getAttribute('data-approot')
      , page = 1
      , transactions = []
      , itemsTemplateSource = Y.one('#hbs_items').getHTML()
      , itemsTemplate = Y.Handlebars.compile(itemsTemplateSource)
      , router = new Y.Router()
      , subjectsList = Y.one('#subjecsList')
      , subjects = JSON.parse(subjectsList.get('innerHTML'))
      
    // Y.log ( subjects )
      
    function findById(tid) {
        for ( var i = 0; i < subjects.length; i++) {
            if (subjects[i].tid == tid) {
                return subjects[i];
            }
        }
    }
    
    Y.Handlebars.registerHelper('subject', function (value) {
    	
        var subject = findById ( value );
        
        if (subject) {
        	
            // return '<a href="' + appRoot + '/subject?tid=' + subject.tid + '">' + subject.term + '</a>';
            
            return subject.term;
        }

    });

    router.route( appRoot +  '/browse', function ( req ) {
        
        var rows = ( req.query.rows ) ? req.query.rows : 10
          , page =  ( req.query.page ) ?  parseInt( req.query.page, 10 ) : 0
          , start =  0
          , node = Y.one('[data-name="items"]')
          
        if ( page <= 1 ) {
            start = 0
        }
        else {
            start = ( page * rows ) - rows
        }
        
    	initRequest ( {
		    container : node
	      , start : start
	      , page : page
    	  , rows : rows
		} )
        
    })
    
    function onFailure() {
        Y.log('onFailure')
    }
    
    function onTimeout() {
        onFailure()
    }
    
    function update ( state ) {
	
    	this.setPage( state.page, true )
		
	    this.setRowsPerPage(state.rowsPerPage, true)
	    	
	    router.save( appRoot + '/browse?page=' + state.page )
	    	
    }
    
    function initPaginator( page, totalRecords, rowsPerPage ) {
        
        var paginatorConfiguration = {
                totalRecords: totalRecords
              , rowsPerPage: rowsPerPage
              , initialPage : page
              , template: '{FirstPageLink} {PageLinks} {NextPageLink}'        
            }
          , paginator = new Y.Paginator( paginatorConfiguration )

        paginator.on( 'changeRequest', update )
               
        paginator.render('#paginator')

    }    

    function onSuccess ( response, args ) {

        try {
        
            var node = args.container
              , resultsnum = Y.one('.resultsnum')
              , page = ( args.page ) ? args.page : 1
              , numfound = parseInt(response.response.numFound, 10)
              , numfoundNode = resultsnum.one('.numfound')
              , start = parseInt(response.response.start, 10)
              , displayStart = ( start < 1 ) ? 1 : start
              , startNode = resultsnum.one('.start')
              , docslengthNode = resultsnum.one('.docslength')
              , docslength = parseInt(response.response.docs.length, 10)
              
            // first transaction; enable paginator
            if ( transactions.length < 1 ) initPaginator( page , numfound, docslength )

            // store called to avoid making the request multiple times
            transactions.push ( this.url )

            // for now, map this at Solr level and fix img to be absolute paths
            response.response.docs.forEach ( function ( element, index ) {
            	response.response.docs[index].appRoot = app
            	response.response.docs[index].identifier = element.ss_identifer
            	response.response.docs[index].app = element.ss_collection_identifier
            })

            node.setAttribute( "data-numFound", numfound )

            node.setAttribute( "data-start", start )

            node.setAttribute( "data-docsLength", docslength )
            
            startNode.set( 'innerHTML', displayStart )

            docslengthNode.set('innerHTML', start + docslength )
            
            numfoundNode.set('innerHTML', numfound)

            // render HTML and append to container
            node.append(
              itemsTemplate({
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
          , page = 0
          , sortBy = 'ss_longlabel'
          , sortDir = 'asc'          
          , language = 'en'
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
                 , 'sm_partners'
                 , 'sm_ar_title'
                 , 'sm_ar_author'
                 , 'sm_ar_publisher'
                 , 'sm_ar_publication_location'
                 , 'sm_ar_subjects'
                 , 'sm_ar_publication_date'
                 , 'sm_ar_partners'
                 
                 // English fields
                 , 'sm_field_title'
                 , 'sm_author'
                 , 'sm_publisher'                 
                 , 'ss_pubdate'
                 // , 'sm_subject'
                 , 'sm_partners'
                 
                 // Subject
                 , 'im_field_subject'
                 
                                  
            ]

        if ( options.page ) {
          page = parseInt( options.page, 10 )
        }

        if ( options.language ) {
          language = options.language
        }

        if ( options.start ) {
          start = parseInt( options.start, 10 )
        }

        if ( options.rows ) {
          rows = parseInt( options.rows, 10 )
        }

        var datasourceURLs = discoveryURL 
                           + "?"
                           + "wt=json"
                           + "&json.wrf=callback={callback}"
                           + "&fq=hash:iy26sh"
                           + "&fq=ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050"
                           + "&fq=ss_language:" + language                           
                           + "&fl=" + fl.join()
                           + "&rows=" + rows
                           + "&start=" + start
                           + "&sort=" + sortBy + "%20" + sortDir
                           
        body.addClass('io-loading')
        
        options.container.empty()

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
    
    if ( QueryString.page ) { 
        page = QueryString.page
    }
    
    router.replace( appRoot + '/browse?page=' + page )

})