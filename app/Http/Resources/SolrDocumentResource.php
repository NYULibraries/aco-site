<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Laravel resourced used to shape the data of each document
 */
class SolrDocumentResource extends JsonResource
{
  protected const PARTNERS_MAP = [
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

  public function toArray($req): array
  {
    $doc = $this->resource;

    $publocation = isset($doc->ss_publocation) ? [[
      'label' => $doc->ss_publocation,
      'path' => "search/?provider={$doc->ss_publocation}",
    ]] : [];

    $ar_publocation = isset($doc->ss_ar_publocation) ? [[
      'label' => $doc->ss_ar_publocation,
      'path' => "search/?provider={$doc->ss_ar_publocation}",
    ]] : [];


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

    $partners = [];
    $partners_ar = [];
    if (isset($doc->zm_partner)) {
      foreach ($doc->zm_partner as $partner) {
        $partner = json_decode($partner);
        $partner->path = "search?partner={$partner->name}";
        $partners[] = $partner;
        if (isset($partners_map[$partner->name])) {
          $partners_ar[] = [
            'label' => self::PARTNERS_MAP[$partner->name],
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

    $pdf_hi = isset($doc->zm_pdf_hi) && isset($doc->zm_pdf_hi[0]) ?
      json_decode($doc->zm_pdf_hi[0]) : [];

    $pdf_lo = isset($doc->zm_pdf_lo) && isset($doc->zm_pdf_lo[0]) ?
      json_decode($doc->zm_pdf_lo[0]) : [];

    $pubdate = isset($doc->ss_pubdate) && isset($doc->ss_pubdate) ?
      $doc->ss_pubdate : 'n.d.';

    return [
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
}
