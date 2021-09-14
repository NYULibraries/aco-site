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
    
    var router = new Y.Router();
    
    var defaultSort = 'ds_created';
    
    var transactions = [];
    
    var QueryString = Y.QueryString.parse(window.location.search.substring(1));

    function getRoute() {
      return router.getPath() + '?' + Y.QueryString.stringify(QueryString);
    }

    router.route(router.getPath(), function(req) {
      var node = Y.one('[data-name="items"]');
      var data = node.getData();
      var rpp = (req.query.rpp) ? req.query.rpp : ((data.rpp) ? data.rpp : '10');
      var sort = (req.query.sort) ? req.query.sort : ((data.sort) ? data.sort : 'ds_created desc');
      var page = (req.query.page) ? parseInt(req.query.page, 10) : 0;
      var start = 0;

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
        sort: sort
      });

    });

    function onSelectChangeSort() {
      var sortData = Y.one('#sort-select-el :checked');
      var sortBy = sortData.get('value');
      var sortDir = sortData.getAttribute('data-sort-dir');
      var sortString = sortBy + '%20' + sortDir;
      QueryString = Y.QueryString.parse(window.location.search.substring(1));
      QueryString.sort = sortString;
      router.replace(getRoute());
    }

    function onSelectChangeRpp() {
      var rppData = Y.one('#rpp-select-el :checked');
      var rppNum = rppData.get('value');
      QueryString = Y.QueryString.parse(window.location.search.substring(1));
      QueryString.rpp = rppNum;
      router.replace(getRoute());
    }

    function onFailure(_response, args) {
      // mover a onFailure
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
      } else {
        Y.log('onFailure: there was a problem with this request');
      }
    
    }

    function onTimeout() {
      onFailure();
    }

    function updateFormElements() {
      var str;
      var rppselect;
      var sortselect;
      var found;
      var re3 = /(.*)\%20(.*)/i;
      for (var x in QueryString) {
        if (QueryString.hasOwnProperty(x) && x == 'rpp') {
          rppselect = Y.one('#rpp-select-el');
          if (rppselect) {
            rppselect.set('value', QueryString[x]);
          }
        } else if (QueryString.hasOwnProperty(x) && x == 'sort') {
          sortselect = Y.one('#sort-select-el');
          str = QueryString[x];
          found = str.match(re3);
          if (sortselect) {
            sortselect.set('value', found[1]);
          }
        }
      }
    }

    function update(state) {
      this.setPage(state.page, true);
      this.setRowsPerPage(state.rowsPerPage, true);
      QueryString = Y.QueryString.parse(window.location.search.substring(1));
      QueryString.page = state.page ? state.page : 1;
      var newPath = router.getPath() + '?';
      var newString = Y.QueryString.stringify(QueryString);
      newPath += newString;
      router.save(newPath);
    }

    function initPaginator(page, totalRecords, rowsPerPage) {
      
      Y.one('#paginator').empty();
      
      var paginatorConfiguration = {
        totalRecords: totalRecords,
        rowsPerPage: rowsPerPage,
        initialPage: page,
        template: '{FirstPageLink} {PageLinks} {NextPageLink}'
      };
      
      var paginator = new Y.Paginator(paginatorConfiguration);

      paginator.on('changeRequest', update);

      if (totalRecords > rowsPerPage) {
        paginator.render('#paginator');
      }

    }

    function onSuccess(response, args) {
      try {
        var node = args.container;
        var page = (args.page) ? args.page : 1;
        var numfound = parseInt(response.response.numFound, 10);
        var start = parseInt(response.response.start, 10);
        var displayStart = (start < 1) ? 1 : (start + 1);
        var docslength = parseInt(response.response.docs.length, 10);
        var appRoot = Y.one('body').getAttribute('data-app');
        node.setAttribute('data-numFound', numfound);
        node.setAttribute('data-start', start);
        node.setAttribute('data-docsLength', docslength);
        if (numfound > 0) {
          node.empty().append(
            itemsTemplate({
              items: response.response.docs,
              app: {
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
                    startNode.set('innerHTML', displayStart);
                    docslengthNode.set('innerHTML', start + docslength);
                    numfoundNode.set('innerHTML', numfound);
                    if (transactions.length < 1)
                    {
                        initPaginator(page, numfound, docslength);

                        var sortselect = Y.one('#sort-select-el');
                        if (sortselect)
                        {
                            sortselect.set('value', defaultSort);
                        }
                        // Sorting dropdown 
                        Y.one('body').delegate('change', onSelectChangeSort, '#sort-select-el');
                        Y.one('body').delegate('change', onSelectChangeRpp, '#rpp-select-el');
                    }
                    transactions.push(this.url);
                }

                args.container.setAttribute('data-requesterror', 0);
                Y.one('body').removeClass('io-loading');
            }
            catch (e)
            {
                Y.log("Error: " + e);
            }

        }

        function initRequest(options) {
            var start = 0,
                page = 0,
                sortBy = options.sort,
                data = options.container.getData(),
                source = Y.one('.widget.items').getAttribute('data-source'),
                fl = (data.fl) ? data.fl : '*',
                rpp = (data.rpp) ? data.rpp : 10,
                fq = [];

            Y.one('body').addClass('io-loading');

            /** find all data-fq and push the value into fq Array*/
            for (var prop in data)
            {
                if (data.hasOwnProperty(prop))
                {
                    if (prop.match('fq-'))
                    {
                        fq.push(prop.replace('fq-', '') + ':' + data[prop]);
                    }
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

            source = source + "?" + "wt=json" + "&json.wrf=callback={callback}" + "&q=*"+"&fl=" + fl + "&fq=" + fq.join("&fq=") + "&rows=" + rpp + "&start=" + start + "&sort=" + sortBy;

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
