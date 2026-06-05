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

require __DIR__ . '/../includes/autoload.php';

use LastJob\Rng;
use LastJob\Dice;
use LastJob\Rules;
use LastJob\SkillCheck;
use LastJob\Humanity;
use LastJob\Netrun\NetrunEngine;
use LastJob\Netrun\Netrunner;
use LastJob\Lifepath\CrewBuilder;

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

fwrite(STDOUT, sprintf("\n%d passed, %d failed\n", $pass, $fail));
exit($fail === 0 ? 0 : 1);
