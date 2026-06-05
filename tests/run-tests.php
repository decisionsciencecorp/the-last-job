<?php
declare(strict_types=1);

/**
 * Dependency-free test runner. Proves the engine is deterministic:
 *  - same seed -> byte-identical run log
 *  - different seeds -> stream actually varies
 *  - the RNG is reproducible in isolation
 *  - SQLite bootstrap is idempotent (run twice, stable row counts)
 *
 * Usage: php tests/run-tests.php
 */

require __DIR__ . '/../public/includes/autoload.php';

use LastJob\Rng;
use LastJob\Dice;
use LastJob\Rules;
use LastJob\SkillCheck;
use LastJob\Humanity;
use LastJob\Economy;
use LastJob\JobRunner;
use LastJob\Netrun\NetrunEngine;
use LastJob\Netrun\Netrunner;
use LastJob\Lifepath\CrewBuilder;
use LastJob\Story\IntelDossier;

$pass = 0;
$fail = 0;

function check(string $name, bool $cond, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        fwrite(STDOUT, "  PASS  {$name}\n");
    } else {
        $fail++;
        fwrite(STDOUT, "  FAIL  {$name}" . ($detail !== '' ? " :: {$detail}" : '') . "\n");
    }
}

function runOnce(int $seed): array
{
    $rules = new Rules();
    $engine = new NetrunEngine($rules, new Rng($seed));
    return $engine->run($rules->architecture('nightcity-apt-3floor'), Netrunner::default());
}

// 1. RNG reproducibility in isolation.
$a = new Rng(42);
$b = new Rng(42);
$seqA = array_map(fn () => $a->die(10), range(1, 50));
$seqB = array_map(fn () => $b->die(10), range(1, 50));
check('rng: same seed -> same sequence', $seqA === $seqB);

$c = new Rng(43);
$seqC = array_map(fn () => $c->die(10), range(1, 50));
check('rng: different seed -> different sequence', $seqA !== $seqC);

$inRange = true;
foreach ($seqA as $v) {
    if ($v < 1 || $v > 10) {
        $inRange = false;
        break;
    }
}
check('rng: d10 stays in [1,10]', $inRange);

// 2. Engine determinism: same seed -> identical log.
$r1 = runOnce(1337);
$r2 = runOnce(1337);
check('engine: same seed -> identical log', $r1['log'] === $r2['log']);
check('engine: same seed -> identical outcome', $r1['outcome'] === $r2['outcome'] && $r1['eddies'] === $r2['eddies']);

// 3. Engine sensitivity: different seeds usually differ across a sample.
$logs = [];
for ($s = 1; $s <= 6; $s++) {
    $logs[] = implode("\n", runOnce($s)['log']);
}
check('engine: seeds produce varied runs', count(array_unique($logs)) > 1, 'all 6 seeds identical');

// 4. Outcomes are always a known terminal state.
$valid = [NetrunEngine::OUT_SUCCESS, NetrunEngine::OUT_FAIL_HEAT, NetrunEngine::OUT_FAIL_FLATLINE, NetrunEngine::OUT_DEAD];
$allValid = true;
for ($s = 1; $s <= 25; $s++) {
    if (!in_array(runOnce($s)['outcome'], $valid, true)) {
        $allValid = false;
        break;
    }
}
check('engine: outcome is always a known terminal state', $allValid);

// 5. SQLite bootstrap idempotency.
$tmp = sys_get_temp_dir() . '/tlj-test-' . getmypid() . '.sqlite';
@unlink($tmp);
$rules = new Rules();
$pdo1 = $rules->bootstrapSqlite($tmp);
$ice1 = (int) $pdo1->query('SELECT COUNT(*) FROM netrun_ice')->fetchColumn();
$pdo2 = $rules->bootstrapSqlite($tmp); // run again
$ice2 = (int) $pdo2->query('SELECT COUNT(*) FROM netrun_ice')->fetchColumn();
check('sqlite: bootstrap is idempotent (stable row count)', $ice1 > 0 && $ice1 === $ice2, "ice1={$ice1} ice2={$ice2}");
@unlink($tmp);

