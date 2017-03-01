/* jshint laxcomma: true */
YUI().use(
    'node', 'event', 'handlebars', 'jsonp', 'router', 'gallery-paginator', 'anim', 'querystring',
    function(Y) {
        'use strict';
        var itemsTemplateSource = Y.one('#items').getHTML(),
            itemsTemplate = Y.Handlebars.compile(itemsTemplateSource),
            router = new Y.Router(),
            defaultSort = 'ds_created',
            transactions = [],
            QueryString = Y.QueryString.parse(window.location.search.substring(1));

        function HandlebarsHelpers() {
            function json(context, options) {
                return options.fn(JSON.parse(context));
            }

            function speakingurl(context, options) {
                return window.getSlug(this.label);
            }

            function ifempty(fieldtocheck, defaultvalue) {
               // Y.log("inside ifempty " + fieldtocheck);
                if (fieldtocheck) {
                    return;
                } else {
                    return defaultvalue;
                }
                // return "" ? fieldtocheck : defaultvalue;

            }

            return {
                ifempty: ifempty,
                json: json,
                speakingurl: speakingurl
            };
        }

        Y.Object.each(HandlebarsHelpers(), function(helper, key) { Y.Handlebars.registerHelper(key, helper); });

        function getRoute() {
            var route = router.getPath() + '?';
            var newString = Y.QueryString.stringify(QueryString);
            route += newString;
            Y.log("getRoute: route  " + route);
            return route;
        }

        router.route(router.getPath(), function(req) {
            var node = Y.one('[data-name="items"]'),
                data = node.getData(),
                rpp = (req.query.rpp) ? req.query.rpp : ((data.rpp) ? data.rpp : "10"),
                sort = (req.query.sort) ? req.query.sort : ((data.sort) ? data.sort : "ds_created desc"),
                page = (req.query.page) ? parseInt(req.query.page, 10) : 0,
                start = 0;
                Y.log("Sort is " + sort);
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
            Y.log(" onSelectChangeSort " + onSelectChangeSort);
            var sortData = Y.one('#sort-select-el :checked'),
                sortBy = sortData.get('value'),
                sortDir = sortData.getAttribute("data-sort-dir"),
                sortString = sortBy + "%20" + sortDir;
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            QueryString.sort = sortString;
            router.replace(getRoute());
        }

        function onSelectChangeRpp() {

            var rppData = Y.one('#rpp-select-el :checked'),
                rppNum = rppData.get('value');
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            QueryString.rpp = rppNum;
            Y.log(" QueryString.rpp " + QueryString.rpp);
            router.replace(getRoute());
        }

        function onFailure(response, args) {

            // mover a onFailure
            var data = args.container.getData(),
                requestError = data.requesterror;

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
            var str,
                rppselect,
                sortselect,
                found,
                re3 = /(.*)\%20(.*)/i;
            for (var x in QueryString) {

                if (QueryString.hasOwnProperty(x) && x == "rpp") {
                    Y.log("QueryString[x] RPP " + QueryString[x]);
                    rppselect = Y.one('#rpp-select-el');
                    if (rppselect) {
                        rppselect.set('value', QueryString[x]);
                    }
                } else if (QueryString.hasOwnProperty(x) && x == "sort") {
                    Y.log("QueryString[x] sort " + QueryString[x]);
                    sortselect = Y.one('#sort-select-el');
                    str = QueryString[x];
                    found = str.match(re3);
                    //  Y.log("@@@   1" + found[0] + " 2 " +  + " 3 " + found[2]);
                    if (sortselect) {
                        sortselect.set('value', found[1]);
                          Y.log("@@@   1" + found[0] + " 2 " + found[1] + " 3 " + found[2]);
                    }
                }
            }
        }

        function update(state) {
            this.setPage(state.page, true);
            this.setRowsPerPage(state.rowsPerPage, true);
            Y.log("Function update page " + state.page + " rowsPerPage " + state.rowsPerPage);
            QueryString = Y.QueryString.parse(window.location.search.substring(1));
            QueryString.page = state.page ? state.page : 1;
            var newPath = router.getPath() + '?';
            var newString = Y.QueryString.stringify(QueryString);
            newPath += newString;
            Y.log("update! new path is " + newPath);
            router.save(newPath);
        }


        function initPaginator(page, totalRecords, rowsPerPage) {

            Y.one('#paginator').empty();
            var paginatorConfiguration = {
                    totalRecords: totalRecords,
                    rowsPerPage: rowsPerPage,
                    initialPage: page,
                    template: '{FirstPageLink} {PageLinks} {NextPageLink}'
                },
                paginator = new Y.Paginator(paginatorConfiguration);

            paginator.on('changeRequest', update);

            if (totalRecords > rowsPerPage) {
                paginator.render('#paginator');
            }

        }

        function onSuccess(response, args) {
              Y.log("onSuccess call. ");
            try {

                var node = args.container,

                    page = (args.page) ? args.page : 1,
                    numfound = parseInt(response.response.numFound, 10),


                    start = parseInt(response.response.start, 10),
                    displayStart = (start < 1) ? 1 : (start + 1),

                    docslength = parseInt(response.response.docs.length, 10),
                    appRoot = Y.one('body').getAttribute('data-app');



                node.setAttribute('data-numFound', numfound);
                node.setAttribute('data-start', start);
                node.setAttribute('data-docsLength', docslength);


                Y.log("numfound " + numfound);
                if (numfound > 0) {
                    // first transaction; enable paginator

                    node.empty().append(
                        itemsTemplate({
                            items: response.response.docs,
                            app: { appRoot: appRoot }
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
                    if (transactions.length < 1) {
                        initPaginator(page, numfound, docslength);
                   
                        var sortselect = Y.one('#sort-select-el');
                        if (sortselect) { 
                            sortselect.set('value', defaultSort); 
                         }
                        // Sorting dropdown 
                        Y.one('body').delegate('change', onSelectChangeSort, '#sort-select-el');
                        Y.one('body').delegate('change', onSelectChangeRpp, '#rpp-select-el');
                    }
                }

                args.container.setAttribute('data-requesterror', 0);
                Y.one('body').removeClass('io-loading');
            } catch (e) {
                Y.log("Error: " + e);
            }

        }

        function initRequest(options) {

            for (var w in options) {
                if (options.hasOwnProperty(w)) {
                    Y.log("options[w]: " + w + "  " + options[w]);
                }
            }
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
            for (var prop in data) {
                if (data.hasOwnProperty(prop)) {
                    if (prop.match('fq-')) {
                        fq.push(prop.replace('fq-', '') + ':' + data[prop]);
                    }
                }
            }


            if (options.page) {
                page = parseInt(options.page, 10);
            }


            if (options.start) {
                start = parseInt(options.start, 10);
            }

            if (options.rpp) {
                rpp = parseInt(options.rpp, 10);
            }

            source = source + "?" + "wt=json" + "&json.wrf=callback={callback}" + "&fl=" + fl + "&fq=" + fq.join("&fq=") + "&rows=" + rpp + "&start=" + start + "&sort=" + sortBy;

            options.container.empty();

            Y.jsonp(source, {
                on: {
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