<?php

use App\Services\SolrService;

describe('SolrService BuilQuery', function () {

  it('checks the solr service class and methods exist', function () {
    expect(class_exists(SolrService::class))->toBeTrue();
    expect(method_exists(SolrService::class, 'search'))->toBeTrue();
    expect(method_exists(SolrService::class, 'buildQuery'))->toBeTrue();
  });

  it('ensures old queries match: ', function ($oldURL, $builderParams) {
    // import the solr service
    $solrService = app(SolrService::class);

    $fieldSelect = $builderParams['fieldSelect']; // for anyField
    $searchString = $builderParams['searchString'];
    $scope = $builderParams['scope'];
    $sortField = $builderParams['sortField'];
    $sortDir = $builderParams['sortDir'];
    $start = $builderParams['start'];
    $rows = $builderParams['rows'];

    // base URL to compare
    $parsedURL = parseSolrUrl($oldURL);

    // call the buildQuery method
    $request = $solrService->buildQuery(
      fieldSelect: $fieldSelect,
      searchString: $searchString,
      scope: $scope,
      sortField: $sortField,
      sortDir: $sortDir,
      start: $start,
      rows: $rows
    );

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
    // removing periods and commas at the test level since we want to preserve them in the URL
    $normalizedUserProvidedValues = array_map(fn($f) => strtoLower(str_replace(['.', ','], '', $f)), $fqValues);
    $normalizedProductionSiteValues = array_map(fn($f) => strtoLower(str_replace(['.', ','], '', $f)), $parsedURL['fq']);
    expect($normalizedUserProvidedValues)->toEqualCanonicalizing($normalizedProductionSiteValues);

    // compare SORTFIELD
    $newSort = $request->getSorts();
    foreach ($newSort as $key => $value) {
      expect($key)->toBe($sortField);
      expect($value)->toBe($sortDir);
    }

    // compare ROW START AND ROWS
    $options = $request->getOptions();
    expect($options['start'])->toBe($start);
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
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // testing for phrases with whitespace
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1771971789804_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:jibran%20OR%20content_und_ws:jibran%20OR%20content_en:jibran%20OR%20content:jibran)%20OR%20(content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))",
      [
        'fieldSelect' => 'q',
        'scope' => 'containsAny',
        'searchString' => 'jibran arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998709194_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))",
      [
        'fieldSelect' => 'q',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // there is a NAN in the original URL caused by coercing a string with ++, this URL is changed to accomodate for that
    [
      // "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998738928_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=(content_und:%22arabs%22%20OR%20content_und_ws:%22arabs%22%20OR%20content_en:%22arabs%22%20OR%20content:%22arabs%22)",
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998738928_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=(content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs)",
      [
        'fieldSelect' => 'q',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // testing for phrases with whitespace
    [
      // "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998738928_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=(content_und:%22arabs%22%20OR%20content_und_ws:%22arabs%22%20OR%20content_en:%22arabs%22%20OR%20content:%22arabs%22)",
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1771971850872_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=(content_und:jibran%20arabs%20OR%20content_und_ws:jibran%20arabs%20OR%20content_en:jibran%20arabs%20OR%20content:jibran%20arabs)",
      [
        'fieldSelect' => 'q',
        'scope' => 'matches',
        'searchString' => 'jibran arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998788005_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_title_long:%22arabs%22%20OR%20ts_title_long:%22arabs%22%20OR%20tusar_title_long:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998818570_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_title_long:%22arabs%22%20OR%20ts_title_long:%22arabs%22%20OR%20tusar_title_long:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // testing for phrases with whitespace
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1771972031985_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22)%20AND%20(tus_title_long:%22arabs%22%20OR%20ts_title_long:%22arabs%22%20OR%20tusar_title_long:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scope' => 'containsAll',
        'searchString' => 'jibran arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999156213_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tks_title_long:%22arabs%22%20OR%20tks_ar_title_long:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // testing for phrases with whitespace
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1771972084851_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tks_title_long:%22jibran%20arabs%22%20OR%20tks_ar_title_long:%22jibran%20arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'title',
        'scope' => 'matches',
        'searchString' => 'jibran arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999009533_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_author:%22arabs%22%20OR%20tm_author:%22arabs%22%20OR%20tumar_author:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999037403_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_author:%22arabs%22%20OR%20tm_author:%22arabs%22%20OR%20tumar_author:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999059009_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_author:%22arabs%22%20OR%20tkm_ar_author:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'author',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999084236_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_topic:%22arabs%22%20OR%20tm_topic:%22arabs%22%20OR%20tumar_topic:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999098737_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_topic:%22arabs%22%20OR%20tm_topic:%22arabs%22%20OR%20tumar_topic:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999113855_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22arabs%22%20OR%20tkm_ar_topic:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Language and Literature - subcategory default search
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772056426218_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22language%20and%20literature%22%20OR%20tkm_ar_topic:%22language%20and%20literature%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'language and literature',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // World History and History of Europe, Asia, Africa, Australia, New Zealand, etc - subcategory default search
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772056862390_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22world%20history%20and%20history%20of%20europe%20asia%20africa%20australia%20new%20zealand%20etc%22%20OR%20tkm_ar_topic:%22world%20history%20and%20history%20of%20europe%20asia%20africa%20australia%20new%20zealand%20etc%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'world history and history of europe, asia, africa, australia, new zealand, etc.',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Geography. Anthropology. Recreation
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772058833644_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22geography%20anthropology%20recreation%22%20OR%20tkm_ar_topic:%22geography%20anthropology%20recreation%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Geography. Anthropology. Recreation',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Socal Sciences
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772655597710_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22social%20sciences%22%20OR%20tkm_ar_topic:%22social%20sciences%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Social Sciences',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Bibliography. Library Science. Information Resources (General)
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772655716967_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22bibliography%20library%20science%20information%20resources%20(general)%22%20OR%20tkm_ar_topic:%22bibliography%20library%20science%20information%20resources%20(general)%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Bibliography. Library Science. Information Resources (General)',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Political Science
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772655762981_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22political%20science%22%20OR%20tkm_ar_topic:%22political%20science%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Political Science',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // General Works
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772655835032_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22general%20works%22%20OR%20tkm_ar_topic:%22general%20works%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'General Works',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Geography. Anthropology. Recreation
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772655932373_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22geography%20anthropology%20recreation%22%20OR%20tkm_ar_topic:%22geography%20anthropology%20recreation%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Geography. Anthropology. Recreation',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Auxiliary Sciences of History
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772656039513_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22auxiliary%20sciences%20of%20history%22%20OR%20tkm_ar_topic:%22auxiliary%20sciences%20of%20history%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Auxiliary Sciences of History',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // Music and Books on Music
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772656127021_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22music%20and%20books%20on%20music%22%20OR%20tkm_ar_topic:%22music%20and%20books%20on%20music%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'Music and Books on Music',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // History of the Americas
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1772656208754_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_topic:%22history%20of%20the%20americas%22%20OR%20tkm_ar_topic:%22history%20of%20the%20americas%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'category',
        'scope' => 'matches',
        'searchString' => 'History of the Americas',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999130268_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_publisher:%22arabs%22%20OR%20tm_publisher:%22arabs%22%20OR%20tumar_publisher:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999178857_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_publisher:%22arabs%22%20OR%20tm_publisher:%22arabs%22%20OR%20tumar_publisher:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999195814_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_publisher:%22arabs%22%20OR%20tkm_ar_publisher:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'publisher',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999211756_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_publocation:%22arabs%22%20OR%20ts_publocation:%22arabs%22%20OR%20tusar_publocation:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    // testing for phrases with whitespace
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1771972140954_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_publocation:%22jibran%22%20OR%20ts_publocation:%22jibran%22%20OR%20tusar_publocation:%22jibran%22)%20OR%20(tus_publocation:%22arabs%22%20OR%20ts_publocation:%22arabs%22%20OR%20tusar_publocation:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scope' => 'containsAny',
        'searchString' => 'jibran arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999225409_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tus_publocation:%22arabs%22%20OR%20ts_publocation:%22arabs%22%20OR%20tusar_publocation:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999241659_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tks_publocation:%22arabs%22%20OR%20tks_ar_publocation:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'pubplace',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999257738_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_provider_label:%22arabs%22%20OR%20tm_provider_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999294795_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_provider_label:%22arabs%22%20OR%20tm_provider_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999306948_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_provider_label:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'provider',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999320914_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_subject_label:%22arabs%22%20OR%20tm_subject_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scope' => 'containsAny',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999334943_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=((tum_subject_label:%22arabs%22%20OR%20tm_subject_label:%22arabs%22))&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scope' => 'containsAll',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
    [
      "https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765999346845_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&fq=(tkm_subject_label:%22arabs%22)&rows=10&start=0&sort=score%20desc&q=*",
      [
        'fieldSelect' => 'subject',
        'scope' => 'matches',
        'searchString' => 'arabs',
        'sortField' => 'score',
        'sortDir' => 'desc',
        'start' => 0,
        'rows' => 10,
      ]
    ],
  ]);
});
