<?php

namespace App\Services;


class FileService {

    /**
     * Generate bucket URL for file
     *
     * @param string|null $path
     * @return string
     */
    public static function generateUrl(?string $path): string
    {

        // Initiliaze path value if it is not set

        if (!$path) $path = '';

        $base = env('BUCKET_BASE_URL', '');
        $base = rtrim($base, '/');
        $base = rtrim($base, '\\');

        $path = trim($path, '/');
        $path = trim($path, '\\');

        return "$base/$path";
    }
}
