<?php
declare(strict_types=1);

/**
 * Placeholder landing page. The real crew-builder / job-board / run UI lands in
 * a later slice (60-UI-UX). This confirms the LEMP entrypoint works.
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Last Job</title>
    <style>
        :root { color-scheme: dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center;
            background: #0a0a0f; color: #e6f1ff; font: 16px/1.5 ui-monospace, monospace; }
        .card { max-width: 40rem; padding: 2rem; border: 1px solid #1f6feb55;
            box-shadow: 0 0 40px #1f6feb22; border-radius: 12px; }
        h1 { color: #ff2e88; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 .5rem; }
        .muted { color: #8b98a5; }
        code { color: #58a6ff; }
    </style>
</head>
<body>
    <div class="card">
        <h1>The Last Job</h1>
        <p>Engine online. The NET is waiting.</p>
        <p class="muted">Slice #1 ships the deterministic netrunner engine. UI is a later slice.
        Run a netrun from the CLI: <code>php bin/netrun-demo.php --seed=1337</code></p>
    </div>
</body>
</html>
