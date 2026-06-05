<?php
declare(strict_types=1);

require __DIR__ . '/includes/autoload.php';
require __DIR__ . '/includes/Layout.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Economy;
use LastJob\JobRunner;
use LastJob\Lifepath\CrewBuilder;
use LastJob\Letta\LettaServices;
use LastJob\Letta\NpcIntentBroker;
use function LastJob\layout_header;
use function LastJob\layout_footer;
use function LastJob\layout_h;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rules = new Rules();
$seed = isset($_GET['seed']) ? (int) $_GET['seed'] : 2077;
$jobId = isset($_GET['job']) ? (string) $_GET['job'] : 'job.arasaka-substation';
$narrate = isset($_GET['narrate']);
$narrateFast = !isset($_GET['narrate_fast']) || (string) $_GET['narrate_fast'] !== '0';
$useCampaign = !isset($_GET['campaign']) || (string) $_GET['campaign'] !== '0';
$manualStreetCred = isset($_GET['street_cred']) ? max(0, (int) $_GET['street_cred']) : 4;
$campaignNotice = null;
$campaignState = $_SESSION['campaign_state'] ?? ['eddies' => 500, 'street_cred' => 4, 'last_run_key' => null];
if (!is_array($campaignState) || !isset($campaignState['eddies'], $campaignState['street_cred'])) {
    $campaignState = ['eddies' => 500, 'street_cred' => 4, 'last_run_key' => null];
}
$runHistory = $_SESSION['run_history'] ?? [];
if (!is_array($runHistory)) {
    $runHistory = [];
}
if (isset($_GET['reset_campaign'])) {
    $campaignState = ['eddies' => 500, 'street_cred' => 4, 'last_run_key' => null];
    $_SESSION['campaign_state'] = $campaignState;
    $runHistory = [];
    $_SESSION['run_history'] = [];
    $campaignNotice = 'Campaign wallet reset.';
}
$streetCred = $useCampaign ? max(0, (int) $campaignState['street_cred']) : $manualStreetCred;
$startingEddies = $useCampaign ? max(0, (int) $campaignState['eddies']) : 500;
$rolesCatalog = $rules->roles();
$roleIds = array_keys($rolesCatalog);
$defaultRoles = CrewBuilder::DEFAULT_ROLES;

$rolePick = [];
for ($i = 0; $i < 4; $i++) {
    $key = 'role' . $i;
    $picked = isset($_GET[$key]) ? (string) $_GET[$key] : ($defaultRoles[$i] ?? $roleIds[$i % count($roleIds)]);
    if (!isset($rolesCatalog[$picked])) {
        $picked = $defaultRoles[$i] ?? $roleIds[0];
    }
    $rolePick[] = $picked;
}

$jobs = $rules->jobs();
$crew = (new CrewBuilder($rules, new Rng($seed)))->build($rolePick);
$economy = new Economy($startingEddies, $streetCred);
$report = null;
$error = null;
$narrator = null;
$narratorError = null;
$jobUnlocked = [];
foreach ($jobs as $j) {
    $jobUnlocked[$j->id] = $rules->isJobUnlocked($j->id, $streetCred);
}
if (!isset($_GET['run']) && empty($jobUnlocked[$jobId] ?? false)) {
    foreach ($jobs as $candidate) {
        if ($jobUnlocked[$candidate->id] ?? false) {
            $jobId = $candidate->id;
            break;
        }
    }
}

if ($narrate) {
    try {
        $narrator = LettaServices::brokerFromEnvironment();
        if ($narrator === null) {
            $narratorError = 'Letta not configured on this host (see config/letta.php or LASTJOB_LETTA_* env).';
        }
    } catch (Throwable $e) {
        $narratorError = $e->getMessage();
    }
}

