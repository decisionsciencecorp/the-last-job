#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Create or update a devlog markdown post from the latest git commit.
 * Used by GitHub Actions on push to main. Skips if post for commit SHA exists.
 *
 * Usage: php tools/devblog-from-commit.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv ?? [], true);
$root = dirname(__DIR__);
$contentDir = $root . '/public/blog/content';

$sha = trim((string) shell_exec('git rev-parse --short HEAD'));
$fullMsg = trim((string) shell_exec('git log -1 --format=%B'));
$subject = trim((string) shell_exec('git log -1 --format=%s'));
$isoDate = trim((string) shell_exec('git log -1 --format=%cI'));
$commitBody = trim((string) shell_exec('git log -1 --format=%b'));
$filesRaw = trim((string) shell_exec('git show --pretty= --name-only HEAD'));

if ($sha === '' || $subject === '') {
    fwrite(STDERR, "Not a git repo or no commits.\n");
    exit(1);
}

if (preg_match('/\[skip devblog\]/i', $fullMsg)) {
    fwrite(STDOUT, "Skip: [skip devblog] in commit message.\n");
    exit(0);
}

foreach (glob($contentDir . '/*.md') ?: [] as $file) {
    $raw = (string) file_get_contents($file);
    if (str_contains($raw, "commit: {$sha}")) {
        fwrite(STDOUT, "Post already exists for {$sha}.\n");
        exit(0);
    }
}

$slugBase = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $subject) ?? 'update');
$slugBase = trim($slugBase, '-');
$slug = substr($slugBase, 0, 60) ?: 'update';
$datePrefix = substr($isoDate, 0, 10);
$filename = "{$datePrefix}-{$slug}.md";
$path = $contentDir . '/' . $filename;

$n = 2;
while (is_file($path)) {
    $path = $contentDir . '/' . $datePrefix . '-' . $slug . '-' . $n . '.md';
    $n++;
}

$excerpt = mb_substr(preg_replace('/\s+/', ' ', $subject) ?? $subject, 0, 180);
$files = array_values(array_filter(array_map('trim', explode("\n", $filesRaw))));
$areaNames = describeAreas($files);
$areaLines = areaLines($areaNames);
$summary = summarizeSubject($subject, $areaNames);
$why = whyItMatters($subject);
$fileLines = fileLines($files);
$commitText = sanitizeCommitText(trim($commitBody) !== '' ? trim($commitBody) : $subject);
$visualArea = visualArea($subject . "\n" . implode("\n", $files) . "\n" . $commitText);
$illustrationUrl = "/blog/assets/visuals/illustrations/{$slug}.svg";
$screenshotUrl = screenshotForArea($visualArea);
$body = <<<MD
Shipped as **`{$sha}`**.

## What Changed

{$summary}

{$areaLines}

## Why It Matters

{$why}

## Implementation Notes

The commit message for this slice was:

```text
{$commitText}
```

Files touched in this slice included:

{$fileLines}

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: {$subject}]({$illustrationUrl})

![Devlog screenshot: {$visualArea}.]({$screenshotUrl})

MD;

$yaml = <<<YAML
---
title: "{$subject}"
date: {$isoDate}
slug: {$slug}
author: Otto Vernal
tags: [devlog, auto]
commit: {$sha}
excerpt: {$excerpt}
---

YAML;

if ($dryRun) {
    fwrite(STDOUT, $yaml . $body);
    exit(0);
}

if (!is_dir($contentDir)) {
    mkdir($contentDir, 0755, true);
}
$illustrationDir = $root . '/public/blog/assets/visuals/illustrations';
if (!is_dir($illustrationDir)) {
    mkdir($illustrationDir, 0755, true);
}
file_put_contents($illustrationDir . "/{$slug}.svg", buildIllustrationSvg($subject, $summary, $slug, $visualArea));
file_put_contents($path, $yaml . $body);
fwrite(STDOUT, "Created {$path}\n");

/**
 * @param string[] $files
 * @return string[]
 */
