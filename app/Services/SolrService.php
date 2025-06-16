<?php

namespace App\Services;

use Solarium\Client;
use Illuminate\Http\Request;

class SolrService
{
    public function __construct(private Client $solrClient) {}

    public function search(Request $request, string $scopeIs = 'matches', string $sortBy = 'score desc'): array
    {

      // title, matches, jibran
      // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&
      // fq=(tks_title_long:"jibran" OR tks_ar_title_long:"jibran")&rows=10&start=0&sort=score desc&q=*

      // title, contains all, jibran
      // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
      // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*

      // title, contains aany, jibran
      // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
      // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*


      // select?
      // wt=json
      // fl=*
      // fq=bundle:dlts_book
      // fq=sm_collection_code:aco
      // fq=ss_language:en
      // rows=10
      // start=0
      // sort=score%20desc
      // q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))

      $options = $request->all();

      $fq = [];

      $fields = [
        'title' => [
          'match' => [
            'tks_title_long',
            'tks_ar_title_long',
          ],
          'contains' => [
            'tus_title_long',
            'ts_title_long',
            'tusar_title_long',
          ],
        ],
        'author' => [
          'match' => [
            'tkm_author',
            'tkm_ar_author',
          ],
          'contains' => [
            'tum_author',
            'tm_author',
            'tumar_author',
          ]
        ],
        'pubplace'  => [
          'match' => [
            'tks_publocation',
            'tks_ar_publocation',
          ],
          'contains' => [
            'tus_publocation',
            'ts_publocation',
            'tusar_publocation',
          ],
        ],
        'publisher' => [
          'match' => [
            'tkm_publisher',
            'tkm_ar_publisher',
          ],
          'contains' => [
            'tum_publisher',
            'tm_publisher',
            'tumar_publisher',
          ],
        ],
        'category' => [
          'match' => [
            'tkm_topic',
            'tkm_ar_topic',
          ],
          'contains' => [
            'tum_topic',
            'tm_topic',
            'tumar_topic',
          ],
        ],
        'provider' => [
          'match' => [
            'tkm_provider_label',
          ],
          'contains' => [
            'tum_provider_label',
            'tm_provider_label',
          ],
        ],
        'subject' => [
          'match' => [
            'tkm_subject_label',
          ],
          'contains' => [
            'tum_subject_label',
            'tm_subject_label',
          ],
        ],
      ];

      foreach ($fields as $key => $map) {

        if (empty($options[$key])) continue;

        $value = trim($options[$key]);

        if ($scopeIs === 'matches') {

          $parts = array_map(fn($f) => "$f:\"$value\"", $map['match']);

          $fq[] = '(' . implode(' OR ', $parts) . ')';

        }
        else {

          $words = preg_split('/\s+/', $value);

          $clauses = [];

          foreach ($words as $w) {

            $sub = array_map(fn($f) => "$f:\"$w\"", $map['contains']);

            $clauses[] = '(' . implode(' OR ', $sub) . ')';

          }

          $fq[] = '(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $clauses) . ')';

        }

      }



      $query = $this->solrClient->createSelect();

      $query->setQuery('*:*');

      foreach ($fq as $filter) {
        $query->createFilterQuery(md5($filter))->setQuery($filter);
      }

      if (!empty($options['q'])) {
            $q = trim($options['q']);
            if ($scopeIs === 'matches') {
                $query->setQuery("(content_und:\"$q\" OR content_und_ws:\"$q\" OR content_en:\"$q\" OR content:\"$q\")");
            } else {
                $words = preg_split('/\s+/', $q);
                $parts = [];
                foreach ($words as $w) {
                    $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
                }
                $query->setQuery('(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $parts) . ')');
            }
        }

        $start = $options['start'] ?? 0;

        $rows  = $options['rpp'] ?? 10;

        $query->setStart($start);

        $query->setRows($rows);

       $request = $this->solrClient->createRequest($query);

       $uri = $request->getUri();

        echo $uri;

        die();

        $resultset = $this->solrClient->select($query);

        $total = $resultset->getNumFound();

        $docs = iterator_to_array($resultset);

        $documents = [];

