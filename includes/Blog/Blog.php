<?php
declare(strict_types=1);

namespace LastJob\Blog;

/**
 * File-backed dev blog. Posts live in content/blog/*.md with YAML front matter.
 * Pattern: technonomicon article list + sanctum blog template shape, but
 * markdown-in-repo (updates on every git push / deploy).
 */
final class Blog
{
    private string $contentDir;

    public function __construct(?string $contentDir = null)
    {
        $this->contentDir = $contentDir ?? dirname(__DIR__, 2) . '/content/blog';
    }

    /** @return array<int,array<string,mixed>> newest first */
    public function allPosts(): array
    {
        $posts = [];
        foreach (glob($this->contentDir . '/*.md') ?: [] as $path) {
            $post = $this->loadFile($path);
            if ($post !== null) {
                $posts[] = $post;
            }
        }
        usort($posts, static fn ($a, $b) => strcmp((string) $b['date'], (string) $a['date']));
        return $posts;
    }

    /** @return array<string,mixed>|null */
    public function postBySlug(string $slug): ?array
    {
        foreach ($this->allPosts() as $post) {
            if (($post['slug'] ?? '') === $slug) {
                return $post;
            }
        }
        return null;
    }

    /** @return array<string,mixed>|null */
    private function loadFile(string $path): ?array
    {
        $raw = (string) file_get_contents($path);
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)\z/s', $raw, $m)) {
            return null;
        }
        $meta = $this->parseFrontMatter($m[1]);
        $body = trim($m[2]);
        if (empty($meta['slug']) || empty($meta['title'])) {
            return null;
        }
        $meta['body_md'] = $body;
        $meta['body_html'] = Markdown::toHtml($body);
        $meta['source'] = basename($path);
        return $meta;
    }

    /** @return array<string,mixed> */
    private function parseFrontMatter(string $yaml): array
    {
        $out = [];
        foreach (explode("\n", $yaml) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$key, $val] = array_map('trim', explode(':', $line, 2));
            $val = trim($val, " \t\"'");
            if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                $inner = trim(substr($val, 1, -1));
                $out[$key] = $inner === '' ? [] : array_map(static fn ($t) => trim($t, " \t\"'"), explode(',', $inner));
            } else {
                $out[$key] = $val;
            }
        }
        return $out;
    }

    public static function formatDate(string $iso): string
    {
        $ts = strtotime($iso);
        return $ts ? date('M j, Y', $ts) : $iso;
    }
}
