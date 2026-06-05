<?php
declare(strict_types=1);

require __DIR__ . '/includes/autoload.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Economy;
use LastJob\JobRunner;
use LastJob\Lifepath\CrewBuilder;

$rules = new Rules();
$seed = isset($_GET['seed']) ? (int) $_GET['seed'] : 2077;
$jobId = isset($_GET['job']) ? (string) $_GET['job'] : 'job.arasaka-substation';

$jobs = $rules->jobs();
$crew = (new CrewBuilder($rules, new Rng($seed)))->build();
$economy = new Economy(500, 4);
$report = null;
$error = null;

if (isset($_GET['run'])) {
    try {
        $report = (new JobRunner($rules))->run($crew, $rules->job($jobId), new Rng($seed * 7 + 1), $economy);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Run a job — The Last Job</title>
    <link rel="stylesheet" href="/blog/assets/style.css">
    <style>
        .form-grid { display: grid; gap: 1rem; max-width: 36rem; margin: 1.5rem 0; }
        label { display: grid; gap: .35rem; font-size: .9rem; color: var(--muted); }
        input, select, button {
            font: inherit; padding: .55rem .75rem; border-radius: 8px;
            border: 1px solid var(--line); background: #0d1117; color: var(--text);
        }
        button { cursor: pointer; background: #1f6feb33; color: var(--accent2); font-weight: 600; }
        .crew-grid { display: grid; gap: .75rem; margin: 1rem 0 2rem; }
        .crew-card { border: 1px solid var(--line); border-radius: 8px; padding: .85rem 1rem; background: var(--panel); }
        .ok { color: #3fb950; } .bad { color: #f85149; }
        .beats { font: 13px/1.5 var(--mono); }
    </style>
</head>
<body>
<header class="site-header"><div class="wrap">
    <a class="brand" href="/"><span class="brand-title">THE LAST JOB</span></a>
    <nav class="nav"><a href="/">Home</a><a href="/blog/">Devlog</a></nav>
</div></header>
<main class="wrap" style="padding:2rem 1.25rem 3rem;">
    <h1>Run a job</h1>
    <p class="lead">Deterministic browser slice — same seed reproduces the same crew and after-action report.</p>

    <form class="form-grid" method="get">
        <label>Seed <input type="number" name="seed" value="<?= h((string) $seed) ?>"></label>
        <label>Contract
            <select name="job">
            <?php foreach ($jobs as $j): ?>
                <option value="<?= h($j->id) ?>"<?= $j->id === $jobId ? ' selected' : '' ?>><?= h($j->name) ?></option>
            <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" name="run" value="1">Jack in and run</button>
    </form>

    <h2>Crew (seed <?= h((string) $seed) ?>)</h2>
    <div class="crew-grid">
    <?php foreach ($crew as $m): $c = $m->toPublicArray(); ?>
        <div class="crew-card">
            <strong><?= h($c['handle']) ?></strong> · <?= h($c['role']) ?><br>
            <span class="muted"><?= h((string) $c['personality']) ?> — <?= h((string) $c['origin']) ?></span>
        </div>
    <?php endforeach; ?>
    </div>

<?php if ($error): ?>
    <p class="bad"><?= h($error) ?></p>
<?php elseif ($report): ?>
    <h2 class="<?= $report['success'] ? 'ok' : 'bad' ?>"><?= $report['success'] ? 'JOB SUCCESS' : 'JOB FAILED' ?></h2>
    <p>Payout: <strong><?= (int) $report['payout_eddies'] ?></strong> eddies · Street cred +<?= (int) $report['street_cred_gained'] ?></p>
    <h3>On-site beats</h3>
    <ul class="beats">
    <?php foreach ($report['beats'] as $b): ?>
        <li><?= h($b['member']) ?> — <?= h($b['obstacle']) ?> — <?= $b['success'] ? 'PASS' : 'FAIL' ?> (<?= (int) $b['total'] ?> vs <?= (int) $b['dv'] ?>)</li>
    <?php endforeach; ?>
    </ul>
    <p>Netrun: <code><?= h((string) $report['netrun']['outcome']) ?></code> · <?= (int) $report['netrun']['floors_cleared'] ?> floors · deck <?= (int) $report['netrun']['deck_hp_remaining'] ?> HP</p>
    <?php if ($report['agendas_triggered']): ?>
    <h3>Agendas surfaced</h3>
    <ul>
    <?php foreach ($report['agendas_triggered'] as $a): ?>
        <li><strong><?= h($a['member']) ?></strong> — <?= h((string) $a['agenda']) ?> (<?= h(strtoupper((string) $a['type'])) ?>)</li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
