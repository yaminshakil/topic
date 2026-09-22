<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/** Validates YouTube video links and looks up their titles via oEmbed (no API key). */
class YouTube
{
    /** Extract the 11-char video id from any common YouTube video URL, or null. */
    public static function videoId(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        if (! preg_match('#^https?://#i', $input)) {
            $input = 'https://' . $input;
        }

        $parts = parse_url($input);
        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^(www|m|music)\./i', '', $parts['host']));
        $path = trim($parts['path'] ?? '', '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $id = null;

        if ($host === 'youtu.be') {
            $id = $segments[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            if (($segments[0] ?? '') === 'watch') {
                parse_str($parts['query'] ?? '', $q);
                $id = is_string($q['v'] ?? null) ? $q['v'] : null;
            } elseif (in_array($segments[0] ?? '', ['shorts', 'embed', 'live', 'v'], true)) {
                $id = $segments[1] ?? null;
            }
        }

        return ($id !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) ? $id : null;
    }

    /**
     * @return array{ok: bool, url?: string, title?: ?string, channel?: ?string, publishedAt?: ?string, error?: string}
     */
    public function lookup(string $input): array
    {
        $id = self::videoId($input);
        if (! $id) {
            return ['ok' => false, 'error' => 'Please enter a valid YouTube video link (youtube.com/watch?v=… or youtu.be/…).'];
        }

        $url = 'https://www.youtube.com/watch?v=' . $id;

        try {
            $res = Http::timeout(6)->acceptJson()
                ->get('https://www.youtube.com/oembed', ['url' => $url, 'format' => 'json']);
        } catch (\Throwable) {
            // YouTube unreachable: accept the link, title/channel/date can't be shown.
            return ['ok' => true, 'url' => $url, 'title' => null, 'channel' => null, 'publishedAt' => null];
        }

        if ($res->successful()) {
            $title   = trim((string) $res->json('title'));
            $channel = trim((string) $res->json('author_name'));

            return [
                'ok'          => true,
                'url'         => $url,
                'title'       => $title !== '' ? Str::limit($title, 250, '') : null,
                'channel'     => $channel !== '' ? Str::limit($channel, 250, '') : null,
                'publishedAt' => $this->publishedAt($url),
            ];
        }

        if (in_array($res->status(), [400, 401, 403, 404], true)) {
            return ['ok' => false, 'error' => "YouTube can't find that video — it may be private or deleted."];
        }

        return ['ok' => true, 'url' => $url, 'title' => null, 'channel' => null, 'publishedAt' => null];
    }

    /**
     * The upload date isn't in the oEmbed response, so it's read off the watch
     * page itself (schema.org "datePublished" meta tag). Best-effort only.
     */
    private function publishedAt(string $url): ?string
    {
        try {
            $res = Http::timeout(6)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $res->successful()) {
            return null;
        }

        // The watch page embeds the date as a full ISO timestamp, e.g.
        // content="2017-01-05T08:07:23-08:00" — only the date part is kept.
        if (preg_match('/itemprop="datePublished"\s+content="(\d{4}-\d{2}-\d{2})/', $res->body(), $m)) {
            return $m[1];
        }

        if (preg_match('/"publishDate":"(\d{4}-\d{2}-\d{2})/', $res->body(), $m)) {
            return $m[1];
        }

        return null;
    }
}
