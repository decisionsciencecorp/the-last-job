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
use LastJob\Rules;
use LastJob\Netrun\NetrunEngine;
use LastJob\Netrun\Netrunner;

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

fwrite(STDOUT, sprintf("\n%d passed, %d failed\n", $pass, $fail));
exit($fail === 0 ? 0 : 1);
