#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Rebuild devlog visuals so every post has its own illustration.
 *
 * Usage:
 *   php tools/rebuild-devlog-visuals.php
 */

$root = dirname(__DIR__);
$contentDir = $root . '/public/blog/content';
$illustrationDir = $root . '/public/blog/assets/visuals/illustrations';

if (!is_dir($illustrationDir)) {
    mkdir($illustrationDir, 0755, true);
}

$count = 0;
foreach (glob($contentDir . '/*.md') ?: [] as $path) {
    $raw = (string) file_get_contents($path);
    if (!preg_match('/^---\n(.*?)\n---\n(.*)$/s', $raw, $match)) {
        continue;
    }
    $front = $match[1];
    $body = $match[2];
    $title = frontValue($front, 'title') ?: basename($path, '.md');
    $slug = frontValue($front, 'slug') ?: slugify($title);
    $summary = firstMeaningfulLine($body) ?: $title;
    $area = visualArea($title . "\n" . $body);
    $illustration = "/blog/assets/visuals/illustrations/{$slug}.svg";
    $screenshot = screenshotForArea($area);

    file_put_contents($illustrationDir . "/{$slug}.svg", buildIllustrationSvg($title, $summary, $slug, $area));

    $visuals = <<<MD
## Visuals

![Devlog illustration: {$title}]({$illustration})

![Devlog screenshot: {$area}.]({$screenshot})
MD;

    if (preg_match('/\n## Visuals\n.*$/s', $body)) {
        $body = preg_replace('/\n## Visuals\n.*$/s', "\n" . $visuals . "\n", $body) ?? $body;
    } else {
        $body = rtrim($body) . "\n\n" . $visuals . "\n";
    }

    file_put_contents($path, "---\n{$front}\n---\n{$body}");
    $count++;
}

fwrite(STDOUT, "Rebuilt visuals for {$count} devlog posts.\n");

function frontValue(string $front, string $key): string
{
    if (!preg_match('/^' . preg_quote($key, '/') . ':\s*(.+)$/m', $front, $match)) {
        return '';
    }
    return trim(trim($match[1]), "\"'");
}

function slugify(string $value): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? 'post');
    return trim($slug, '-') ?: 'post';
}

function firstMeaningfulLine(string $body): string
{
    foreach (explode("\n", $body) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '![') || str_starts_with($line, '---')) {
            continue;
        }
        if (str_starts_with($line, 'Shipped as ')) {
            continue;
        }
        return strip_tags($line);
    }
    return '';
}

function visualArea(string $text): string
{
    $s = strtolower($text);
    return match (true) {
        str_contains($s, 'terminal') || str_contains($s, 'deck') || str_contains($s, 'wire') || str_contains($s, 'ux') => 'terminal deck flow',
        str_contains($s, 'crew') || str_contains($s, 'lifepath') || str_contains($s, 'chrome') => 'crew dossier surface',
        str_contains($s, 'intel') || str_contains($s, 'dossier') || str_contains($s, 'campaign') || str_contains($s, 'story') => 'campaign dossier',
        str_contains($s, 'run') || str_contains($s, 'aftermath') || str_contains($s, 'wallet') || str_contains($s, 'cred') => 'run aftermath',
        str_contains($s, 'test') || str_contains($s, 'e2e') || str_contains($s, 'link') || str_contains($s, 'qa') => 'verification rig',
        str_contains($s, 'letta') || str_contains($s, 'narrat') || str_contains($s, 'prefetch') || str_contains($s, 'cache') => 'agent narration loop',
        str_contains($s, 'netrun') || str_contains($s, 'skill') || str_contains($s, 'humanity') => 'rules engine',
        default => 'build transmission',
    };
}

