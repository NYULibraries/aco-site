YUI().use(
    'node'
  , 'event'
  , 'handlebars'
  , 'jsonp'
  , 'router'
  , 'gallery-paginator'
  , 'anim'
  , function (Y) {
	
    'use strict';

    var itemsTemplateSource = Y.one('#items').getHTML()
      , itemsTemplate = Y.Handlebars.compile(itemsTemplateSource)
      , router = new Y.Router()
      , transactions = [];
    
    function getRoute () {

        var pageQueryString = getParameterByName('page')
          , sortQueryString = getParameterByName('sort')
          , page = ( pageQueryString ) ? pageQueryString : 1
          , route = router.getPath() + '?page=' + page;

        if ( sortQueryString ) {
            route = route + '&sort=' + sortQueryString;
        }
        
        return route;

    }
      
    function getParameterByName(name) {
        
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)")
          , results = regex.exec(location.search);

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

    router.route( router.getPath(), function ( req ) {

        var node = Y.one('[data-name="items"]')
          , data = node.getData()
          , rows = ( req.query.rows ) ? req.query.rows : ( ( data.rows ) ? data.rows : 10 ) 
          , sort = ( req.query.sort ) ? req.query.sort : ( ( data.sort ) ? data.sort : Y.one('#browse-select').get('value') )           
          , page = ( req.query.page ) ? parseInt( req.query.page, 10 ) : 0
          , start =  0;

        if ( page <= 1 ) {
            start = 0;
        }
        else {
            start = ( page * rows ) - rows;
        }
        
    	initRequest ( {
		    container : node
	      , start : start
	      , page : page
    	  , rows : rows
    	  , sort : sort
		} );
        
    });
    
    function onSelectChange( ) {
        router.replace( getRoute() );
    }
    
    function onFailure( response, args ) {
    	
        // mover a onFailure
        var data = args.container.getData()
          , requestError = data.requesterror;
          
        if ( !requestError ) {
            args.container.setAttribute( 'data-requesterror', 1 );
            requestError = 1;
        }
        else { 
            requestError = parseInt(requestError, 10) + 1;
            args.container.setAttribute( 'data-requesterror', requestError );                
        }
    	
        /** there try 3 more times before giving up */
        if ( requestError < 3 ) {
            router.replace( getRoute () );
        }
        else {
        	Y.log('onFailure: there was a problem with this request');
        }
    }

    function onTimeout() {
        onFailure();
    }
    
    function update ( state ) {
	
    	this.setPage( state.page, true );
		
	    this.setRowsPerPage( state.rowsPerPage, true );
	    	
	    router.save( router.getPath() + '?page=' + state.page );

    }
    
    function initPaginator( page, totalRecords, rowsPerPage ) {
        
        var paginatorConfiguration = {
                totalRecords: totalRecords
              , rowsPerPage: rowsPerPage
              , initialPage : page
              , template: '{FirstPageLink} {PageLinks} {NextPageLink}'        
            }
          , paginator = new Y.Paginator( paginatorConfiguration );

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
              , displayStart = ( start < 1 ) ? 1 : (start + 1)
              , startNode = resultsnum.one('.start')
              , docslengthNode = resultsnum.one('.docslength')
              , docslength = parseInt(response.response.docs.length, 10)
              , appRoot = Y.one('body').getAttribute('data-app');
              
            // first transaction; enable paginator
            if ( transactions.length < 1 ) {
            	initPaginator( page , numfound, docslength );
            }

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
                app: { appRoot : appRoot }
              })
            );
            
            args.container.setAttribute( 'data-requesterror', 0 );

            Y.one('body').removeClass('io-loading');

        }

        catch (e) {

        }

    }

    function initRequest ( options ) {
    
        var start = 0
          , page = 0
          , sortData = Y.one('#browse-select :checked')
          , sortBy = sortData.get('value')
          , sortDir = sortData.getAttribute( "data-sort-dir" )
          , data = options.container.getData()
          , source = ( data.source ) ? data.source : null
          , fl = ( data.fl ) ? data.fl : '*'
          , rows = ( data.rows ) ? data.rows : 10
          , fq = [];

        Y.one('body').addClass('io-loading');
        
        /** find all data-fq and push the value into fq Array*/
        for ( var prop in data ) {
            if ( data.hasOwnProperty( prop ) ) {
          	    if ( prop.match('fq-') ) {
          	    	fq.push( prop.replace('fq-', '') + ':' + data[prop] );
        	    }
            }
        }

        if ( options.page ) {
            page = parseInt( options.page, 10 );
        }

        if ( options.start ) {
            start = parseInt( options.start, 10 );
        }

        if ( options.rows ) {
            rows = parseInt( options.rows, 10 );
        }
        
        source = source 
               + "?"
               + "wt=json"
               + "&json.wrf=callback={callback}"
               + "&fl=" + fl
               + "&fq=" + fq.join("&fq=")
               + "&rows=" + rows
               + "&start=" + start
               + "&sort=" + sortBy + "%20" + sortDir;

        options.container.empty();

        Y.jsonp( source, {
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
    
    // Sort
    Y.one('body').delegate('change', onSelectChange, '#browse-select');
    
  
    

});