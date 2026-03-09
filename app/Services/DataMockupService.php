<?php

namespace App\Services;

class DataMockupService
{
    public function __construct(
        protected string $filePath
    ) {}

    public function search(int $start = 0, int $rows = 10): array
    {
        $path = $this->filePath;

        // Ensure fallback also contains the new keys so tests don't break
        if (!file_exists($path)) {
            return [
                'documents' => [],
                'total' => 0,
                'rows' => $rows,
                'start' => $start,
                'page' => 1,
                'last_page' => 1,
                'has_prev' => false,
                'has_more' => false,
            ];
        }

        $json = file_get_contents($path);

        $data = json_decode($json, true);

        $allDocuments = $data['documents'] ?? [];

        $totalCount = count($allDocuments);

        $pagedDocuments = array_slice($allDocuments, $start, $rows);

        return [
            'documents' => $pagedDocuments,
            'total'     => $totalCount,
            'rows'      => $rows,
            'start'     => $start,
            'page'      => ($rows > 0) ? (int) (floor($start / $rows) + 1) : 1,
            'last_page' => ($rows > 0) ? (int) ceil($totalCount / $rows) : 1,
            'has_prev'  => $start > 0,
            'has_more'  => ($start + $rows) < $totalCount,
        ];
    }
}
