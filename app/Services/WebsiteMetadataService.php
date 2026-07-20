<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class WebsiteMetadataService
{
    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function extract(string $url): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; BeraniDigitalBot/1.0)',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new Exception("Gagal mengambil website. HTTP status: {$response->status()}");
        }

        $html = $response->body();

        return [
            'url'          => $url,
            'title'        => $this->extractTitle($html),
            'description'  => $this->extractMeta($html, 'description'),
            'canonical'    => $this->extractCanonical($html, $url),
            'favicon'      => $this->extractFavicon($html, $url),
            'emails'       => $this->extractEmails($html),
            'phones'       => $this->extractPhones($html),
            'social_media' => $this->extractSocialMedia($html),
            'open_graph'   => [
                'title'       => $this->extractMeta($html, 'og:title', true),
                'description' => $this->extractMeta($html, 'og:description', true),
                'image'       => $this->extractMeta($html, 'og:image', true),
            ],
        ];
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            return trim(html_entity_decode($m[1]));
        }
        return null;
    }

    private function extractMeta(string $html, string $name, bool $isProperty = false): ?string
    {
        $attr = $isProperty ? 'property' : 'name';
        $pattern = '/<meta[^>]*' . $attr . '=["\']' . preg_quote($name, '/') . '["\'][^>]*content=["\'](.*?)["\'][^>]*>/is';
        if (preg_match($pattern, $html, $m)) {
            return trim(html_entity_decode($m[1]));
        }

        $pattern2 = '/<meta[^>]*content=["\'](.*?)["\'][^>]*' . $attr . '=["\']' . preg_quote($name, '/') . '["\'][^>]*>/is';
        if (preg_match($pattern2, $html, $m)) {
            return trim(html_entity_decode($m[1]));
        }

        return null;
    }


    private function extractCanonical(string $html, string $baseUrl): ?string
    {
        if (preg_match('/<link[^>]*rel=["\']canonical["\'][^>]*href=["\'](.*?)["\'][^>]*>/is', $html, $m)) {
            return $this->resolveUrl($m[1], $baseUrl);
        }
        return null;
    }

    private function extractFavicon(string $html, string $baseUrl): ?string
    {
        if (preg_match('/<link[^>]*rel=["\'](?:shortcut icon|icon)["\'][^>]*href=["\'](.*?)["\'][^>]*>/is', $html, $m)) {
            return $this->resolveUrl($m[1], $baseUrl);
        }
        $parsed = parse_url($baseUrl);
        return ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . '/favicon.ico';
    }

    private function extractEmails(string $html): array
    {
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', strip_tags($html), $m);
        return array_values(array_unique($m[0] ?? []));
    }

    private function extractPhones(string $html): array
    {
        preg_match_all('/(?:\+62|62|0)8[1-9][0-9]{6,10}/', strip_tags($html), $m);
        return array_values(array_unique($m[0] ?? []));
    }

    private function extractSocialMedia(string $html): array
    {
        $platforms = ['facebook.com', 'instagram.com', 'twitter.com', 'x.com', 'linkedin.com', 'youtube.com', 'tiktok.com'];
        preg_match_all('/href=["\'](https?:\/\/(?:www\.)?(?:' . implode('|', array_map(fn($p) => preg_quote($p, '/'), $platforms)) . ')[^"\']*)["\']/i', $html, $m);
        return array_values(array_unique($m[1] ?? []));
    }

    private function resolveUrl(string $link, string $baseUrl): string
    {
        if (Str::startsWith($link, ['http://', 'https://'])) {
            return $link;
        }
        $parsed = parse_url($baseUrl);
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
        return Str::startsWith($link, '/') ? $base . $link : $base . '/' . $link;
    }
}
