<?php
declare(strict_types=1);

/**
 * Dev subdomain and main site entry routing.
 * - dev.the-last-job.decisionsciencecorp.com -> dev blog
 * - apex -> game landing (UI slices later)
 */

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
if (str_starts_with($host, 'dev.')) {
    require __DIR__ . '/blog/router.php';
    exit;
}

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
        .card { max-width: 42rem; padding: 2rem; border: 1px solid #1f6feb55;
            box-shadow: 0 0 40px #1f6feb22; border-radius: 12px; }
        h1 { color: #ff2e88; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 .5rem; }
        .muted { color: #8b98a5; }
        code { color: #58a6ff; }
        a { color: #58a6ff; }
    </style>
</head>
<body>
    <div class="card">
        <h1>The Last Job</h1>
        <p>Engine online. The NET is waiting.</p>
        <p class="muted">Play in the browser: <a href="/play.php">Run a job</a> · Build log: <a href="https://dev.the-last-job.decisionsciencecorp.com/">dev blog</a>.</p>
        <p class="muted"><code>php bin/job-demo.php --seed=2077</code></p>
    </div>
</body>
</html>
