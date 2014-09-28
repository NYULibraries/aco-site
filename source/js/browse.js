YUI().use(
    'node'
  , 'event'
  , 'handlebars'
  , 'json-parse'
  , 'jsonp'
  , 'paginator'
  , 'jsonp-url'
  , 'router'
  , 'gallery-paginator'
  , function (Y) {

    'use strict'

    var body = Y.one('body')
      , app = body.getAttribute('data-app')
      , appRoot = body.getAttribute('data-approot')
      , transactions = []
      , itemsTemplateSource = Y.one('#hbs_items').getHTML()
      , itemsTemplate = Y.Handlebars.compile(itemsTemplateSource)
      , router = new Y.Router()
      
    function getRoute () {

        var pageQueryString = getParameterByName('page')
          , sortQueryString = getParameterByName('sort')
          , page = ( pageQueryString ) ? pageQueryString : 1
          , route = appRoot + '/browse?page=' + page;

        if ( sortQueryString ) {
            route = route + '&sort=' + sortQueryString
        }
        
        return route;

    }
      
    function getParameterByName(name) {
        
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);

        return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }      

    /**
     *
     * Unused until subject page its available
     * 

     var subjectsList = Y.one('#subjecsList')
       , subjects = JSON.parse(subjectsList.get('innerHTML'))
    
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
            return '<a href="' + appRoot + '/subject?tid=' + subject.tid + '">' + subject.term + '</a>';
        }
    });
    */

    router.route( appRoot +  '/browse', function ( req ) {

        var node = Y.one('[data-name="items"]')
          , data = node.getData()
          , rows = ( req.query.rows ) ? req.query.rows : ( ( data.rows ) ? data.rows : 10 ) 
          , sort = ( req.query.sort ) ? req.query.sort : ( ( data.sort ) ? data.sort : Y.one('#browse-select').get('value') )           
          , page =  ( req.query.page ) ?  parseInt( req.query.page, 10 ) : 0
          , start =  0

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
    	  , sort : sort
		} );
        
    })
    
    function onSelectChange( ) {
        router.replace( getRoute() );
    }
    
    function onFailure() {
        Y.log('onFailure');
    }
    
    function onTimeout() {
        onFailure();
    }
    
    function update ( state ) {
	
    	this.setPage( state.page, true );
		
	    this.setRowsPerPage( state.rowsPerPage, true );
	    	
	    router.save( appRoot + '/browse?page=' + state.page );

    }
    
    function initPaginator( page, totalRecords, rowsPerPage ) {
        
        var paginatorConfiguration = {
                totalRecords: totalRecords
              , rowsPerPage: rowsPerPage
              , initialPage : page
              , template: '{FirstPageLink} {PageLinks} {NextPageLink}'        
            }
          , paginator = new Y.Paginator( paginatorConfiguration )

        paginator.on( 'changeRequest', update );
               
        paginator.render('#paginator');

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
            if ( transactions.length < 1 ) initPaginator( page , numfound, docslength );

            // store called to avoid making the request multiple times
            transactions.push ( this.url );

            node.setAttribute( 'data-numFound', numfound );

            node.setAttribute( 'data-start', start );

            node.setAttribute( 'data-docsLength', docslength );
            
            startNode.set( 'innerHTML', displayStart );

            docslengthNode.set( 'innerHTML', start + docslength );
            
            numfoundNode.set( 'innerHTML', numfound );

            node.append(
              itemsTemplate({
                items : response.response.docs,
                app: { appRoot : app }
              })
            );
            
            args.container.setAttribute( 'data-requesterror', 0 );

            body.removeClass('io-loading')

        }

        catch (e) {

            var data = args.container.getData()
              , requestError = data.requesterror
              
            if ( !requestError ) {
                args.container.setAttribute( 'data-requesterror', 1 );
                requestError = 1;
            }
            else { 
                requestError = parseInt(requestError, 10) + 1;
                args.container.setAttribute( 'data-requesterror', requestError );                
            }
            
            if ( requestError < 3 ) {
                router.replace( getRoute () );
            }

        }

    }
    
    function initRequest ( options ) {
    
        var rows = 10
          , start = 0
          , page = 0
          , sortBy = Y.one('#browse-select').get('value')
          , sortDir = 'asc'          
          , language = 'en'
          , discoveryURL = "http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/select"
          , fl = [ 

                 /** shared fields */
                 , 'ss_thumbnail'
                 , 'ss_identifer'
                 
                 /** english fields */                 
                 , 'ss_title'
                 , 'sm_author'
                 , 'sm_publisher'                 
                 , 'ss_pubdate'
                 , 'sm_partner'
                 , 'sm_subject' 
                 
                 /** arabic fields */
                 , 'ss_ar_title'
                 , 'sm_ar_author'
                 , 'sm_ar_publisher'
                 , 'sm_ar_publication_date'
                 , 'sm_ar_partner'
                 , 'sm_ar_subject'

                 /** sort fields */
                 , 'score'
                 , 'ss_longlabel'
                 , 'ss_ar_title'      
                 , 'ss_sauthor'
                 , 'ss_ar_sauthor'           
                 
            ]

        Y.one('body').addClass('io-loading');

        if ( options.page ) {
            page = parseInt( options.page, 10 );
        }

        if ( options.language ) {
            language = options.language;
        }

        if ( options.start ) {
            start = parseInt( options.start, 10 );
        }

        if ( options.rows ) {
            rows = parseInt( options.rows, 10 );
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
                           
        options.container.empty();

        Y.jsonp( datasourceURLs, {
            on: {
                success: onSuccess,
                failure: onFailure,
                timeout: onTimeout
            },
            args: options,
            timeout: 3000
        });
    
    }
    
    router.replace( getRoute () );
    
    Y.one('body').delegate('change', onSelectChange, '#browse-select');

});