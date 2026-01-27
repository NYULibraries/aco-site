<?php

use App\Services\SolrService;
use Solarium\Client;

describe('SolrService BuilQuery', function () {

  it('checks the solr service class and methods exist', function () {
    expect(class_exists(SolrService::class))->toBeTrue();
    expect(method_exists(SolrService::class, 'search'))->toBeTrue();
    expect(method_exists(SolrService::class, 'buildQuery'))->toBeTrue();
  });

  it('compares all queries: ', function ($oldURL, $builderParams) {
    // import the solr service
    $solrService = app(SolrService::class);

    $fieldSelect = $builderParams['fieldSelect']; // for anyField
    $query = $builderParams['query'];
    $scopeIs = $builderParams['scopeIs'];
    $sortField = $builderParams['sortField'];
    $sortDir = $builderParams['sortDir'];
    $rowStart = $builderParams['rowStart'];
    $rows = $builderParams['rows'];

    // base URL to compare
    $parsedURL = $solrService->parseSolrUrl($oldURL);

    // call the buildQuery method
    $request = $solrService->buildQuery($fieldSelect, $query, $scopeIs, $sortField, $sortDir, $rowStart, $rows);

    // compare QUERY
    $newQuery = $request->getQuery();
    expect($parsedURL['q'])->toBe($newQuery);

    // compare FQ
    $filterQueries = $request->getFilterQueries();
    // since filterqueries are hashed in creation we will extract values
    $fqValues = [];
    foreach ($filterQueries as $filterQuery) {
      $fqValues[] = $filterQuery->getOptions()['query'];
    }
    expect($parsedURL['fq'])->toEqualCanonicalizing($fqValues);

    // compare SORTFIELD
    $newSort = $request->getSorts();
    foreach ($newSort as $key => $value) {
      expect($key)->toBe($sortField);
      expect($value)->toBe($sortDir);
      break; // should only be one iteration
    }

    // compare ROW START AND ROWS
    $options = $request->getOptions();
    expect($options['start'])->toBe($rowStart);
    expect($options['rows'])->toBe($rows);

    // compare FIELD LIST fl
    $params = $request->getParams();
    expect($params['fl'])->toBe("*"); // field list is always *, nothing more or less
  })->with([
    // oldurl, [params for builder,]
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998658109_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))",
      [
        'fieldSelect' => 'q',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998709194_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))",
      [
        'fieldSelect' => 'q',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    // this one errors out for some reason with a NAN at the end of the original URL???
    // [
    //   "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998738928_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=(content_und:%22arabs%22%20OR%20content_und_ws:%22arabs%22%20OR%20content_en:%22arabs%22%20OR%20content:%22NaN%22)",
    //   [
    //     'fieldSelect' => 'q',
    //     'scopeIs' => 'matches',
    //     'query' => 'arabs',
    //     'sortField' => 'score',
    //     'sortDir' => 'desc',
    //     'rowStart' => 0,
    //     'rows' => 10,
    //   ]
    // ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998788005_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_title_long:%22arabs%22%20OR%20ts_title_long:%22arabs%22%20OR%20tusar_title_long:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998818570_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_title_long:%22arabs%22%20OR%20ts_title_long:%22arabs%22%20OR%20tusar_title_long:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999156213_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tks_title_long:%22arabs%22%20OR%20tks_ar_title_long:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999009533_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_author:%22arabs%22%20OR%20tm_author:%22arabs%22%20OR%20tumar_author:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999037403_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_author:%22arabs%22%20OR%20tm_author:%22arabs%22%20OR%20tumar_author:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999059009_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_author:%22arabs%22%20OR%20tkm_ar_author:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999084236_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_topic:%22arabs%22%20OR%20tm_topic:%22arabs%22%20OR%20tumar_topic:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999098737_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_topic:%22arabs%22%20OR%20tm_topic:%22arabs%22%20OR%20tumar_topic:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999113855_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22arabs%22%20OR%20tkm_ar_topic:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999130268_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_publisher:%22arabs%22%20OR%20tm_publisher:%22arabs%22%20OR%20tumar_publisher:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999178857_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_publisher:%22arabs%22%20OR%20tm_publisher:%22arabs%22%20OR%20tumar_publisher:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999195814_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_publisher:%22arabs%22%20OR%20tkm_ar_publisher:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999211756_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_publocation:%22arabs%22%20OR%20ts_publocation:%22arabs%22%20OR%20tusar_publocation:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999225409_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_publocation:%22arabs%22%20OR%20ts_publocation:%22arabs%22%20OR%20tusar_publocation:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999241659_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tks_publocation:%22arabs%22%20OR%20tks_ar_publocation:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999257738_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_provider_label:%22arabs%22%20OR%20tm_provider_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999294795_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_provider_label:%22arabs%22%20OR%20tm_provider_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999306948_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_provider_label:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999320914_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_subject_label:%22arabs%22%20OR%20tm_subject_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scopeIs' => 'containsAny',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999334943_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_subject_label:%22arabs%22%20OR%20tm_subject_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scopeIs' => 'containsAll',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999346845_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_subject_label:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scopeIs' => 'matches',
        'query' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'rowStart' => 0,
        'rows' => 10,
      ]
    ],
  ]);
});