if (isset($_GET['run'])) {
    if (empty($jobUnlocked[$jobId] ?? false)) {
        $needed = $rules->job($jobId)->minRepTier;
        $error = "Street cred {$streetCred} is too low for this contract (need {$needed}).";
    } else {
        try {
            $report = (new JobRunner($rules))->run($crew, $rules->job($jobId), new Rng($seed * 7 + 1), $economy);
            if ($report !== null && $narrator !== null) {
                $runId = NpcIntentBroker::runId($seed, $jobId);
                $report = $narrator->enrichReport($report, $runId, $narrateFast ? 1 : PHP_INT_MAX);
            }
            if ($useCampaign && $report !== null && !empty($report['success'])) {
                $runKey = sha1(json_encode([$seed, $jobId, $rolePick], JSON_UNESCAPED_SLASHES));
                if (($campaignState['last_run_key'] ?? null) !== $runKey) {
                    $campaignState = [
                        'eddies' => (int) ($economy->toArray()['eddies'] ?? $startingEddies),
                        'street_cred' => (int) ($economy->toArray()['street_cred'] ?? $streetCred),
                        'last_run_key' => $runKey,
                    ];
                    $_SESSION['campaign_state'] = $campaignState;
                    $campaignNotice = 'Campaign wallet updated from this successful run.';
                } else {
                    $campaignNotice = 'Run rewards already counted for this exact seed/job/crew combo.';
                }
            }
            if ($report !== null) {
                $entry = [
                    'at' => gmdate('c'),
                    'job' => $report['job_name'] ?? $jobId,
                    'success' => !empty($report['success']),
                    'seed' => $seed,
                    'payout' => (int) ($report['payout_eddies'] ?? 0),
                    'cred' => (int) ($report['street_cred_gained'] ?? 0),
                    'outcome' => (string) (($report['netrun']['outcome'] ?? '') ?: 'unknown'),
                ];
                $runHistory[] = $entry;
                if (count($runHistory) > 12) {
                    $runHistory = array_slice($runHistory, -12);
                }
                $_SESSION['run_history'] = $runHistory;
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$selectedJob = $rules->job($jobId);
$clock = $report['clock'] ?? null;

layout_header('Job board', 'play');
?>
<h1>Job board</h1>
<p class="lead">Pick a contract, jack in, and read the after-action report. Same seed reproduces crew and outcomes; optional Letta dialogue is cached per run.</p>
<div class="clock-panel" style="margin:1rem 0;">
    <strong>Campaign wallet</strong>
    <div class="meta-row">
        <span>Eddies <?= (int) $campaignState['eddies'] ?></span>
        <span>Street cred <?= (int) $campaignState['street_cred'] ?></span>
        <span>Mode: <?= $useCampaign ? 'campaign (session)' : 'manual' ?></span>
    </div>
    <?php if ($campaignNotice): ?>
        <p class="status-ok" style="margin:.35rem 0 0;"><?= layout_h($campaignNotice) ?></p>
    <?php endif; ?>
</div>
<?php if ($runHistory !== []): ?>
<div class="clock-panel" style="margin:1rem 0;">
    <strong>Recent runs (session)</strong>
    <ul class="beats" style="margin-top:.5rem;">
    <?php foreach (array_reverse($runHistory) as $row): ?>
        <li>
            <?= layout_h((string) ($row['job'] ?? 'Job')) ?> · seed <?= (int) ($row['seed'] ?? 0) ?>
            — <span class="<?= !empty($row['success']) ? 'status-ok' : 'status-bad' ?>"><?= !empty($row['success']) ? 'SUCCESS' : 'FAIL' ?></span>
            · payout <?= (int) ($row['payout'] ?? 0) ?>eb
            · cred +<?= (int) ($row['cred'] ?? 0) ?>
            · net <?= layout_h((string) ($row['outcome'] ?? 'unknown')) ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="get">
    <input type="hidden" name="campaign" value="0">
    <?php for ($i = 0; $i < 4; $i++): ?>
        <input type="hidden" name="role<?= $i ?>" value="<?= layout_h($rolePick[$i]) ?>">
    <?php endfor; ?>
    <div class="form-grid">
        <label>Seed
            <input type="number" name="seed" value="<?= layout_h((string) $seed) ?>" min="1">
        </label>
        <label>Street cred
            <input type="number" name="street_cred" value="<?= layout_h((string) $streetCred) ?>" min="0">
        </label>
        <label>
            <input type="checkbox" name="campaign" value="1"<?= $useCampaign ? ' checked' : '' ?>>
            Use campaign wallet (session)
        </label>
        <label>
            <input type="checkbox" name="reset_campaign" value="1">
            Reset campaign wallet before applying this run
        </label>
        <label>
            <input type="checkbox" name="narrate" value="1"<?= $narrate ? ' checked' : '' ?>>
            NPC dialogue (Letta — cached per run)
        </label>
        <input type="hidden" name="narrate_fast" value="0">
        <label>
            <input type="checkbox" name="narrate_fast" value="1"<?= $narrateFast ? ' checked' : '' ?>>
            Fast narrate mode (cache-first; max 1 live call)
        </label>
    </div>

    <h2>Contracts</h2>
    <div class="job-grid">
        <?php foreach ($jobs as $j): ?>
            <?php $locked = !($jobUnlocked[$j->id] ?? false); ?>
            <label class="job-card<?= $locked ? ' locked' : '' ?>">
                <h3>
                    <input type="radio" name="job" value="<?= layout_h($j->id) ?>"<?= $j->id === $jobId ? ' checked' : '' ?><?= $locked ? ' disabled' : '' ?>>
                    <?= layout_h($j->name) ?>
                </h3>
                <div class="meta-row">
                    <span>Fixer: <?= layout_h($j->fixer) ?></span>
                    <span><?= (int) $j->payoutEddies ?> eddies</span>
                    <span>Rep +<?= (int) $j->streetCredReward ?></span>
                    <span>Clock <?= (int) $j->difficultyTicks ?> ticks</span>
                    <span>Need cred <?= (int) $j->minRepTier ?></span>
                </div>
                <?php if ($j->briefing !== ''): ?>
                    <p class="job-briefing"><?= layout_h($j->briefing) ?></p>
                <?php endif; ?>
                <?php if ($j->stakes !== ''): ?>
                    <p class="job-stakes"><strong>Stakes:</strong> <?= layout_h($j->stakes) ?></p>
                <?php endif; ?>
                <?php if ($j->complication !== ''): ?>
                    <p class="job-complication"><strong>Complication:</strong> <?= layout_h($j->complication) ?></p>
                <?php endif; ?>
                <?php if ($locked): ?>
                    <p class="status-warn" style="margin:.35rem 0 0;">Locked — needs street cred <?= (int) $j->minRepTier ?></p>
                <?php endif; ?>
                <?php if ($j->tags !== []): ?>
                    <div class="job-tags">
                        <?php foreach ($j->tags as $tag): ?>
                            <span><?= layout_h($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="muted" style="margin:.5rem 0 0;font-size:.85rem;"><?= count($j->obstacles) ?> on-site obstacles · NET <?= layout_h($j->archId) ?></p>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="actions-row">
        <button type="submit" name="run" value="1">Jack in and run</button>
        <a class="btn btn-secondary" href="/crew.php?seed=<?= (int) $seed ?>&roll=1&campaign=<?= $useCampaign ? 1 : 0 ?>&street_cred=<?= (int) $streetCred ?><?= '&amp;role0=' . layout_h($rolePick[0]) . '&amp;role1=' . layout_h($rolePick[1]) . '&amp;role2=' . layout_h($rolePick[2]) . '&amp;role3=' . layout_h($rolePick[3]) ?>">Edit crew for this seed</a>
        <a class="btn btn-secondary" href="/api/narrate-prefetch.php?seed=<?= (int) $seed ?>&job=<?= layout_h($jobId) ?>&street_cred=<?= (int) $streetCred ?>&max_live=1&role0=<?= layout_h($rolePick[0]) ?>&role1=<?= layout_h($rolePick[1]) ?>&role2=<?= layout_h($rolePick[2]) ?>&role3=<?= layout_h($rolePick[3]) ?>" target="_blank" rel="noopener">Warm narration cache</a>
    </div>
</form>

<?php if ($narratorError): ?>
    <p class="status-bad"><?= layout_h($narratorError) ?></p>
<?php endif; ?>
<?php if ($narrate && $narrateFast): ?>
    <p class="muted" style="margin:.4rem 0 1rem;">Fast narrate mode is ON. Uncached beats may be deferred to keep page response fast.</p>
<?php endif; ?>

<h2>Crew preview (seed <?= (int) $seed ?>)</h2>
<div class="crew-grid">
<?php foreach ($crew as $m): $c = $m->toPublicArray(); ?>
    <div class="crew-card">
        <div class="role"><?= layout_h($c['role']) ?></div>
        <h3><?= layout_h($c['handle']) ?></h3>
        <p class="muted"><?= layout_h((string) $c['personality']) ?> — <?= layout_h((string) $c['origin']) ?></p>
    </div>
<?php endforeach; ?>
</div>

<?php if ($error): ?>
    <p class="status-bad"><?= layout_h($error) ?></p>
<?php elseif ($report): ?>
    <h2 class="<?= $report['success'] ? 'status-ok' : 'status-bad' ?>"><?= $report['success'] ? 'JOB SUCCESS' : 'JOB FAILED' ?></h2>
    <p>Payout <strong><?= (int) $report['payout_eddies'] ?></strong> eddies · Street cred +<?= (int) $report['street_cred_gained'] ?></p>
    <?php if (!empty($report['debrief'])): ?>
        <div class="clock-panel">
            <strong>Aftermath</strong>
            <p class="job-briefing"><?= layout_h((string) $report['debrief']) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($clock !== null):
        $spentPct = $clock['total'] > 0 ? (int) round(($clock['spent'] / $clock['total']) * 100) : 0;
        $danger = ($clock['remaining'] ?? 0) <= 2;
    ?>
        <div class="clock-panel">
            <strong>Mission clock</strong>
            <div class="meta-row">
                <span><?= (int) $clock['spent'] ?> spent</span>
                <span><?= (int) $clock['remaining'] ?> remaining</span>
                <span>of <?= (int) $clock['total'] ?> ticks</span>
            </div>
            <div class="clock-bar<?= $danger ? ' danger' : '' ?>"><span style="width:<?= max(0, min(100, $spentPct)) ?>%"></span></div>
        </div>
    <?php endif; ?>

    <div class="report-grid">
        <section>
            <h3>On-site beats</h3>
            <ul class="beats">
            <?php foreach ($report['beats'] as $b): ?>
                <li>
                    <strong><?= layout_h($b['member']) ?></strong> — <?= layout_h($b['obstacle']) ?>
                    — <?= $b['success'] ? 'PASS' : 'FAIL' ?> (<?= (int) $b['total'] ?> vs DV <?= (int) $b['dv'] ?>)
                    <?php if (isset($b['clock_remaining'])): ?>
                        <span class="muted">· clock <?= (int) $b['clock_remaining'] ?> left</span>
                    <?php endif; ?>
                    <?php if (!empty($b['npc_dialogue'])): ?>
                        <br><em><?= layout_h((string) $b['npc_dialogue']) ?></em><?= !empty($b['npc_cached']) ? ' <span class="muted">(cached)</span>' : '' ?>
                    <?php elseif (!empty($b['npc_error'])): ?>
                        <br><span class="status-bad"><?= layout_h((string) $b['npc_error']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </section>

        <section class="netrun-panel">
            <h3 style="margin-top:0;">Parallel NET run</h3>
            <p>Outcome: <strong><?= layout_h((string) $report['netrun']['outcome']) ?></strong></p>
            <p>Floors cleared: <?= (int) $report['netrun']['floors_cleared'] ?></p>
            <p>Deck HP remaining: <?= (int) $report['netrun']['deck_hp_remaining'] ?></p>
            <p>Architecture: <?= layout_h($selectedJob->archId) ?></p>
            <?php if (!empty($report['netrun']['log']) && is_array($report['netrun']['log'])): ?>
                <details>
                    <summary>Floor log</summary>
                    <ul>
                        <?php foreach ($report['netrun']['log'] as $line): ?>
                            <li><?= layout_h(is_string($line) ? $line : json_encode($line, JSON_UNESCAPED_UNICODE)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($report['agendas_triggered']): ?>
        <h3>Agendas surfaced</h3>
        <ul>
        <?php foreach ($report['agendas_triggered'] as $a): ?>
            <li><strong><?= layout_h($a['member']) ?></strong> — <?= layout_h((string) $a['agenda']) ?> (<?= layout_h(strtoupper((string) $a['type'])) ?>)</li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php layout_footer(); ?>
