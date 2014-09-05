YUI().use(
    'node'
  , 'event'
  , 'io'
  , 'node-scroll-info'
  , 'handlebars'
  , 'json-parse'
  , 'jsonp'
  , 'paginator'
  , 'jsonp-url'
  , 'gallery-idletimer'
  , function (Y) {

    'use strict'
                      
    var body = Y.one('body')
      , container = Y.all('[data-name="items"]')
      , searchString = '*:*'
      , transactions = []
      , fold = 200
      , language = 'und' // find a better solution
      , appRoot = body.getAttribute("data-app")
      , templates = {}
      , template      
      , lazyLoad = 0
      , rows = 10
      , query = 10  
      , paginator = 1
      , datasourceURLs = "http://dev-discovery.dlib.nyu.edu:8080/solr3_discovery/core0/"
                       + "select?wt=json&json.wrf=callback={callback}&fq=hash:iy26sh&fq="
                       + "ss_collection_identifier:7b71e702-e6b8-4f09-90c9-e5c2906f3050&"
                       + "fq=ss_language:en&fl=ss_embedded,title,type,"
                       + "ss_collection_identifier,ss_identifer,ss_representative_image,"
                       + "teaser,sm_field_author,sm_field_title,ss_language,"
                       + "sm_field_publication_date_text,sm_field_publication_location,"
                       + "sm_field_publisher,sm_vid_Terms,tm_vid_1_names,sm_ar_title,"
                       + "sm_ar_author,sm_ar_publisher,sm_ar_publication_location,"
                       + "sm_ar_subjects,sm_ar_publication_date,sm_ar_partner,"
                       + "sm_field_partner"
                       + "&rows=" + rows      

    function initRequest ( options ) {

         // make the first request
         Y.jsonp(datasourceURLs, {
             on: {
                 success: onSuccess,
                 failure: onFailure,
                 timeout: onTimeout
             },
             args: options,
             timeout: 3000
         })
     
     }                       

    function onFailure() {
        Y.log('onFailure')
    }
    
    function onTimeout() {
        onFailure()
    }

    function onClick(e) {
        e.preventDefault()
        onScroll()
    }      
      
    function onScroll() {
    
        var numfound = 0
          , start = 0
          , docslength = 0
          , next = 0
          , sourceIndex = 0
          , sourceLength = 1
          , href
          
        if ( body.hasClass('io-done') ) return
          
        numfound = parseInt(container.getAttribute("data-numfound"), 10)

        start = parseInt(container.getAttribute("data-start"), 10)

        docslength = parseInt(container.getAttribute("data-docslength"), 10)
        
        next = ( start + docslength )
        
        if (
          next <= numfound
        ) {
        
            href = datasourceURLs + '&start=' + next
            
	        if (Y.Array.indexOf(transactions, href) < 0 && !body.hasClass('io-loading')) {
                
                if (
                    body.scrollInfo.getScrollInfo().atBottom ||
                    (
                        Y.IdleTimer.isIdle() && pager.get('region').top - fold < body.get('winHeight')
                    )
                ) {
             
                  body.addClass('io-loading')
                  
                  Y.jsonp( href, {
                    on: {
                      success: onSuccess,
                      failure: onFailure,
                      timeout: onTimeout
                    },
                    timeout: 3000
                  })

                }
            }

        }
        
    }
    
    function onSuccess( response, args ) {
    
        try {
        
            var node = args.container
              , data = node.getData()
              , numfound = parseInt(response.response.numFound, 10)
              , start = parseInt(response.response.start, 10)
              , docslength = parseInt(response.response.docs.length, 10)
              , language = 'und'

            if ( data.language ) {
                language = data.language
            }
            
            // store called to avoid making the request multiple times
            transactions.push( this.url )

            // for now, map this at Solr level and fix img to be absolute paths
            response.response.docs.forEach( function ( element, index, array ) {
                
                element.appRoot = appRoot
                
                element.identifier = element.ss_identifer

                element.app = element.ss_collection_identifier   
                
                element.thumbHref = element.ss_representative_image                

                element.language = {}

                element.ar = {}         
                
                element.en = {}
                
                // language

                element.language.title = element.sm_field_title

                element.language.author = element.sm_field_author

                element.language.publisher = element.sm_field_publisher                                 

                element.language.publication_location = element.sm_field_publication_location

                element.language.publication_date = element.sm_field_publication_date_text             

                element.language.subjects = element.sm_vid_Terms                  

                // english

                element.en.title = element.sm_field_title

                element.en.author = element.sm_field_author

                element.en.publisher = element.sm_field_publisher                                 

                element.en.publication_location = element.sm_field_publication_location

                element.en.publication_date = element.sm_field_publication_date_text             

                element.en.subjects = element.sm_vid_Terms  
                
                // arabic
                
                element.ar.title = element.sm_ar_title

                element.ar.author = element.sm_ar_author

                element.ar.publisher = element.sm_ar_publisher                              

                element.ar.publication_location = element.sm_ar_publication_location

                element.ar.publication_date = element.sm_ar_publication_date            

                element.ar.subjects = element.sm_ar_subjects
                
            })

            if ( paginator ) {
             
               if ( transactions.length === 1 ) {
               
                 // first transaction; enable paginator
               
                 // var pg = new Y.Paginator({ totalItems : numfound })

                 // http://yuilibrary.com/gallery-archive/gallery/show/paginator-view.html
                 // node.insert( templates['paginator']({ pages : [ { number: 1 } ]}), 'after' )
                 
               }
             
            }
             
            node.setAttribute("data-numFound", numfound)

            node.setAttribute("data-start", start)

            node.setAttribute("data-docsLength", docslength)
             
            // render HTML and append to container
            node.append(
              templates['items']({
                items : response.response.docs
              })
            )

            if ( 
                start + docslength === numfound 
            ) 
            {
                body.addClass('io-done')
            }
            
            else if ( 
                start + docslength === numfound  
            )
            {

                container.setAttribute("data-start", 0)

                container.setAttribute("data-docsLength", 0)
                
            }
            
            body.removeClass('io-loading')
            
        }
        catch (e) {
            Y.log('something went wrong. error')
        }
        
    }
    
    if ( lazyLoad === 1 ) {

        Y.IdleTimer.subscribe('idle', onScroll)

        // be opportunistic
        Y.IdleTimer.start(5000)

        // Plug ScrollInfo 
        body.plug(Y.Plugin.ScrollInfo, { scrollMargin: fold })

        body.scrollInfo.on({ scroll: onScroll })

    }
    
    container.each( function (node) {

        var data = node.getData()
        
        node.setAttribute("data-id", Y.guid() )
        
        if ( data.lazyload ) {
          lazyLoad = parseInt( data.lazyload, 10 )
        }

        if ( data.language ) {
          language = data.language
        }
          
        if ( data.rows ) {
          rows = parseInt( data.rows, 10 )
        }    
      
        if ( data.paginator ) {
          paginator = parseInt( data.paginator, 10 )
        }
      
        if ( data.script ) {
          
          var files = JSON.parse(data.script);
          
          Y.Array.each( files.hbs, function( source ) {
            
            Y.Object.each( source, function( file, key ) {
                
                Y.io( body.getAttribute("data-app") + '/js/' + file, {
  			      sync: false,
  			      on: {
                        success: function( transactionId, response ) {
                          templates[key] = Y.Handlebars.compile(response.responseText)
  				      },
                        failure:function() {
  					      throw "Handlebars: failed to retrieve url: " + url;
  				      }
  			      },
  			      context: this
                })

            })
  		  
          })

        }
        
        initRequest ( { container : node } )

    })    

})