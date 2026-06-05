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
$contentDir = $root . '/content/blog';

$sha = trim((string) shell_exec('git rev-parse --short HEAD'));
$fullMsg = trim((string) shell_exec('git log -1 --format=%B'));
$subject = trim((string) shell_exec('git log -1 --format=%s'));
$isoDate = trim((string) shell_exec('git log -1 --format=%cI'));

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
$body = <<<MD
Pushed to `main` as **`{$sha}`**.

```
{$subject}
```

Automated devlog entry — see the repo diff for full changes.

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
file_put_contents($path, $yaml . $body);
fwrite(STDOUT, "Created {$path}\n");
