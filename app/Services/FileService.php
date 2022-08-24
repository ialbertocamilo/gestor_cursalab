<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService {

    /**
     * Generate bucket URL for file
     *
     * @param string|null $path
     * @return string
     */
    public static function generateUrl(string $path = ''): string
    {
        if (str_starts_with($path, 'https://')) return $path;

        // Initiliaze path value if it is not set

        // if (!$path) $path = '';

        $full_url = Storage::url($path);
        // $full_path = Storage::path($path);

        $full_url = str_replace("%5C", '/', $full_url);

        return $full_url;

        // $base = env('BUCKET_BASE_URL', '');
        // $base = rtrim($base, '/');
        // $base = rtrim($base, '\\');

        // $path = trim($path, '/');
        // $path = trim($path, '\\');

        // return "$base/$path";
    }
}