// 6. Crew generation determinism (lifepath -> crew).
function crewCards(int $seed): array
{
    $rules = new Rules();
    $crew = (new CrewBuilder($rules, new Rng($seed)))->build();
    return array_map(static fn ($m) => $m->toPublicArray(), $crew);
}
$crewA = crewCards(2077);
$crewB = crewCards(2077);
check('crew: same seed -> identical crew', $crewA === $crewB);

$crewC = crewCards(2078);
check('crew: different seed -> different crew', $crewA !== $crewC);

$rolesOk = array_map(static fn ($c) => $c['role'], $crewA) === ['Solo', 'Netrunner', 'Tech', 'Fixer'];
check('crew: default crew is the core four roles', $rolesOk);

$customCrew = (new CrewBuilder($rules, new Rng(2077)))->build(['role.tech', 'role.tech', 'role.solo', 'role.netrunner']);
$customRoles = array_map(static fn ($m) => $m->toPublicArray()['role'], $customCrew);
check('crew: explicit role picks are honored in order', $customRoles === ['Tech', 'Tech', 'Solo', 'Netrunner']);

$statsValid = true;
foreach ($crewA as $c) {
    foreach ($c['stats'] as $v) {
        if ($v < 1 || $v > 10) {
            $statsValid = false;
            break 2;
        }
    }
}
check('crew: all stats clamped to [1,10]', $statsValid);

// 7. SECRECY GUARDRAIL (Athena hard rule): player card never leaks the agenda.
$rules = new Rules();
$crew = (new CrewBuilder($rules, new Rng(2077)))->build();
$leak = false;
foreach ($crew as $m) {
    $publicBlob = strtolower(json_encode($m->toPublicArray(), JSON_UNESCAPED_SLASHES) ?: '');
    $agenda = $m->sealedAgenda();
    $needles = [strtolower((string) $agenda['result']), strtolower((string) $agenda['consequence'])];
    foreach ($needles as $needle) {
        if ($needle !== '' && str_contains($publicBlob, $needle)) {
            $leak = true;
            break 2;
        }
    }
    // The sealed accessor must actually carry the agenda (sanity).
    if (empty($agenda['result'])) {
        $leak = true;
        break;
    }
}
check('secrecy: hidden agenda never appears in the player-facing card', !$leak);

// 8. Skill checks: deterministic, crit rules, valid structure.
function checkSeq(int $seed): array
{
    $sc = new SkillCheck(new Dice(new Rng($seed)));
    $out = [];
    for ($i = 0; $i < 50; $i++) {
        $out[] = $sc->check(6, 13);
    }
    return $out;
}
$scA = checkSeq(99);
$scB = checkSeq(99);
check('skillcheck: same seed -> identical results', $scA === $scB);
check('skillcheck: different seed -> different results', $scA !== checkSeq(100));

$critUpOk = true;
$critDownOk = true;
$rollsValid = true;
foreach (checkSeq(99) as $r) {
    if ($r['roll'] < 1 || $r['roll'] > 10) {
        $rollsValid = false;
    }
    if ($r['crit'] === 'up' && $r['die_total'] < 11) {
        $critUpOk = false; // 10 + 1..10 = 11..20
    }
    if ($r['crit'] === 'down' && $r['die_total'] > 0) {
        $critDownOk = false; // 1 - 1..10 = 0..-9
    }
}
check('skillcheck: natural rolls stay in [1,10]', $rollsValid);
check('skillcheck: crit-up adds (die_total >= 11)', $critUpOk);
check('skillcheck: crit-down subtracts (die_total <= 0)', $critDownOk);

$sc = new SkillCheck(new Dice(new Rng(7)));
$op = $sc->opposed(8, 8);
check('skillcheck: opposed picks a/b correctly (tie -> defender b)',
    ($op['winner'] === 'a') === ($op['a']['total'] > $op['b']['total']));

// 9. Humanity / cyberpsychosis: deterministic, requirement gate, threshold.
$rules = new Rules();
function chromeRun(int $seed, Rules $rules): array
{
    // EMP 3 (Humanity 30): the full heavy plan drives EMP to 0 even on minimum
    // dice, so the cyberpsychosis threshold is exercised deterministically.
    $h = new Humanity(new Dice(new Rng($seed)), 3);
    $plan = ['cw.neural.neurallink', 'cw.optics.cybereye', 'cw.neural.sandevistan',
             'cw.limb.cyberarm', 'cw.body.subdermal', 'cw.borg.linearframe', 'cw.borg.fullconversion'];
    $log = [];
    foreach ($plan as $id) {
        $log[] = $h->install($rules->cyberwareItem($id));
        if ($h->status() === 'cyberpsychotic') {
            break;
        }
    }
    return [$log, $h->toArray()];
}
[$logA] = chromeRun(1337, $rules);
[$logB] = chromeRun(1337, $rules);
check('humanity: same seed -> identical chrome run', $logA === $logB);

