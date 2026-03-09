<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Service for reading and writing item counts without a database.
 *
 * Data is stored as JSON on disk and validated defensively.
 * Designed to be safe against malformed JSON, race conditions,
 * and partial writes.
 */
class ItemCountService
{
    /**
     * Default fallback values when data is missing or invalid.
     */
    private const DEFAULT_COUNTS = [
        'volumes'  => 0,
        'subjects' => 0,
    ];

    /**
     * Absolute path to the JSON storage file.
     */
    private static function getPath(): string
    {
        return base_path('database/data/itemcount.json');
    }

    /**
     * Read item counts from disk.
     *
     * @return array{volumes:int, subjects:int}
     */
    public static function getItemCounts(): array
    {
        $path = self::getPath();

        if (!File::exists($path)) {
            return self::DEFAULT_COUNTS;
        }

        try {
            $content = File::get($path);
            $data = self::decodeJson($content, $path);

            if ($data === null || !self::isValid($data)) {
                return self::DEFAULT_COUNTS;
            }

            // Explicit casting for safety
            return [
                'volumes'  => (int) $data['volumes'],
                'subjects' => (int) $data['subjects'],
            ];
        } catch (Throwable $e) {
            Log::error('Failed to read item counts', [
                'path'      => $path,
                'exception' => $e,
            ]);

            return self::DEFAULT_COUNTS;
        }
    }

    /**
     * Persist item counts to disk.
     *
     * Uses atomic write (temp file + rename) and file locking
     * to prevent corruption during concurrent writes.
     */
    public static function saveItemCounts(array $counts): bool
    {
        if (!self::isValid($counts)) {
            Log::error('Attempted to save invalid item counts', [
                'counts' => $counts,
            ]);

            return false;
        }

        $path = self::getPath();
        $directory = dirname($path);

        try {
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $payload = json_encode(
                [
                    'volumes'  => (int) $counts['volumes'],
                    'subjects' => (int) $counts['subjects'],
                ],
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );

            // Atomic write: write to temp file, then rename
            $tmpPath = $path . '.tmp';

            File::put($tmpPath, $payload, true); // LOCK_EX
            File::move($tmpPath, $path);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to save item counts', [
                'path'      => $path,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Decode JSON safely and log errors with context.
     */
    private static function decodeJson(string $content, string $path): ?array
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::error('Invalid JSON in item counts file', [
                'path'  => $path,
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        return $data;
    }

    /**
     * Validate the expected schema for item counts.
     */
    private static function isValid(array $data): bool
    {
        $validator = Validator::make($data, [
            'volumes'  => 'required|integer|min:0',
            'subjects' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Item count validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return false;
        }

        return true;
    }
}