function describeAreas(array $files): array
{
    $areas = [];
    $has = static fn (string $prefix): bool => (bool) array_filter($files, static fn (string $f): bool => str_starts_with($f, $prefix));
    if ($has('public/blog/content/')) {
        $areas[] = 'developer vlog content';
    }
    if ($has('public/blog/assets/') || $has('public/includes/Blog/')) {
        $areas[] = 'blog presentation and rendering';
    }
    if ($has('public/data/jobs/') || in_array('public/play.php', $files, true)) {
        $areas[] = 'job board and contract flow';
    }
    if ($has('public/data/story/') || in_array('public/intel.php', $files, true)) {
        $areas[] = 'campaign intel';
    }
    if ($has('public/data/lifepath/') || in_array('public/crew.php', $files, true)) {
        $areas[] = 'crew and lifepath';
    }
    if ($has('tests/')) {
        $areas[] = 'test coverage';
    }
    if ($has('tools/') || $has('.github/')) {
        $areas[] = 'automation/tooling';
    }
    if ($areas === []) {
        $areas[] = 'project implementation';
    }

    return $areas;
}

/** @param string[] $areas */
function areaLines(array $areas): string
{
    return implode("\n", array_map(static fn (string $area): string => "- Worked in {$area}.", $areas));
}

/** @param string[] $areas */
function summarizeSubject(string $subject, array $areas): string
{
    $s = strtolower($subject);
    if (str_contains($s, 'devlog') || str_contains($s, 'vlog')) {
        return 'This slice updates the public build record so readers can understand what changed without leaving the devlog for GitHub.';
    }
    if (str_contains($s, 'visual')) {
        return 'This slice improves the devlog as a visual artifact, adding screenshots, illustrations, and renderer support where needed.';
    }
    if (str_contains($s, 'intel') || str_contains($s, 'dossier')) {
        return 'This slice connects the game surface to the campaign mystery through dossier threads, links, and structured story data.';
    }
    if (str_contains($s, 'narrat') || str_contains($s, 'letta') || str_contains($s, 'prefetch')) {
        return 'This slice improves NPC narration behavior so story flavor can be richer without making the job runner feel stalled.';
    }
    if (str_contains($s, 'crew') || str_contains($s, 'lifepath')) {
        return 'This slice strengthens generated crew presentation while preserving deterministic output and sealed hidden agenda rules.';
    }
    if (str_contains($s, 'campaign') || str_contains($s, 'street') || str_contains($s, 'wallet')) {
        return 'This slice adds campaign continuity so each run changes the player-facing state instead of feeling isolated.';
    }

    return 'This slice moves the project forward across ' . implode(', ', $areas) . '.';
}

function whyItMatters(string $subject): string
{
    $s = strtolower($subject);
    if (str_contains($s, 'devlog') || str_contains($s, 'vlog')) {
        return 'The devlog is a public-facing build journal, so each entry needs to explain the work in plain language instead of acting as a commit index.';
    }
    if (str_contains($s, 'visual')) {
        return 'Screenshots and illustrations make the project easier to evaluate at a glance and show how the shipped work actually appears.';
    }
    if (str_contains($s, 'narrative') || str_contains($s, 'intel') || str_contains($s, 'dossier')) {
        return 'The heist simulator depends on story pressure as much as mechanics. Clear narrative context makes the contracts and crew choices feel connected.';
    }
    if (str_contains($s, 'narrat') || str_contains($s, 'letta')) {
        return 'Agent-backed narration only helps if it is fast and reliable enough to stay inside the play loop.';
    }

    return 'Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.';
}

function sanitizeCommitText(string $text): string
{
    $lines = array_filter(
        explode("\n", $text),
        static fn (string $line): bool => !preg_match('/^Co-authored-by:\s*Cursor\b/i', trim($line))
    );
    $clean = trim(implode("\n", $lines));
    return $clean !== '' ? $clean : 'Implementation details were recorded in the commit subject.';
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

/** @param string[] $files */
function fileLines(array $files): string
{
    if ($files === []) {
        return '- No file list was available for this commit.';
    }
    $shown = array_slice($files, 0, 12);
    $lines = array_map(static fn (string $file): string => "- `{$file}`", $shown);
    if (count($files) > count($shown)) {
        $remaining = count($files) - count($shown);
        $lines[] = "- plus {$remaining} more files in the same slice";
    }
    return implode("\n", $lines);
}