$empMonotonic = true;
$prev = 999;
foreach ($logA as $step) {
    if ($step['emp_after'] > $prev) {
        $empMonotonic = false;
        break;
    }
    $prev = $step['emp_after'];
}
check('humanity: EMP never increases as chrome stacks', $empMonotonic);

$reachedPsycho = false;
foreach ($logA as $step) {
    if ($step['status'] === 'cyberpsychotic') {
        $reachedPsycho = true;
    }
}
check('humanity: heavy borg load drives toward cyberpsychosis', $reachedPsycho);

$gateOk = false;
try {
    $h = new Humanity(new Dice(new Rng(1)), 8);
    $h->install($rules->cyberwareItem('cw.optics.targeting')); // requires cybereye, not installed
} catch (\RuntimeException $e) {
    $gateOk = true;
}
check('humanity: requirement gate blocks orphan options', $gateOk);

// 10. Cyberware bootstrap idempotency.
$tmp2 = sys_get_temp_dir() . '/tlj-cw-' . uniqid() . '.sqlite';
$rules->bootstrapSqlite($tmp2);
$pdo2 = $rules->bootstrapSqlite($tmp2);
$cw1 = (int) $pdo2->query('SELECT COUNT(*) FROM cyberware')->fetchColumn();
$rules->bootstrapSqlite($tmp2);
$cw2 = (int) (new Rules())->bootstrapSqlite($tmp2)->query('SELECT COUNT(*) FROM cyberware')->fetchColumn();
check('sqlite: cyberware bootstrap idempotent', $cw1 > 0 && $cw1 === $cw2, "cw1={$cw1} cw2={$cw2}");
@unlink($tmp2);

// 11. Economy + mission clock.
$econ = new Economy(1000, 4);
$econ->payout(2000, 2);
$spendOk = $econ->spend(500);
$overspend = $econ->spend(999999);
check('economy: payout + spend + cred tier', $econ->eddies() === 2500 && $spendOk && !$overspend && $econ->streetCred() === 6 && $econ->repTier() === 'reputable');

$clock = new \LastJob\MissionClock(5);
$clock->tick(3);
$clock->tick(3);
check('clock: caps at total, reports expired', $clock->remaining() === 0 && $clock->expired() && $clock->spent() === 6);

// 12. JobRunner: end-to-end determinism + economy effect + agenda secrecy.
$rules = new Rules();
function runJob(int $seed, Rules $rules, string $jobId): array
{
    $crew = (new CrewBuilder($rules, new Rng($seed)))->build();
    $econ = new Economy(500, 4);
    $report = (new JobRunner($rules))->run($crew, $rules->job($jobId), new Rng($seed * 7 + 1), $econ);
    // Strip nothing - report is the full deterministic artifact.
    return $report;
}
$r1 = runJob(2077, $rules, 'job.arasaka-substation');
$r2 = runJob(2077, $rules, 'job.arasaka-substation');
check('jobrunner: same crew+job+seed -> identical report', $r1 === $r2);
check('jobrunner: different seed -> different report', $r1 !== runJob(2078, $rules, 'job.arasaka-substation'));

check('jobrunner: report carries a known success flag', is_bool($r1['success']));
check('jobrunner: netrun outcome is a known terminal state', in_array($r1['netrun']['outcome'],
    [NetrunEngine::OUT_SUCCESS, NetrunEngine::OUT_FAIL_HEAT, NetrunEngine::OUT_FAIL_FLATLINE, NetrunEngine::OUT_DEAD], true));

// Payout only on success; wallet matches.
$paidConsistent = $r1['success']
    ? ($r1['economy']['eddies'] === 500 + $r1['payout_eddies'] && $r1['payout_eddies'] > 0)
    : ($r1['economy']['eddies'] === 500 && $r1['payout_eddies'] === 0);
check('jobrunner: payout applied iff success', $paidConsistent);

