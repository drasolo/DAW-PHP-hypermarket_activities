<?php

final class ExternalFetch
{
    public static function get(string $url, string $cacheKey, int $ttlSeconds = 3600): array
    {
        $cacheDir = __DIR__ . '/../../storage/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        $cacheFile = $cacheDir . '/' . preg_replace('/[^a-z0-9_\-]/i', '_', $cacheKey) . '.html';

        // 1) Serve cache if fresh
        if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttlSeconds)) {
            return [
                'ok' => true,
                'status' => 200,
                'html' => file_get_contents($cacheFile),
                'from_cache' => true,
                'error' => null,
            ];
        }

        // 2) Fetch live
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9,ro;q=0.8',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
            ],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
        ]);

        $html = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($html === false || $code >= 400 || $code === 0) {
            // 3) Fallback: old cache if exists
            if (is_file($cacheFile)) {
                return [
                    'ok' => true,
                    'status' => 200,
                    'html' => file_get_contents($cacheFile),
                    'from_cache' => true,
                    'error' => "Live fetch failed (HTTP {$code}): {$err}. Served cached copy.",
                ];
            }

            return [
                'ok' => false,
                'status' => $code,
                'html' => null,
                'from_cache' => false,
                'error' => "Fetch failed (HTTP {$code}): {$err}",
            ];
        }

        file_put_contents($cacheFile, $html);

        return [
            'ok' => true,
            'status' => $code,
            'html' => $html,
            'from_cache' => false,
            'error' => null,
        ];
    }
}
