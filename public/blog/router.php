<?php
declare(strict_types=1);

require __DIR__ . '/../includes/autoload.php';

use LastJob\Blog\Blog;

$blog = new Blog();
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$post = $slug !== '' ? $blog->postBySlug($slug) : null;
$posts = $blog->allPosts();
$siteName = 'The Last Job — Devlog';
$baseUrl = blogBaseUrl();

function blogBaseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/blog/';
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($post): ?>
    <title><?= h((string) $post['title']) ?> — <?= h($siteName) ?></title>
    <meta name="description" content="<?= h((string) ($post['excerpt'] ?? '')) ?>">
<?php else: ?>
    <title><?= h($siteName) ?></title>
    <meta name="description" content="Build log for The Last Job — multi-agent AI heist sim on Cyberpunk RED mechanics.">
<?php endif; ?>
    <link rel="stylesheet" href="/blog/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="/blog/">
            <span class="brand-kicker">Night City build channel</span>
            <span class="brand-title">THE LAST JOB</span>
            <span class="brand-sub">devlog</span>
        </a>
        <nav class="nav">
            <a href="https://github.com/decisionsciencecorp/the-last-job" rel="noopener">GitHub</a>
            <a href="/">Game</a>
        </nav>
    </div>
</header>

<div class="layout wrap">
    <aside class="sidebar">
        <section class="panel">
            <h2>Recent transmissions</h2>
            <ul class="post-list compact">
            <?php foreach (array_slice($posts, 0, 8) as $p): ?>
                <li>
                    <a href="/blog/?slug=<?= h((string) $p['slug']) ?>"<?= ($post && ($post['slug'] ?? '') === ($p['slug'] ?? '')) ? ' class="active"' : '' ?>>
                        <?= h((string) $p['title']) ?>
                    </a>
                    <time datetime="<?= h((string) $p['date']) ?>"><?= h(Blog::formatDate((string) $p['date'])) ?></time>
                </li>
            <?php endforeach; ?>
            </ul>
        </section>
        <section class="panel muted">
            <p>Deterministic PHP engine + Letta NPC minds. Updates land here on every push to <code>main</code>.</p>
        </section>
    </aside>

    <main class="main">
<?php if ($post): ?>
        <article class="post">
            <header class="post-header">
                <p class="post-meta">
                    <time datetime="<?= h((string) $post['date']) ?>"><?= h(Blog::formatDate((string) $post['date'])) ?></time>
                    <?php if (!empty($post['author'])): ?> · <?= h((string) $post['author']) ?><?php endif; ?>
                    <?php if (!empty($post['commit'])): ?> · <code><?= h((string) $post['commit']) ?></code><?php endif; ?>
                </p>
                <h1><?= h((string) $post['title']) ?></h1>
                <?php if (!empty($post['tags']) && is_array($post['tags'])): ?>
                <p class="tags"><?php foreach ($post['tags'] as $tag): ?><span class="tag"><?= h((string) $tag) ?></span><?php endforeach; ?></p>
                <?php endif; ?>
            </header>
            <div class="post-body"><?= $post['body_html'] ?></div>
        </article>
<?php else: ?>
        <section class="index-intro">
            <h1>Devlog</h1>
            <p class="lead">Shipping a multi-agent heist sim — engine in PHP, minds on Letta, rules from Cyberpunk RED. This is the honest build record.</p>
        </section>
        <ul class="post-list">
        <?php foreach ($posts as $p): ?>
            <li class="post-card">
                <time datetime="<?= h((string) $p['date']) ?>"><?= h(Blog::formatDate((string) $p['date'])) ?></time>
                <h2><a href="/blog/?slug=<?= h((string) $p['slug']) ?>"><?= h((string) $p['title']) ?></a></h2>
                <?php if (!empty($p['excerpt'])): ?><p><?= h((string) $p['excerpt']) ?></p><?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
<?php endif; ?>
    </main>
</div>

<footer class="site-footer wrap">
    <p>Decision Science Corp · Otto Vernal · <a href="https://tasks.decisionsciencecorp.com/admin/project.php?id=16">Tasks board</a></p>
</footer>
</body>
</html>
