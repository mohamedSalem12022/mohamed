<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class WebSearchService
{
    /**
     * Perform a live web search.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 4): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        // 1. Google Programmable Search if API key & CX are provided
        $googleKey = config('services.google_search.key') ?: env('GOOGLE_SEARCH_API_KEY');
        $googleCx = config('services.google_search.cx') ?: env('GOOGLE_SEARCH_CX');

        if (!empty($googleKey) && !empty($googleCx)) {
            $googleResults = $this->searchGoogle($query, $googleKey, $googleCx, $limit);
            if (!empty($googleResults)) {
                return $googleResults;
            }
        }

        // 2. Open Live Web Search (DuckDuckGo Lite)
        $ddgResults = $this->searchDuckDuckGo($query, $limit);
        if (!empty($ddgResults)) {
            return $ddgResults;
        }

        // 3. Fallback to Arabic Wikipedia Search API
        return $this->searchWikipedia($query, $limit);
    }

    /**
     * Search via Google Programmable Search API
     */
    protected function searchGoogle(string $query, string $key, string $cx, int $limit): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->get('https://www.googleapis.com/customsearch/v1', [
                    'key' => $key,
                    'cx' => $cx,
                    'q' => $query,
                    'num' => min($limit, 10),
                    'hl' => 'ar',
                ]);

            if ($response->successful()) {
                $items = $response->json('items') ?? [];
                $results = [];
                foreach ($items as $item) {
                    $url = $item['link'] ?? '';
                    $title = $item['title'] ?? '';
                    $snippet = $item['snippet'] ?? '';

                    if (!empty($url) && !empty($title)) {
                        $host = parse_url($url, PHP_URL_HOST) ?? 'Google Search';
                        $results[] = [
                            'title' => $title,
                            'snippet' => $snippet,
                            'url' => $url,
                            'source' => str_ireplace('www.', '', $host),
                            'domain' => $host,
                            'verified' => true,
                            'provider' => 'Google Search API',
                        ];
                    }
                    if (count($results) >= $limit) break;
                }
                return $results;
            }
        } catch (\Throwable $e) {
            Log::warning('Google Search API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Search via DuckDuckGo Lite (No API key needed, live web results)
     */
    protected function searchDuckDuckGo(string $query, int $limit): array
    {
        try {
            $response = Http::withoutVerifying()
                ->asForm()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept-Language' => 'ar,en;q=0.9',
                ])
                ->timeout(8)
                ->post('https://lite.duckduckgo.com/lite/', [
                    'q' => $query,
                ]);

            if ($response->successful()) {
                $html = $response->body();
                $dom = new DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
                $xpath = new DOMXPath($dom);

                $links = $xpath->query('//a[@class="result-link"]');
                $snippets = $xpath->query('//td[@class="result-snippet"]');

                $results = [];
                for ($i = 0; $i < $links->length; $i++) {
                    $a = $links->item($i);
                    $title = trim($a->textContent);
                    $rawUrl = $a->getAttribute('href');

                    // Extract actual URL if redirected through DDG
                    $url = $rawUrl;
                    if (str_contains($rawUrl, 'uddg=')) {
                        $parts = parse_url($rawUrl);
                        if (isset($parts['query'])) {
                            parse_str($parts['query'], $queryParams);
                            if (!empty($queryParams['uddg'])) {
                                $url = urldecode($queryParams['uddg']);
                            }
                        }
                    }

                    $snippet = '';
                    if ($snippets->length > $i) {
                        $snippet = trim($snippets->item($i)->textContent);
                    }

                    if (!empty($title) && !empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                        $host = parse_url($url, PHP_URL_HOST) ?? 'الويب';
                        $results[] = [
                            'title' => $title,
                            'snippet' => $snippet,
                            'url' => $url,
                            'source' => str_ireplace('www.', '', $host),
                            'domain' => $host,
                            'verified' => false,
                            'provider' => 'DuckDuckGo Web',
                        ];
                    }

                    if (count($results) >= $limit) break;
                }

                return $results;
            }
        } catch (\Throwable $e) {
            Log::warning('DuckDuckGo search error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Fallback to Wikipedia API
     */
    protected function searchWikipedia(string $query, int $limit): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['User-Agent' => 'MOHabitTracker/1.0 (Educational AI; Windows)'])
                ->timeout(6)
                ->get('https://ar.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $query,
                    'format' => 'json',
                    'utf8' => 1,
                ]);

            if ($response->successful()) {
                $items = $response->json('query.search') ?? [];
                $results = [];
                foreach ($items as $item) {
                    $title = $item['title'] ?? '';
                    $snippet = strip_tags($item['snippet'] ?? '');
                    $cleanTitle = str_replace(' ', '_', $title);
                    $url = 'https://ar.wikipedia.org/wiki/' . urlencode($cleanTitle);

                    $results[] = [
                        'title' => $title,
                        'snippet' => $snippet,
                        'url' => $url,
                        'source' => 'ويكيبيديا العربية',
                        'domain' => 'ar.wikipedia.org',
                        'verified' => true,
                        'provider' => 'Wikipedia API',
                    ];
                    if (count($results) >= $limit) break;
                }
                return $results;
            }
        } catch (\Throwable $e) {
            Log::warning('Wikipedia search error: ' . $e->getMessage());
        }

        return [];
    }
}
