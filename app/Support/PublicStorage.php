<?php

namespace App\Support;

use Illuminate\Http\Request;

class PublicStorage
{
    /**
     * URL for a file on the public disk.
     *
     * Uses the current request’s scheme, host, and base path when handling an HTTP request
     * (works on any port, e.g. :8001, and behind subdirectory installs). Falls back to
     * asset() when there is no request (queues, CLI) so APP_URL still applies there.
     */
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $relative = 'storage/'.$path;

        $request = request();
        if ($request instanceof Request && $request->getSchemeAndHttpHost() !== '') {
            $root = rtrim($request->getSchemeAndHttpHost().$request->getBasePath(), '/');

            return $root.'/'.$relative;
        }

        return asset($relative);
    }
}