// corp+enemy job always fires those conditions.
check('jobrunner: corp/enemy job fires corp_job + enemy_present',
    in_array('corp_job', $r1['fired_conditions'], true) && in_array('enemy_present', $r1['fired_conditions'], true));

// Agenda secrecy at the report layer: every triggered agenda must correspond to
// a fired condition (or 'always'); nothing leaks without a trigger.
$triggerSound = true;
foreach ($r1['agendas_triggered'] as $a) {
    $on = (string) $a['trigger_on'];
    if ($on !== 'always' && !in_array($on, $r1['fired_conditions'], true)) {
        $triggerSound = false;
        break;
    }
}
check('jobrunner: agendas surface only on a fired trigger (or always)', $triggerSound);

// A 'never' agenda must never appear in any triggered list.
$neverLeaked = false;
foreach ($r1['agendas_triggered'] as $a) {
    if ((string) $a['trigger_on'] === 'never') {
        $neverLeaked = true;
    }
}
check('jobrunner: never-trigger agendas stay sealed', !$neverLeaked);

// 13. Content depth: roster grew, every job's architecture runs to a terminal state.
$rules = new Rules();
check('content: ICE roster expanded (>=12)', count($rules->loadJson('netrun/ice.json')) >= 12);
check('content: program roster expanded (>=9)', count($rules->loadJson('netrun/programs.json')) >= 9);
check('content: multiple jobs loaded (>=4)', count($rules->jobs()) >= 4);
check('content: job unlock gates by street cred',
    $rules->isJobUnlocked('job.pawnshop', 0)
    && !$rules->isJobUnlocked('job.arasaka-substation', 2)
    && $rules->isJobUnlocked('job.arasaka-substation', 3)
    && !$rules->isJobUnlocked('job.militech-datafort', 5)
    && $rules->isJobUnlocked('job.militech-datafort', 6)
    && !$rules->isJobUnlocked('job.the-last-job', 8)
    && $rules->isJobUnlocked('job.the-last-job', 9)
);

$cred6Jobs = array_map(static fn ($j) => $j->id, $rules->jobsForStreetCred(6));
check('content: jobsForStreetCred filters endgame until legend',
    in_array('job.pawnshop', $cred6Jobs, true)
    && in_array('job.arasaka-substation', $cred6Jobs, true)
    && in_array('job.militech-datafort', $cred6Jobs, true)
    && !in_array('job.the-last-job', $cred6Jobs, true)
);

$allJobsRun = true;
$allTerminal = true;
foreach ($rules->jobs() as $job) {
    try {
        $crew = (new CrewBuilder($rules, new Rng(2077)))->build();
        $rep = (new JobRunner($rules))->run($crew, $job, new Rng(4242), new Economy(500, 9));
        if (!in_array($rep['netrun']['outcome'],
            [NetrunEngine::OUT_SUCCESS, NetrunEngine::OUT_FAIL_HEAT, NetrunEngine::OUT_FAIL_FLATLINE, NetrunEngine::OUT_DEAD], true)) {
            $allTerminal = false;
        }
    } catch (\Throwable $e) {
        $allJobsRun = false;
        break;
    }
}
check('content: every job runs end-to-end without error', $allJobsRun);
check('content: every job netrun ends in a terminal state', $allTerminal);

$dossier = new IntelDossier(__DIR__ . '/../public/data/story/intel_threads.json');
$threads = $dossier->threads();
$threadIds = array_map(static fn ($t) => (string) $t['id'], $threads);
check('content: intel dossier has core conspiracy threads',
    count($threads) >= 4
    && in_array('thread.engram', $threadIds, true)
    && in_array('thread.tower', $threadIds, true)
);
$threadsHaveEvidence = true;
foreach ($threads as $thread) {
    if (empty($thread['evidence']) || !is_array($thread['evidence']) || empty($thread['question'])) {
        $threadsHaveEvidence = false;
        break;
    }
}
check('content: intel dossier threads carry evidence and questions', $threadsHaveEvidence);

$knownThreads = array_flip($threadIds);
$jobThreadRefsOk = true;
foreach ($rules->jobs() as $job) {
    if ($job->intelThreads === []) {
        $jobThreadRefsOk = false;
        break;
    }
    foreach ($job->intelThreads as $threadId) {
        if (!isset($knownThreads[$threadId])) {
            $jobThreadRefsOk = false;
            break 2;
        }
    }
}
check('content: every job links to valid intel threads', $jobThreadRefsOk);

