<?php
declare(strict_types=1);

namespace LastJob;

function layout_h(string|int|float $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function layout_header(string $title, string $active = ''): void
{
    $nav = static function (string $href, string $label, string $key) use ($active): string {
        $class = $active === $key ? ' class="active"' : '';

        return '<a href="' . layout_h($href) . '"' . $class . '>' . layout_h($label) . '</a>';
    };
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= layout_h($title) ?> — The Last Job</title>
    <link rel="stylesheet" href="/blog/assets/style.css">
    <link rel="stylesheet" href="/assets/game.css">
</head>
<body>
<header class="site-header"><div class="wrap">
    <a class="brand" href="/"><span class="brand-title">./lastjob</span></a>
    <nav class="nav">
        <span class="nav-prefix">cmd:</span>
        <?= $nav('/', 'deck', 'deck') ?>
        <?= $nav('/play.php', 'wire', 'wire') ?>
        <?= $nav('/crew.php', 'fixer.roster', 'booth') ?>
        <?= $nav('/crew.php#chair', 'chair', 'chair') ?>
        <?= $nav('/play.php#run', 'run', 'run') ?>
        <?= $nav('/intel.php', 'file', 'file') ?>
        <?= $nav('/play.php#wake', 'wake', 'wake') ?>
    </nav>
</div></header>
<main class="wrap game-main">
    <?php
}

function layout_footer(): void
{
    ?>
</main>
<footer class="site-footer wrap">
    <a href="/">Deck</a>
    <span>·</span>
    <a href="/blog/">Build Log</a>
    <span>·</span>
    <a href="https://tasks.decisionsciencecorp.com/admin/project.php?id=16">Tasks</a>
</footer>
</body>
</html>
    <?php
}
