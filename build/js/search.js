/* jshint laxcomma: true */
YUI().use(
  'node', 
  'event', 
  'handlebars', 
  'jsonp', 
  'router', 
  'gallery-paginator', 
  'anim', 
  'querystring',
  function(Y) {
    'use strict';

    Y.Object.each(window.HandlebarsHelpers(), function(helper, key) {
      Y.Handlebars.registerHelper(key, helper);
    });

    var itemsTemplateSource = Y.one('#items').getHTML();
    var itemsTemplate = Y.Handlebars.compile(itemsTemplateSource);
    var nrSource = Y.one('#noresults').getHTML();
    var noresultsTemplate = Y.Handlebars.compile(nrSource);
    var slSource = Y.one('#searchtips').getHTML();
    var searchlandingTemplate = Y.Handlebars.compile(slSource);
    var router = new Y.Router();
    var transactions = [];
    var defaultRpp = "10";
    var defaultSort = "score";
    var defaultScope = "matches";
    var initialQs = window.location.search.substring(1);
    var QueryString = Y.QueryString.parse(initialQs);

        if (initialQs !== "")
        {
            router.route(router.getPath(), function(req)
            {
                var node = Y.one('[data-name="items"]'),
                    data = node.getData(),
                    rpp = (req.query.rpp) ? req.query.rpp : ((data.rpp) ? data.rpp : defaultRpp),
                    sort = (req.query.sort) ? req.query.sort : ((data.sort) ? data.sort : "score desc"),
                    page = (req.query.page) ? parseInt(req.query.page, 10) : 0,
                    query = (req.query.q) ? req.query.q : '',
                    scopeIs = (req.query.scope) ? req.query.scope : defaultScope,
                    provider = (req.query.provider) ? req.query.provider : '',
                    category = (req.query.category) ? req.query.category : '',
                    author = (req.query.author) ? req.query.author : '',
                    title = (req.query.title) ? req.query.title : '',
                    publisher = (req.query.publisher) ? req.query.publisher : '',
                    subject = (req.query.subject) ? req.query.subject : '',
                    pubplace = (req.query.pubplace) ? req.query.pubplace : '',
                    start = 0;

                if (page <= 1) {
                  start = 0;
                } else {
                  start = (page * rpp) - rpp;
                }

                initRequest({
                  container: node,
                  start: start,
                  page: page,
                  rpp: rpp,
                  sort: sort,
                  scopeIs: scopeIs,
                  q: window.removeQueryDiacritics(query),
                  provider: window.removeQueryDiacritics(provider).toLowerCase(),
                  category: window.removeQueryDiacritics(category).toLowerCase(),
                  author: window.removeQueryDiacritics(author).toLowerCase(),
                  title: window.removeQueryDiacritics(title).toLowerCase(),
                  publisher: window.removeQueryDiacritics(publisher).toLowerCase(),
                  subject: window.removeQueryDiacritics(subject).toLowerCase(),
                  pubplace: window.removeQueryDiacritics(pubplace).toLowerCase()
                });

            });
        }
        else {
            var node = Y.one('[data-name="items"]');
            node.append(searchlandingTemplate());
        }

        function getRoute() {
          var route = router.getPath() + '?';
          var newString = Y.QueryString.stringify(QueryString);
          route += newString;
          return route;
        }

        function onSelectChangeSort() {
          var sortData = Y.one('#sort-select-el :checked'),
                sortBy = sortData.get('value'),
                sortDir = sortData.getAttribute("data-sort-dir"),
                sortString = sortBy + "%20" + sortDir;
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            QueryString.sort = sortString;
            router.replace(getRoute());
        }

        function onSelectChangeRpp() {
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            var rppData = Y.one('#rpp-select-el :checked'),
                rowsPerPage = rppData.get('value'),
                numfound = Y.one('.numfound').get('innerHTML'),
                page = 1;

            QueryString.page = page;
            QueryString.rpp = rowsPerPage;

            initPaginator(page, numfound, rowsPerPage)

            router.replace(getRoute());
        }

        function onFailure(_response, args) {
          var data = args.container.getData();
          var requestError = data.requesterror;
          if (!requestError) {
            args.container.setAttribute('data-requesterror', 1);
            requestError = 1;
          } else {
            requestError = parseInt(requestError, 10) + 1;
            args.container.setAttribute('data-requesterror', requestError);
          }
          /** there try 3 more times before giving up */
          if (requestError < 3) {
            router.replace(getRoute());
          }
        }

        function onTimeout() {
          onFailure();
        }

        function updateFormElements() {
          var i = 1;
          var cleanstring;
          var str;
          var regex = /(.*)\%20(.*)/i;
          var found;

          for (var x in QueryString) {
            if (QueryString.hasOwnProperty(x) && x == 'rpp') {
              var rppselect = Y.one('#rpp-select-el');
              if (rppselect) {
                rppselect.set('value', QueryString[x]);
              }
            }
            else if (QueryString.hasOwnProperty(x) && x === 'scope') {
              var scopeselect = Y.one('.scope-select');
              if (scopeselect) {
                scopeselect.set('value', QueryString[x]);
              }
            }
            else if (QueryString.hasOwnProperty(x) && x == "sort") {
              var sortselect = Y.one('#sort-select-el');
                    str = QueryString[x];
                    found = str.match(regex);
                    if (sortselect)
                    {
                        sortselect.set('value', found[1]);
                    }
                }
                if (QueryString.hasOwnProperty(x) && x !== "scope" && x !== "sort" && x !== "page" && x !== "rpp")
                {
                    var thisValueBox = Y.one('.group' + i + ' .q' + i);
                    if (thisValueBox)
                    {
                        str = QueryString[x];
                        cleanstring = removeSOLRcharacters(str);
                        Y.one('.group' + i + ' .q' + i).set('value', cleanstring);
                    }
                    var selectField = Y.one('.group' + i + ' .field-select');
                    if (selectField)
                    {
                        selectField.set('value', x);
                    }
                    //Y.log(i + " updateFormElements cleanstring " + x + " is  " + cleanstring);
                    i++;
                }
            }

        }


        function update(state)
        {
            this.setPage(state.page, true);
            this.setRowsPerPage(state.rowsPerPage, true);
           // Y.log("Function update page " + state.page + " rowsPerPage " + state.rowsPerPage);
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            QueryString.page = state.page ? state.page : 1;
            var newPath = router.getPath() + '?';
            var newString = Y.QueryString.stringify(QueryString);
            newPath += newString;
            Y.log("Save update " + newPath);
            router.save(newPath);
        }

        function initPaginator(page, totalRecords, rowsPerPage)
        {
            Y.one('#paginator').empty();
            page = parseInt(page);
            totalRecords = parseInt(totalRecords);
            rowsPerPage = parseInt(rowsPerPage);
            var paginatorConfiguration = {
                    totalRecords: totalRecords,
                    rowsPerPage: rowsPerPage,
                    initialPage: page,
                    template: '{FirstPageLink} {PageLinks} {NextPageLink}'
                },
            paginator = new Y.Paginator(paginatorConfiguration);
            paginator.on('changeRequest', update);
            if (totalRecords > rowsPerPage)
            {
                paginator.render('#paginator');
            }
        }

        function removeSOLRcharacters(str)
        {
            var outString = str.replace(/[*"]/gi, '');
            // Y.log(" removeSOLRcharacters returning " + outString);
            return outString;
        }

        function onSuccess(response, args)
        {
            updateFormElements();

            try
            {
                var node = args.container,

                    page = (args.page) ? args.page : 1,
                    numfound = parseInt(response.response.numFound, 10),

                    start = parseInt(response.response.start, 10),
                    displayStart = (start < 1) ? 1 : (start + 1),
                    rpp = QueryString.rpp,
                    docslength = parseInt(response.response.docs.length, 10),
                    q = QueryString.q,
                    pS = QueryString.provider,
                    cS = QueryString.category,
                    tS = QueryString.title,
                    aS = QueryString.author,
                    pubS = QueryString.publisher,
                    subS = QueryString.subject,
                    scopeIs = args.scopeIs,
                    pubplaceS = QueryString.pubplace,
                    appRoot = Y.one('body').getAttribute('data-app'),
                    ADescribeSearch = [],
                    stringToDescribeSearch = "";
                Y.one('body').removeClass('io-loading');

                if (q)
                {
                    ADescribeSearch.push(q);
                }
                if (tS)
                {
                    tS = removeSOLRcharacters(tS);
                    ADescribeSearch.push("Title " + scopeIs + " " + tS);
                }
                if (pS)
                {
                    pS = removeSOLRcharacters(pS);
                    ADescribeSearch.push(" Provider " + scopeIs + " " + pS);
                }
                if (cS)
                {
                    cS = removeSOLRcharacters(cS);
                    ADescribeSearch.push(" Category " + scopeIs + " " + cS);
                }

                if (aS)
                {
                    aS = removeSOLRcharacters(aS);
                    ADescribeSearch.push(" Author " + scopeIs + " " + aS);
                }
                if (pubS)
                {
                    pubS = removeSOLRcharacters(pubS);
                    ADescribeSearch.push(" Publisher " + scopeIs + " " + pubS);
                }
                if (subS)
                {
                    subS = removeSOLRcharacters(subS);
                    ADescribeSearch.push(" Subject " + scopeIs + " " + subS);
                }
                if (pubplaceS)
                {
                    pubplaceS = removeSOLRcharacters(pubplaceS);
                    ADescribeSearch.push(" Place of Publication " + scopeIs + " " + pubplaceS);
                }
                stringToDescribeSearch = ADescribeSearch.join((" and "));

                //Y.log("The number of results found: numfound " + numfound);
                if (numfound > 0)
                {

                    // render HTML and append to container
                    node.empty().append(
                        itemsTemplate(
                        {
                            items: response.response.docs,
                            app:
                            {
                                appRoot: appRoot
                            }
                        })
                    );
                    updateFormElements();

                    var resultsnum = Y.one('.resultsnum'),
                        querytextNode = Y.one('.s-query'),
                        numfoundNode = resultsnum.one('.numfound'),
                        startNode = resultsnum.one('.start'),
                        docslengthNode = resultsnum.one('.docslength');

                    ///
                    node.setAttribute("data-numFound", numfound);
                    node.setAttribute("data-start", start);
                    node.setAttribute("data-docsLength", docslength);
                    startNode.set('innerHTML', displayStart);
                    docslengthNode.set('innerHTML', start + docslength);
                    if (querytextNode)
                    {
                        querytextNode.set('innerHTML', stringToDescribeSearch);
                    }
                    numfoundNode.set('innerHTML', numfound);
                    var aboutInfoBox = function onAboutSearchClick(event)
                    {

                        event.preventDefault();
                        /* add the mustache content into the dropdown */
                        var node = Y.one('.about-info-content');
                        node.get('childNodes').remove();
                        node.append(searchlandingTemplate());
                        /*   add fx plugin to module body */
                        var content = Y.one('.about-info-content').plug(Y.Plugin.NodeFX,
                        {
                            from:
                            {
                                height: function(node)
                                {
                                    return node.get('scrollHeight');
                                }
                            },
                            to:
                            {
                                height: 0
                            },
                            easing: Y.Easing.easeBoth,
                            on:
                            {
                                start: function()
                                {
                                    var aboutlink = Y.all('.aboutinfo-link');
                                    aboutlink.removeClass('aboutinfo-link-available');
                                    aboutlink.toggleClass('open');
                                },
                                end: function()
                                {
                                    var aboutlink = Y.all('.aboutinfo-link');
                                    aboutlink.addClass('aboutinfo-link-available');
                                    node.toggleClass('open');
                                }
                            },
                            duration: 0.5
                        });
                        content.fx.set('reverse', !content.fx.get('reverse'));
                        content.fx.run();

                    };
                    Y.one('body').delegate('click', aboutInfoBox, '.aboutinfo-link-available');
                    // first transaction; enable paginator, update the new form elements, delegate events to the new form elements
                    if (transactions.length < 1)
                    {
                        initPaginator(page, numfound, docslength);
                        Y.one('body').delegate('change', onSelectChangeSort, '#sort-select-el');
                        Y.one('body').delegate('change', onSelectChangeRpp, '#rpp-select-el');
                        var sortselect = Y.one('#sort-select-el');
                        if (sortselect)
                        {
                            sortselect.set('value', defaultSort);
                        }


                    }
                    // store called to avoid making the request multiple times
                    transactions.push(this.url);
                    Y.one('body').removeClass('items-no-results');
                    args.container.setAttribute('data-requesterror', 0);
                }
                // no results
                else
                {
                   // Y.log("nothing found ");
                    Y.one('body').addClass('items-no-results');
                    updateFormElements();
                    node.append(noresultsTemplate());
                    node.append(searchlandingTemplate());
                }
            }
            catch (e)
            {
                Y.log("Error: " + e);
            }
        }

        function initRequest(options)
        {
            var start = 0,
                page = 0,
                sortBy = options.sort,
                rpp = options.rpp,
                scopeIs = options.scopeIs,
                data = options.container.getData(),
                source = Y.one('.widget.items').getAttribute('data-source'),
                qs = '',
                fl = (data.fl) ? data.fl : '*',
               // scope = Y.one('.widget.items').getAttribute('data-scope'),
                fq = [];
            Y.one('body').addClass('io-loading');
            /** find all data-fq and push the value into fq Array*/
            for (var prop in data)
            {
                if (data.hasOwnProperty(prop))
                {
                    if (prop.match('fq-'))
                    {
                        //Y.log("data[prop]: " + data[prop]);
                        fq.push(prop.replace('fq-', '') + ':' + data[prop]);
                    }
                }
            }

            if (options.title)
            {
                if (scopeIs == "matches")
                {
                    fq.push('(tks_title_long:"' + options.title + '" OR ' + 'tks_ar_title_long:"' + options.title + '")');
                }
                else
                {
                    var title_words=options.title.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < title_words.length; ++index) {
                      query_str=query_str+'(tus_title_long:"' + title_words[index] +'" OR ' + 'ts_title_long:"' + title_words[index]+ '" OR ' + 'tusar_title_long:"' + title_words[index]+'")';
                      if(index<(title_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                             query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }
            if (options.author)
            {
                if (scopeIs == "matches")
                {
                    fq.push('(tkm_author:"' + options.author + '" OR ' + 'tkm_ar_author:"' + options.author + '")');
                }
                else
                {
                    var author_words=options.author.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < author_words.length; ++index) {
                      query_str=query_str+'(tum_author:' + '"'+author_words[index]+'"' + ' OR ' + 'tm_author:"' + author_words[index] +'" OR ' + 'tumar_author:"' + author_words[index]+'")';
                      if(index<(author_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                           query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }
            if (options.pubplace)
            {
                if (scopeIs === "matches")
                {
                    fq.push('(tks_publocation:"' + options.pubplace + '" OR ' + 'tks_ar_publocation:"' + options.pubplace + '")');
                }
                else
                {
                    var pubplace_words=options.pubplace.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < pubplace_words.length; ++index) {
                      query_str=query_str+'(tus_publocation:' + '"'+pubplace_words[index]+ '" OR ' + 'ts_publocation:"' + pubplace_words[index]+  '" OR ' + 'tusar_publocation:"' + pubplace_words[index]+'")';
                      if(index<(pubplace_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                           query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }

            if (options.publisher)
            {
                if (scopeIs === "matches")
                {
                    fq.push('(tkm_publisher:"' + options.publisher + '" OR ' + 'tkm_ar_publisher:"' + options.publisher + '")');
                }
                else
                {
                    var publisher_words=options.publisher.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < publisher_words.length; ++index) {
                      query_str=query_str+'(tum_publisher:"'+publisher_words[index]+'" OR ' + 'tm_publisher:"' + publisher_words[index]+'" OR ' + 'tumar_publisher:"' + publisher_words[index]+'")';
                      if(index<(publisher_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                           query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }

            if (options.category)
{
                if (scopeIs === "matches")
                {
                    fq.push('(tkm_topic:"' + options.category + '" OR ' + 'tkm_ar_topic:"' + options.category + '")');
                }
                else
                {
                    var category_words=options.category.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < category_words.length; ++index) {
                      query_str=query_str+'(tum_topic:"'+category_words[index]+'" OR '+'tm_topic:"'+category_words[index]+'" OR ' + 'tumar_topic:"' + category_words[index]+'")';

                      if(index<(category_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                           query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }
            if (options.provider)
            {
                if (scopeIs === "matches")
                {
                    fq.push('(tkm_provider_label:"' + options.provider + '")' );
                }
                else
                {
                    var provider_words=options.provider.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < provider_words.length; ++index) {
                      query_str=query_str+'(tum_provider_label:"'+provider_words[index]+'" OR '+'tm_provider_label:"'+provider_words[index]+'")';
                      if(index<(provider_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                           query_str=query_str+' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }
            if (options.subject)
            {
                if (scopeIs === "matches")
                {
                    fq.push('(tkm_subject_label:"' + options.subject + '")');
                }
                else
                {
                    var subject_words=options.subject.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < subject_words.length; ++index) {
                      query_str=query_str+'(tum_subject_label:' + '"'+subject_words[index]+'" OR '+ 'tm_subject_label:"'+subject_words[index]+'")';
                      if(index<(subject_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                             query_str=query_str+ ' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
                    fq.push(query_str);
                }
            }
            if (options.page)
            {
                page = parseInt(options.page, 10);
            }
            if (options.start)
            {
                start = parseInt(options.start, 10);
            }
            if (options.rpp)
            {
                rpp = parseInt(options.rpp, 10);
            }
            qs = "?" + "wt=json" + "&json.wrf=callback={callback}" + "&fl=*" + "&fq=" + fq.join("&fq=") + "&rows=" + rpp + "&start=" + start + "&sort=" + sortBy;
            if (options.q)
            {
                var query_str='';
                if (scopeIs == "matches")
                {
                    query_str = '(content_und:"' + options.q + '" OR ' + 'content_und_ws:"' + options.q + '" OR ' + 'content_en:"' + options.q + '" OR ' + 'content:"' + +options.q+'")';
                }
                else
                {
                    var query_words=options.q.split(" ");
                    var index;
                    var query_str='(';
                    for (index = 0; index < query_words.length; ++index) {
                      query_str=query_str+'(content_und:' +query_words[index]+' OR '+ 'content_und_ws:'+query_words[index]+' OR ' + 'content_en:' + query_words[index] + ' OR ' + 'content:' + query_words[index]+')';
                      if(index<(query_words.length-1))
                      {
                          if (scopeIs === "containsAny")
                          {
                             query_str=query_str+ ' OR ';
                          }
                          else
                          {
                             query_str=query_str+ ' AND ';
                          }
                      }
                    }
                    query_str=query_str+')';
               }
                    qs = qs + '&q=' + query_str;
            }
            else
            {
                qs = qs + '&q=*';
            }

            //Y.log("**** Sending to Solr: " + qs);
            source = source + qs;


            options.container.empty();
            Y.jsonp(source,
            {
                on:
                {
                    success: onSuccess,
                    failure: onFailure,
                    timeout: onTimeout
                },
                args: options,
                timeout: 3000
            });
        }
        router.replace(getRoute());

    });
