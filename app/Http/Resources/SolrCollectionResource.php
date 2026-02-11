<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Laravel resource used to shape the final data sent to the blade template
 */
class SolrCollectionResource extends ResourceCollection
{
  protected $solariumResult;

  public function __construct($resource, $solariumResult)
  {
    parent::__construct($resource);
    $this->solariumResult = $solariumResult;
  }
  // overwritting the toArray method so it returns what we want
  public function toArray($req): array
  {
    $rows = $this->solariumResult->getQuery()->getOption('rows');
    $start = $this->solariumResult->getQuery()->getOption('start');

    return [
      'documents' => $this->collection,
      'total' => $this->solariumResult->getNumFound(),
      'rows' => $rows,
      'page' => ($start / $rows) + 1,
    ];
  }
}