    foreach ($docs as $doc) {

      $publocation = [];

      if (isset($doc->ss_publocation)) {
        $publocation[] = [
          'label' => $doc->ss_publocation,
          'path' => "search/?provider={$doc->ss_publocation}",
        ];
      }

      $ar_publocation = [];
      if (isset($doc->ss_ar_publocation)) {
        $ar_publocation[] = [
          'label' => $doc->ss_ar_publocation,
          'path' => "search/?provider={$doc->ss_ar_publocation}",
        ];
      }

      $providers = [];

      if (isset($doc->sm_provider_label)) {
        foreach ($doc->sm_provider_label as $provider) {
          $providers[] = [
            'label' => $provider,
            'path' => "search/?provider={$provider}",
          ];
        }
      }

      $publishers = [];

      if (isset($doc->sm_publisher)) {
        foreach ($doc->sm_publisher as $publisher) {
          $publishers[] = [
            'label' => $publisher,
            'path' => "search/?publisher={$publisher}",
          ];
        }
      }

      $topics = [];

      if (isset($doc->sm_field_topic)) {
        foreach ($doc->sm_field_topic as $topic) {
          $topics[] = [
            'label' => $topic,
            'path' => "search?category={$topic}&scope=matches",
          ];
        }
      }

      $subjects = [];

      if (isset($doc->zm_subject)) {
        foreach ($doc->zm_subject as $subject) {
          $subject = json_decode($subject);
          $subject->path = "search?subject={$subject->name}";
          $subjects[] = $subject;
        }
      }

      $partners_map = [
        'Arabic collections online' => 'المجموعات العربية على الانترنت',
        'New York University Libraries' => 'مكتبات جامعة نيويورك',
        'Princeton University Libraries' => 'مكتبات جامعة برينستون',
        'Cornell University Libraries' => 'مكتبات جامعة كورنيل',
        'Columbia University Libraries' => 'مكتبات جامعة كولومبيا',
        'American University of Beirut' => 'الجامعة الاميركية في بيروت',
        'American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
        'The American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
        'United Arab Emirates National Archives' => 'الامارات العربية المتحدة - الارشيف الوطني',
      ];

      $partners = [];

      $partners_ar = [];

      if (isset($doc->zm_partner)) {
        foreach ($doc->zm_partner as $partner) {
          $partner = json_decode($partner);
          $partner->path = "search?partner={$partner->name}";
          $partners[] = $partner;
          if (isset($partners_map[$partner->name])) {
            $partners_ar[] = [
              'label' => $partners_map[$partner->name],
              'path' => "search?partner={$partner->name}",
            ];
          }
        }
      }

      $authors = [];

      if (isset($doc->sm_author)) {
        foreach ($doc->sm_author as $author) {
          $authors[] = [
            'label' => $author,
            'path' => "search?subject={$author}",
          ];
        }
      }

      $authors_ar = [];

      if (isset($doc->sm_ar_author)) {
        foreach ($doc->sm_ar_author as $author) {
          $authors_ar[] = [
            'label' => $author,
            'path' => "search?subject={$author}",
          ];
        }
      }

      $pdf_hi = [];

      if (isset($doc->zm_pdf_hi) && isset($doc->zm_pdf_hi[0])) {
        $pdf_hi = json_decode($doc->zm_pdf_hi[0]);
      }

      $pdf_lo = [];

      if (isset($doc->zm_pdf_lo) && isset($doc->zm_pdf_lo[0])) {
        $pdf_lo = json_decode($doc->zm_pdf_lo[0]);
      }

      $pubdate = 'n.d.';
      if (isset($doc->ss_pubdate) && isset($doc->ss_pubdate)) {
        $pubdate = $doc->ss_pubdate;
      }

      $documents[] = [
        'en' => [
          'title' => $doc->ss_title_long,
          'identifier' => $doc->ss_book_identifier,
          'path' => "book/{$doc->ss_book_identifier}/1",
          'noid' => $doc->ss_noid,
          'handle' => $doc->ss_handle,
          'manifest' => $doc->ss_manifest,
          'subjects' => $subjects,
          'pubdate' => $pubdate,
          'pdf' => [
            'hi' => $pdf_hi,
            'lo' => $pdf_lo,
          ],
          'authors' => $authors,
          'partners' => $partners,
          'topics' => $topics,
          'publishers' => $publishers,
          'provider' => $providers,
          'publocation' => $publocation,
        ],
        'ar' => [
          'title' => $doc->ss_ar_title_long,  // ok
          'identifier' => $doc->ss_book_identifier,  // ok
          'path' => "book/{$doc->ss_book_identifier}/1?lang=ar",  // ok
          'noid' => $doc->ss_noid,  // ok
          'handle' => $doc->ss_handle,  // ok
          'manifest' => $doc->ss_manifest,  // ok
          'subjects' => [], // we do not display subjects in ar?
          'pubdate' => $pubdate,  // ok
          'pdf' => [
            'hi' => $pdf_hi,
            'lo' => $pdf_lo,
          ],
          'authors' => $authors_ar,  // ok
          'partners' => $partners_ar,
          'topics' => $topics,
          'publishers' => $publishers,
          'provider' => $providers,
          'publocation' => $ar_publocation,
        ],
      ];

    }

        return [
            'documents' => $documents,
            'total' => $total,
            'rows' => $rows,
            'page' => ($start / $rows) + 1,
        ];
    }
}
