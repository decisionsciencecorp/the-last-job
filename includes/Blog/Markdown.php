<?php
declare(strict_types=1);

namespace LastJob\Blog;

/** Minimal markdown -> HTML for dev blog posts (no Composer). */
final class Markdown
{
    public static function toHtml(string $md): string
    {
        $md = str_replace(["\r\n", "\r"], "\n", trim($md));
        $blocks = preg_split("/\n{2,}/", $md) ?: [];
        $html = [];
        $inCode = false;
        $codeBuf = [];

        foreach ($blocks as $block) {
            if (str_starts_with($block, '```')) {
                if (!$inCode) {
                    $inCode = true;
                    $codeBuf = [];
                    $first = explode("\n", $block, 2);
                    if (isset($first[1])) {
                        $codeBuf[] = $first[1];
                    }
                    continue;
                }
            }
            if ($inCode) {
                if (str_starts_with(trim($block), '```')) {
                    $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeBuf), ENT_QUOTES, 'UTF-8') . '</code></pre>';
                    $inCode = false;
                    $codeBuf = [];
                    continue;
                }
                $codeBuf[] = $block;
                continue;
            }

            $trim = trim($block);
            if ($trim === '') {
                continue;
            }
            if (preg_match('/^#{1,6}\s+(.+)$/', $trim, $m)) {
                $level = min(6, strlen(strtok($trim, ' ')));
                $html[] = sprintf('<h%d>%s</h%d>', $level, self::inline($m[1]), $level);
                continue;
            }
            if (preg_match('/^[-*]\s+/m', $trim)) {
                $items = preg_split('/\n/', $trim) ?: [];
                $lis = [];
                foreach ($items as $line) {
                    if (preg_match('/^[-*]\s+(.*)$/', trim($line), $lm)) {
                        $lis[] = '<li>' . self::inline($lm[1]) . '</li>';
                    }
                }
                if ($lis) {
                    $html[] = '<ul>' . implode('', $lis) . '</ul>';
                }
                continue;
            }
            $html[] = '<p>' . self::inline(str_replace("\n", ' ', $trim)) . '</p>';
        }

        if ($inCode && $codeBuf) {
            $html[] = '<pre><code>' . htmlspecialchars(implode("\n\n", $codeBuf), ENT_QUOTES, 'UTF-8') . '</code></pre>';
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
