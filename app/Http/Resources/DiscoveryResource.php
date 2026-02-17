<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Discovery Resource
 *
 * This resource transforms Solr document data into a bilingual (English/Arabic)
 * format suitable for the frontend. It handles complex field mappings and
 * provides consistent data structure for all document types.
 */
class DiscoveryResource extends JsonResource
{
    /**
     * Partner name translations from English to Arabic.
     */
    private const PARTNER_TRANSLATIONS = [
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

    /**
     * Field name constants for English content.
     */
    private const FIELD_EN_TITLE = 'ss_title_long';
    private const FIELD_EN_AUTHOR = 'sm_author';
    private const FIELD_EN_TOPIC = 'sm_field_topic';
    private const FIELD_EN_PUBLISHER = 'sm_publisher';
    private const FIELD_EN_PUBLOCATION = 'ss_publocation';

    /**
     * Field name constants for Arabic content.
     */
    private const FIELD_AR_TITLE = 'ss_ar_title_long';
    private const FIELD_AR_AUTHOR = 'sm_ar_author';
    private const FIELD_AR_TOPIC = 'sm_ar_topic';
    private const FIELD_AR_PUBLISHER = 'sm_ar_publisher';
    private const FIELD_AR_PUBLOCATION = 'ss_ar_publocation';

    /**
     * Common field name constants.
     */
    private const FIELD_IDENTIFIER = 'ss_book_identifier';
    private const FIELD_NOID = 'ss_noid';
    private const FIELD_HANDLE = 'ss_handle';
    private const FIELD_MANIFEST = 'ss_manifest';
    private const FIELD_PUBDATE = 'ss_pubdate';
    private const FIELD_SUBJECT = 'zm_subject';
    private const FIELD_PARTNER = 'zm_partner';
    private const FIELD_PROVIDER = 'sm_provider_label';
    private const FIELD_PDF_HI = 'zm_pdf_hi';
    private const FIELD_PDF_LO = 'zm_pdf_lo';
    private const FIELD_SEQUENCE_COUNT = 'itm_field_sequence_count';
    /**
     * Default values.
     */
    private const DEFAULT_NOT_AVAILABLE = 'N/A';
    private const DEFAULT_NO_DATE = 'n.d.';

    /**
     * Transform the resource into an array.
     */
    public function toArray(?Request $request = null): array
    {
        return [
            'en' => $this->transformEnglish(),
            'ar' => $this->transformArabic(),
        ];
    }

    /**
     * Transform English data.
     */
    private function transformEnglish(): array
    {
        return [
            'title' => $this->getField(self::FIELD_EN_TITLE, self::DEFAULT_NOT_AVAILABLE),
            'identifier' => $this->getField(self::FIELD_IDENTIFIER, self::DEFAULT_NOT_AVAILABLE),
            'path' => $this->buildBookPath(),
            'noid' => $this->getField(self::FIELD_NOID, self::DEFAULT_NOT_AVAILABLE),
            'handle' => $this->getField(self::FIELD_HANDLE, self::DEFAULT_NOT_AVAILABLE),
            'manifest' => $this->getField(self::FIELD_MANIFEST, self::DEFAULT_NOT_AVAILABLE),
            'subjects' => $this->transformSubjects(),
            'pubdate' => $this->getPubdate(),
            'pdf' => $this->transformPdf(),
            'authors' => $this->transformSimpleItems(self::FIELD_EN_AUTHOR, 'author'),
            'partners' => $this->transformPartners(),
            'topics' => $this->transformSimpleItems(self::FIELD_EN_TOPIC, 'category', ['scope' => 'matches']),
            'publishers' => $this->transformSimpleItems(self::FIELD_EN_PUBLISHER, 'publisher'),
            'provider' => $this->transformSimpleItems(self::FIELD_PROVIDER, 'provider'),
            'publocation' => $this->transformPublocation(self::FIELD_EN_PUBLOCATION),
            'sequence_count' => $this->getField(self::FIELD_SEQUENCE_COUNT, self::DEFAULT_NOT_AVAILABLE),
        ];
    }

    /**
     * Transform Arabic data.
     */
    private function transformArabic(): array
    {
        return [
            'title' => $this->getField(self::FIELD_AR_TITLE, self::DEFAULT_NOT_AVAILABLE),
            'identifier' => $this->getField(self::FIELD_IDENTIFIER, self::DEFAULT_NOT_AVAILABLE),
            'path' => $this->buildBookPath('ar'),
            'noid' => $this->getField(self::FIELD_NOID, self::DEFAULT_NOT_AVAILABLE),
            'handle' => $this->getField(self::FIELD_HANDLE, self::DEFAULT_NOT_AVAILABLE),
            'manifest' => $this->getField(self::FIELD_MANIFEST, self::DEFAULT_NOT_AVAILABLE),
            'subjects' => $this->transformArabicSubjects(),
            'pubdate' => $this->getPubdate(),
            'pdf' => $this->transformPdf(),
            'authors' => $this->transformSimpleItems(self::FIELD_AR_AUTHOR, 'author'),
            'partners' => $this->transformArabicPartners(),
            'topics' => $this->transformSimpleItems(self::FIELD_AR_TOPIC, 'category', ['scope' => 'matches']),
            'publishers' => $this->transformSimpleItems(self::FIELD_AR_PUBLISHER, 'publisher'),
            'provider' => $this->transformSimpleItems(self::FIELD_PROVIDER, 'provider'),
            'publocation' => $this->transformPublocation(self::FIELD_AR_PUBLOCATION),
            'sequence_count' => $this->getField(self::FIELD_SEQUENCE_COUNT, self::DEFAULT_NOT_AVAILABLE),
        ];
    }

    /**
     * Get field value with default fallback.
     */
    private function getField(string $field, mixed $default = null): mixed
    {
        return $this->resource[$field] ?? $default;
    }

    /**
     * Build book path with optional language parameter.
     */
    private function buildBookPath(?string $lang = null): string
    {
        $identifier = $this->getField(self::FIELD_IDENTIFIER, 'unknown');
        $path = "book/{$identifier}/1";

        return $lang ? "{$path}?lang={$lang}" : $path;
    }

    /**
     * Get publication date with fallback.
     */
    private function getPubdate(): string
    {
        return $this->getField(self::FIELD_PUBDATE, self::DEFAULT_NO_DATE);
    }

    /**
     * Build search URL with query parameters.
     */
    private function buildSearchUrl(array $params): string
    {
        return 'search?' . http_build_query($params);
    }

    /**
     * Safely decode JSON string to array.
     *
     * @param string $json JSON string to decode
     * @return array|null Decoded array or null on failure
     */
    private function safeJsonDecode(string $json): ?array
    {
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('JSON decode failed in DiscoveryResource', [
                'error' => json_last_error_msg(),
                'data' => substr($json, 0, 100),
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Decode a collection of JSON strings from a field.
     *
     * @param string $field Field name containing JSON strings
     * @return Collection Collection of decoded arrays
     */
    private function decodeJsonCollection(string $field): Collection
    {
        if (!isset($this->resource[$field])) {
            return collect();
        }

        return collect($this->resource[$field])
            ->map(fn($item) => $this->safeJsonDecode($item))
            ->filter();
    }

    /**
     * Transform subjects from JSON data.
     * Returns arrays with consistent ['label'] and ['path'] structure.
     */
    private function transformSubjects(): array
    {
        return $this->decodeJsonCollection(self::FIELD_SUBJECT)
            ->map(fn($subject) => [
                'label' => $subject['name'] ?? '',
                'path' => $this->buildSearchUrl(['subject' => $subject['name'] ?? '']),
            ])
            ->values()
            ->all();
    }

    /**
     * Transform Arabic subjects (currently empty as per business logic).
     */
    private function transformArabicSubjects(): array
    {
        // Arabic subjects are intentionally excluded per requirements
        return [];
    }

    /**
     * Transform PDF data.
     * Returns arrays for consistent structure.
     */
    private function transformPdf(): array
    {
        return [
            'hi' => $this->extractPdfData(self::FIELD_PDF_HI),
            'lo' => $this->extractPdfData(self::FIELD_PDF_LO),
        ];
    }

    /**
     * Extract PDF data from Solr field.
     * Returns array for consistent structure.
     */
    private function extractPdfData(string $field): ?array
    {
        if (!isset($this->resource[$field][0])) {
            return null;
        }

        return $this->safeJsonDecode($this->resource[$field][0]);
    }

    /**
     * Transform simple items (authors, topics, publishers, providers) with consistent structure.
     * Returns arrays with ['label'] and ['path'] keys.
     *
     * @param string $field The field name in the resource
     * @param string $queryParam The query parameter name for the search URL
     * @param array $additionalParams Additional query parameters to include
     * @return array
     */
    private function transformSimpleItems(
        string $field,
        string $queryParam,
        array $additionalParams = []
    ): array {
        if (!isset($this->resource[$field])) {
            return [];
        }

        return collect($this->resource[$field])
            ->map(fn($item) => [
                'label' => $item,
                'path' => $this->buildSearchUrl(
                    array_merge([$queryParam => $item], $additionalParams)
                ),
            ])
            ->values()
            ->all();
    }

    /**
     * Transform partners with English and Arabic translations.
     * Returns arrays with consistent ['label'] and ['path'] structure.
     */
    private function transformPartners(): array
    {
        return $this->decodeJsonCollection(self::FIELD_PARTNER)
            ->map(fn($partner) => [
                'label' => $partner['name'] ?? '',
                'path' => $this->buildSearchUrl(['partner' => $partner['name'] ?? '']),
            ])
            ->values()
            ->all();
    }

    /**
     * Transform Arabic partners using translation map.
     * Returns arrays with ['label'] and ['path'] keys.
     */
    private function transformArabicPartners(): array
    {
        return $this->decodeJsonCollection(self::FIELD_PARTNER)
            ->filter(fn($partner) => isset(self::PARTNER_TRANSLATIONS[$partner['name'] ?? '']))
            ->map(fn($partner) => [
                'label' => self::PARTNER_TRANSLATIONS[$partner['name']],
                'path' => $this->buildSearchUrl(['partner' => $partner['name']]),
            ])
            ->values()
            ->all();
    }

    /**
     * Transform publication location.
     * Returns arrays with ['label'] and ['path'] keys.
     */
    private function transformPublocation(string $field): array
    {
        if (!isset($this->resource[$field])) {
            return [];
        }

        $location = $this->resource[$field];

        return [[
            'label' => $location,
            'path' => $this->buildSearchUrl(['publocation' => $location]),
        ]];
    }
}
