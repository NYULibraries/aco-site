<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ItemCountService
{
    // Default volume, subject counts
    private const DEFAULT_COUNTS = ['volumes' => '0', 'subjects' => '0'];

    public static function getItemCounts(): array
    {
        $path = base_path('database/data/itemcount.json');

        // Check if file exists first
        if (! File::exists($path)) {
            Log::warning("Datasource file is missing: {$path}");
            return self::DEFAULT_COUNTS;
        }

        // Try to read the file
        try {
            $content = File::get($path);
            $data = json_decode($content, true);

            // Check if JSON is valid and an arr (json_decode should return arr)
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                Log::error("Invalid JSON in item counts", [
                    'path' => $path,
                    'error' => json_last_error_msg(),
                ]);
                return self::DEFAULT_COUNTS;
            }

            // Validate the data using Validator
            $validator = Validator::make($data, [
                'volumes' => 'required|string',
                'subjects' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::error("Item count validation failed", [
                    'path' => $path,
                    'errors' => $validator->errors()->toArray(),
                ]);
                return self::DEFAULT_COUNTS;
            }

            return $data;

        } catch (Throwable $e) {
            Log::error("Failed to read item counts", [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return self::DEFAULT_COUNTS;
        }
    }
}
