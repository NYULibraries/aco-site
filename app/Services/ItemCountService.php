<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ItemCountService
{
    private const DEFAULT_COUNTS = ['volumes' => 0, 'subjects' => 0];

    private static function getPath(): string
    {
        return base_path('database/data/itemcount.json');
    }

    public static function getItemCounts(): array
    {
        $path = self::getPath();

        if (!File::exists($path)) {
            return self::DEFAULT_COUNTS;
        }

        try {
            $data = json_decode(File::get($path), true);

            $validator = Validator::make($data ?? [], [
                'volumes' => 'required|integer',
                'subjects' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return self::DEFAULT_COUNTS;
            }

            return $data;
        } catch (Throwable $e) {
            Log::error("Failed to read item counts: " . $e->getMessage());
            return self::DEFAULT_COUNTS;
        }
    }

    public static function saveItemCounts(array $counts): bool
    {
        try {
            $path = self::getPath();
            $directory = dirname($path);

            // Ensure directory exists
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Write prettified JSON for readability
            File::put($path, json_encode($counts, JSON_PRETTY_PRINT));

            return true;
        } catch (Throwable $e) {
            Log::error("Failed to save item counts: " . $e->getMessage());
            return false;
        }
    }
}