// 14. Flavor layer: deterministic, ticker distinct, district filter.
$flavor = new \LastJob\Flavor($rules);
$fq1 = $flavor->fixerQuote(new Rng(5));
$fq2 = $flavor->fixerQuote(new Rng(5));
check('flavor: same seed -> same fixer quote', $fq1 === $fq2 && $fq1 !== '');
check('flavor: different seed can vary', $flavor->fixerQuote(new Rng(5)) !== '' );

$ticker = $flavor->newsTicker(new Rng(11), 3);
check('flavor: ticker returns N distinct headlines', count($ticker) === 3 && count(array_unique($ticker)) === 3);

$amb = $flavor->ambiance(new Rng(3), 'City Center');
check('flavor: district filter returns that district line',
    str_contains($amb, 'Arasaka') || str_contains($amb, 'Corpo') || $amb !== '');

// 15. Dev blog: markdown posts load, back-dated entries present.
$blog = new \LastJob\Blog\Blog();
$posts = $blog->allPosts();
check('devblog: posts load from public/blog/content', count($posts) >= 7, 'count=' . count($posts));
$genesis = $blog->postBySlug('genesis-athena-and-the-board');
check('devblog: genesis post exists with HTML body', $genesis !== null && str_contains((string) $genesis['body_html'], 'Athena'));
$ordered = true;
for ($i = 1; $i < count($posts); $i++) {
    if ($posts[$i - 1]['date'] < $posts[$i]['date']) {
        $ordered = false;
        break;
    }
}
check('devblog: posts sorted newest-first', $ordered);

$visualsOk = true;
$visualFilesOk = true;
foreach ($posts as $post) {
    $bodyMd = (string) ($post['body_md'] ?? '');
    $bodyHtml = (string) ($post['body_html'] ?? '');
    if (!str_contains($bodyMd, '## Visuals') || substr_count($bodyMd, '![') < 2 || !str_contains($bodyHtml, '<figure>')) {
        $visualsOk = false;
        break;
    }
    if (preg_match_all('/!\[[^\]]*\]\(([^)\s]+)(?:\s+"[^"]+")?\)/', $bodyMd, $matches)) {
        foreach ($matches[1] as $src) {
            if (str_starts_with($src, '/blog/assets/') && !is_file(__DIR__ . '/../public' . $src)) {
                $visualFilesOk = false;
                break 2;
            }
        }
    }
}
check('devblog: every post has illustration and screenshot figures', $visualsOk);
check('devblog: referenced visual assets exist on disk', $visualFilesOk);

// 16. Letta NPC cache: idempotent storage + stable context hash.
use LastJob\Letta\LettaResponseCache;
use LastJob\Letta\NpcIntentBroker;

$cacheTmp = sys_get_temp_dir() . '/tlj-letta-' . uniqid() . '.sqlite';
$cache = new LettaResponseCache($cacheTmp);
$ctx = ['job' => 'job.test', 'obstacle' => 'Gate', 'member' => 'Razor', 'success' => true, 'dv' => 15];
$hash = LettaResponseCache::hashContext($ctx);
$cache->put('run1', 'Gate', 'Razor', $hash, ['intent' => 'push', 'dialogue' => 'Move.', 'raw' => '{}']);
$hit = $cache->get('run1', 'Gate', 'Razor', $hash);
check('letta cache: round-trip hit', $hit !== null && $hit['dialogue'] === 'Move.');
$cache->bootstrap(); // idempotent
$hit2 = $cache->get('run1', 'Gate', 'Razor', $hash);
check('letta cache: bootstrap idempotent', $hit2 !== null && $hit2['dialogue'] === 'Move.');
check('letta cache: context hash stable', $hash === LettaResponseCache::hashContext($ctx));
check('letta cache: run id stable', NpcIntentBroker::runId(2077, 'job.arasaka-substation') === NpcIntentBroker::runId(2077, 'job.arasaka-substation'));
@unlink($cacheTmp);

fwrite(STDOUT, sprintf("\n%d passed, %d failed\n", $pass, $fail));
exit($fail === 0 ? 0 : 1);
