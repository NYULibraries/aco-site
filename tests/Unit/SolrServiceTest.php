<?php

use App\Services\SolrService;
use Solarium\Client;
use Mockery;

describe('solr search service', function () {

  test('checks the solr service for the method of `search`', function () {
    expect(class_exists(SolrService::class))->toBeTrue();
    expect(method_exists(SolrService::class, 'search'))->toBeTrue();
  });

  test('Transformation logic for solr calls', function () {
    // input
    $rawFormInput = [
      'field' => '1',
      'scope' => 'something',
      'q' => 'arabs'
    ];
    $scop = 'matches';
    $sort = 'score desc';

    // output
    $expectedURL = 'https://discovery1.dlib.nyu.edu/solr/viewer/select?wt=json&json.wrf=callback=YUI.Env.JSONP.yui_3_18_1_3_1765998658109_48&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))';

    // TODO: mock solr client
    $client = new Client();
    $searchService = new SolrService($client);

    $query = $searchService->buildQuery(req, scope, sort);
    $mockSolr->shouldReceive('search')
      ->with($req, $scop, $sort)
      ->andReturn('search')
      ->with($req, $scop, $sort);

    //   ->with('1')
    //   ->andReturn(['title' => 'title']);


    // $service = new SolrService($mockSolr);
    // $url = $service->search('1');

    expect($expectedURL)->toBe($expectedURL);
  })->only();
});

// -----------------
// code provided by alberto, created by gemini
//
<?php

use App\Services\SolrService;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;

beforeEach(function () {
    // 1. Create the Mock for the Solarium Client
    $this->clientMock = mock(Client::class);

    // 2. Inject the mock into the Service constructor
    $this->service = new SolrService($this->clientMock);

    // 3. (Optional) Mock the Query and Result if your search() method uses them
    $this->queryMock = mock(Query::class);
    $this->resultMock = mock(Result::class);
});

test('it returns an array', function () {
    // Prepare: Tell the mock what to expect when search() is called
    // This assumes your search() method calls $this->solrClient->createSelect()
    $this->clientMock
        ->shouldReceive('createSelect')
        ->andReturn($this->queryMock);

    $this->clientMock
        ->shouldReceive('execute')
        ->andReturn($this->resultMock);

    // If your service converts results to an array, mock that behavior:
    $this->resultMock->shouldReceive('getDocuments')->andReturn([]);

    // Execute
    $result = $this->service->search('*:*');

    // Assert
    expect($result)->toBeArray();

});
