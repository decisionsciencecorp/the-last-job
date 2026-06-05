#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Cyberpsychosis balance sweep — rolls chrome loads across seeds and reports EMP/status distribution.
 *
 * Usage: php tools/balance-cyberpsychosis.php [--seeds=500] [--load=heavy|moderate|borg]
 */

require dirname(__DIR__) . '/public/includes/autoload.php';

use LastJob\Dice;
use LastJob\Humanity;
use LastJob\Rng;
use LastJob\Rules;

$opts = getopt('', ['seeds::', 'load::']);
$seedCount = max(1, (int) ($opts['seeds'] ?? 500));
$load = (string) ($opts['load'] ?? 'heavy');

$rules = new Rules();
$loads = [
    'moderate' => ['cw.neural.neurallink', 'cw.neural.interfaceplugs', 'cw.optics.cybereye', 'cw.optics.lowlight'],
    'heavy' => ['cw.neural.neurallink', 'cw.neural.sandevistan', 'cw.limb.cyberarm', 'cw.limb.popupmelee', 'cw.body.subdermal'],
    'borg' => ['cw.neural.neurallink', 'cw.borg.linearframe', 'cw.limb.cyberarm', 'cw.limb.popupmelee'],
];
$wareIds = $loads[$load] ?? $loads['heavy'];

$statusCounts = [];
$empBuckets = ['0-2' => 0, '3-4' => 0, '5-6' => 0, '7+' => 0];
$cyberpsychotic = 0;
$minEmp = 99;
$maxEmp = 0;

for ($s = 1; $s <= $seedCount; $s++) {
    $rng = new Rng($s * 991);
    $empStat = 4 + ($rng->intRange(1, 6)); // typical rolled EMP 5-10
    $humanity = new Humanity(new Dice(new Rng($s * 997)), $empStat);

    foreach ($wareIds as $id) {
        try {
            $humanity->install($rules->cyberwareItem($id));
        } catch (Throwable) {
            // prerequisite ordering may fail on some picks — skip broken step
        }
    }

    $row = $humanity->toArray();
    $status = (string) $row['status'];
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    $emp = (int) $row['emp'];
    $minEmp = min($minEmp, $emp);
    $maxEmp = max($maxEmp, $emp);

    if ($emp <= 2) {
        $empBuckets['0-2']++;
    } elseif ($emp <= 4) {
        $empBuckets['3-4']++;
    } elseif ($emp <= 6) {
        $empBuckets['5-6']++;
    } else {
        $empBuckets['7+']++;
    }

    if ($status === 'cyberpsychotic') {
        $cyberpsychotic++;
    }
}

$pct = fn (int $n): string => number_format(($n / $seedCount) * 100, 1);

echo "Cyberpsychosis balance sweep\n";
echo "Seeds: {$seedCount} · Load: {$load} (" . implode(', ', $wareIds) . ")\n\n";
echo "Status distribution:\n";
foreach ($statusCounts as $status => $count) {
    echo sprintf("  %-20s %5d  (%s%%)\n", $status, $count, $pct($count));
}
echo "\nEMP buckets after install:\n";
foreach ($empBuckets as $bucket => $count) {
    echo sprintf("  EMP %s: %5d  (%s%%)\n", $bucket, $count, $pct($count));
}
echo sprintf("\nCyberpsychotic rate: %s%% (%d / %d)\n", $pct($cyberpsychotic), $cyberpsychotic, $seedCount);
echo sprintf("EMP range observed: %d – %d\n", $minEmp, $maxEmp);
