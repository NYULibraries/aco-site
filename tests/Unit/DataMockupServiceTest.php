<?php

use App\Services\DataMockupService;

beforeEach(function () {
    // __DIR__ is tests/Unit. We go up twice to reach the project root.
    $this->mockupPath = dirname(__DIR__, 2) . '/database/data/DataMockupService.json';

    $this->service = new DataMockupService($this->mockupPath);
});

test('it returns the correct data structure and pagination keys', function () {
    $result = $this->service->search();

    expect($result)->toHaveKeys([
        'documents', 'total', 'rows', 'start', 'page', 'last_page', 'has_more', 'has_prev'
    ]);
});

test('it calculates the correct page number based on start and rows', function () {
    // Page 2: Start at 10, with 10 rows per page
    $result = $this->service->search(start: 10, rows: 10);

    // Use toBe(2) (integer) instead of toBe(2.0) (float)
    expect($result['page'])->toBe(2);
});

test('it handles pagination flags correctly', function () {
    $firstPage = $this->service->search(start: 0, rows: 5);
    expect($firstPage['has_prev'])->toBeFalse();
    expect($firstPage['has_more'])->toBeTrue();

    $middlePage = $this->service->search(start: 5, rows: 5);
    expect($middlePage['has_prev'])->toBeTrue();
    expect($middlePage['has_more'])->toBeTrue();
    expect($middlePage['page'])->toBe(2);
});

test('it returns the total count of all documents in the JSON file', function () {
    $result = $this->service->search();
    expect($result['total'])->toBeGreaterThan(0);
});