function screenshotForArea(string $area): string
{
    return match ($area) {
        'terminal deck flow' => '/blog/assets/visuals/screenshots/home.png',
        'crew dossier surface' => '/blog/assets/visuals/screenshots/crew-builder.png',
        'campaign dossier' => '/blog/assets/visuals/screenshots/intel-dossier.png',
        'run aftermath' => '/blog/assets/visuals/screenshots/run-aftermath.png',
        'verification rig' => '/blog/assets/visuals/screenshots/devlog-index.png',
        'agent narration loop' => '/blog/assets/visuals/screenshots/job-board.png',
        'rules engine' => '/blog/assets/visuals/screenshots/job-board.png',
        default => '/blog/assets/visuals/screenshots/devlog-index.png',
    };
}

function buildIllustrationSvg(string $title, string $summary, string $slug, string $area): string
{
    $hash = sprintf('%u', crc32($slug));
    $h = (int) $hash;
    $accent = ['#39ff14', '#05d9e8', '#ff2a6d', '#f6c945', '#9b5cff'][$h % 5];
    $accent2 = ['#05d9e8', '#ff2a6d', '#f6c945', '#39ff14', '#ffffff'][($h >> 3) % 5];
    $leftX = 80 + (($h >> 5) % 160);
    $midX = 420 + (($h >> 7) % 180);
    $rightX = 830 + (($h >> 11) % 190);
    $startY = 180 + (($h >> 13) % 260);
    $midY = 180 + (($h >> 17) % 260);
    $rightY = 170 + (($h >> 19) % 260);
    $bars = '';
    for ($i = 0; $i < 8; $i++) {
        $height = 50 + (($h >> ($i + 2)) % 190);
        $x = 90 + ($i * 68);
        $y = 545 - $height;
        $color = $i % 2 === 0 ? $accent : $accent2;
        $bars .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"34\" height=\"{$height}\" fill=\"{$color}\" opacity=\"0.28\"/>\n";
    }

    $safeTitle = svgText($title, 54);
    $safeSummary = svgText($summary, 78);
    $safeArea = strtoupper(svgText($area, 32));
    $safeSlug = svgText($slug, 60);

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675" role="img" aria-label="Devlog illustration for {$safeTitle}">
  <rect width="1200" height="675" fill="#05070a"/>
  <rect x="44" y="44" width="1112" height="587" fill="#0b1015" stroke="{$accent}" stroke-opacity="0.58"/>
  <g opacity="0.18" stroke="{$accent2}" stroke-width="1">
    <path d="M80 120 H1120 M80 210 H1120 M80 300 H1120 M80 390 H1120 M80 480 H1120"/>
    <path d="M160 72 V604 M320 72 V604 M480 72 V604 M640 72 V604 M800 72 V604 M960 72 V604"/>
  </g>
  <path d="M70 {$startY} C {$leftX} 210, {$midX} 520, 1120 250" fill="none" stroke="{$accent}" stroke-width="6" opacity="0.48"/>
  <polyline points="82,376 {$leftX},298 {$midX},{$midY} {$rightX},{$rightY} 1118,292" fill="none" stroke="{$accent2}" stroke-width="3" opacity="0.78"/>
  {$bars}
  <circle cx="980" cy="170" r="86" fill="none" stroke="{$accent}" stroke-width="7" opacity="0.42"/>
  <circle cx="980" cy="170" r="34" fill="{$accent2}" opacity="0.24"/>
  <text x="82" y="122" fill="{$accent2}" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="22" letter-spacing="5">{$safeArea}</text>
  <text x="82" y="210" fill="#e6edf3" font-family="Inter, system-ui, sans-serif" font-size="48" font-weight="700">{$safeTitle}</text>
  <text x="84" y="272" fill="#aeb8c5" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="20">{$safeSummary}</text>
  <text x="82" y="588" fill="{$accent}" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="19" letter-spacing="3">{$safeSlug}</text>
</svg>
SVG;
}

function svgText(string $text, int $max): string
{
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? trim($text);
    if (mb_strlen($text) > $max) {
        $text = rtrim(mb_substr($text, 0, max(0, $max - 1))) . '...';
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
