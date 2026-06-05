<?php
declare(strict_types=1);

namespace LastJob\Blog;

/** Minimal markdown -> HTML for dev blog posts (no Composer). */
final class Markdown
{
    public static function toHtml(string $md): string
    {
        $md = str_replace(["\r\n", "\r"], "\n", trim($md));
        $html = [];
        $inCode = false;
        $codeBuf = [];
        $paragraph = [];
        $list = [];

        $flushParagraph = static function () use (&$html, &$paragraph): void {
            if ($paragraph === []) {
                return;
            }
            $html[] = '<p>' . self::inline(implode(' ', array_map('trim', $paragraph))) . '</p>';
            $paragraph = [];
        };
        $flushList = static function () use (&$html, &$list): void {
            if ($list === []) {
                return;
            }
            $html[] = '<ul>' . implode('', $list) . '</ul>';
            $list = [];
        };

        foreach (explode("\n", $md) as $line) {
            $trim = trim($line);

            if ($inCode) {
                if (str_starts_with($trim, '```')) {
                    $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeBuf), ENT_QUOTES, 'UTF-8') . '</code></pre>';
                    $inCode = false;
                    $codeBuf = [];
                } else {
                    $codeBuf[] = $line;
                }
                continue;
            }

            if (str_starts_with($trim, '```')) {
                $flushParagraph();
                $flushList();
                $inCode = true;
                $codeBuf = [];
                continue;
            }

            if ($trim === '') {
                $flushParagraph();
                $flushList();
                continue;
            }

            if (preg_match('/^#{1,6}\s+(.+)$/', $trim, $m)) {
                $flushParagraph();
                $flushList();
                $level = min(6, strlen(strtok($trim, ' ')));
                $html[] = sprintf('<h%d>%s</h%d>', $level, self::inline($m[1]), $level);
                continue;
            }

            if (preg_match('/^!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]+)")?\)$/', $trim, $m)) {
                $flushParagraph();
                $flushList();
                $alt = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $src = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
                $caption = isset($m[3]) ? htmlspecialchars($m[3], ENT_QUOTES, 'UTF-8') : '';
                $figcaption = $caption !== '' ? '<figcaption>' . $caption . '</figcaption>' : '';
                $html[] = '<figure><img src="' . $src . '" alt="' . $alt . '" loading="lazy">' . $figcaption . '</figure>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.*)$/', $trim, $m)) {
                $flushParagraph();
                $list[] = '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }

            $flushList();
            $paragraph[] = $line;
        }

        $flushParagraph();
        $flushList();
        if ($inCode && $codeBuf) {
            $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeBuf), ENT_QUOTES, 'UTF-8') . '</code></pre>';
        }

        return implode("\n", $html);
    }

    private static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text) ?? $text;
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text) ?? $text;
        return $text;
    }
}
