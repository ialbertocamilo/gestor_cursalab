<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService {

    /**
     * Format file size showing its unit
     * @param int|null $sizeInKB $
     */
    public static function formatSize(?int $sizeInKB): string
    {
        if (!$sizeInKB) return '-';

        $size = '';
        if ($sizeInKB < 1024) {

            $size = "$sizeInKB KB";

        } else {

            $sizeInMB = round($sizeInKB / 1024, 2);

            if ($sizeInMB < 1024) {

                $size = "$sizeInMB MB";

            } else {

                $sizeInGB = round($sizeInKB / (1024 * 1024), 2);
                $size = "$sizeInGB GB";
            }
        }

        return $size;
    }

    /**
     * Generate bucket URL for file
     *
     * @param string|null $path
     * @return string
     */
    public static function generateUrl($path = ''): string
    {
        // Initiliaze path value if it is not set
        if (!$path) $path = '';

        if (str_starts_with($path, 'https://')) return $path;

        $full_url = Storage::disk('cdn')->url($path);
        // $base = config('app.storage_base_url');

        // $full_url = $base . '/' . $path;
        // $full_path = Storage::path($path);

        $full_url = str_replace("%5C", '/', $full_url);

        return $full_url;

        // $base = rtrim($base, '/');
        // $base = rtrim($base, '\\');

        // $path = trim($path, '/');
        // $path = trim($path, '\\');

        // return "$base/$path";
    }
}
