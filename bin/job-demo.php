<?php
declare(strict_types=1);

/**
 * End-to-end job loop demo: generate a crew, pull a job, run it, print the
 * after-action report. Fully deterministic.
 * Usage: php bin/job-demo.php [--seed=N] [--job=job.arasaka-substation]
 */

require __DIR__ . '/../public/includes/autoload.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Economy;
use LastJob\Flavor;
use LastJob\JobRunner;
use LastJob\Lifepath\CrewBuilder;

$opts = getopt('', ['seed::', 'job::']);
$seed = isset($opts['seed']) ? (int) $opts['seed'] : 2077;
$jobId = $opts['job'] ?? 'job.arasaka-substation';

$rules = new Rules();
$crew = (new CrewBuilder($rules, new Rng($seed)))->build();
$job = $rules->job((string) $jobId);
$economy = new Economy(eddies: 500, streetCred: 4);

$flavor = new Flavor($rules);
$frng = new Rng($seed + 9000);
fwrite(STDOUT, "// NIGHT CITY //\n");
foreach ($flavor->newsTicker($frng, 2) as $line) {
    fwrite(STDOUT, "  >> {$line}\n");
}
fwrite(STDOUT, sprintf("  ~ %s\n", $flavor->ambiance($frng)));
fwrite(STDOUT, sprintf("\nFIXER: \"%s\"\n\n", $flavor->fixerQuote($frng)));

$report = (new JobRunner($rules))->run($crew, $job, new Rng($seed * 7 + 1), $economy);

fwrite(STDOUT, sprintf("=== %s (fixer: %s) ===\n", $report['job_name'], $report['fixer']));
fwrite(STDOUT, sprintf("Crew: %s\n", implode(', ', array_map(static fn ($m) => $m->handle . ' [' . $m->roleName . ']', $crew))));
fwrite(STDOUT, "\n-- On-site beats --\n");
foreach ($report['beats'] as $b) {
    fwrite(STDOUT, sprintf(
        "  %-28s %-10s %s  roll d10=%d%s total %d vs DV %d -> %s  (clock %d left)\n",
        $b['obstacle'], $b['member'], $b['stat'],
        $b['roll'], $b['crit'] !== 'none' ? '(' . $b['crit'] . ')' : '',
        $b['total'], $b['dv'], $b['success'] ? 'PASS' : 'FAIL', $b['clock_remaining']
    ));
}
$n = $report['netrun'];
fwrite(STDOUT, sprintf("\n-- Netrun -- %s, %d floors, %d eddies, deck %d HP, %d rounds\n",
    $n['outcome'], $n['floors_cleared'], $n['eddies'], $n['deck_hp_remaining'], $n['rounds']));

$c = $report['clock'];
fwrite(STDOUT, sprintf("\n-- Clock -- %d/%d ticks spent (%d left)%s\n",
    $c['spent'], $c['total'], $c['remaining'], $report['time_ran_out'] ? '  *** TIME RAN OUT ***' : ''));

fwrite(STDOUT, sprintf("\n-- Aftermath -- %s\n", $report['success'] ? 'JOB SUCCESS' : 'JOB FAILED'));
fwrite(STDOUT, sprintf("  Payout: %d eddies   Street cred: +%d\n", $report['payout_eddies'], $report['street_cred_gained']));
fwrite(STDOUT, sprintf("  Wallet: %d eddies, cred %d (%s)\n",
    $report['economy']['eddies'], $report['economy']['street_cred'], $report['economy']['rep_tier']));

if ($report['agendas_triggered']) {
    fwrite(STDOUT, "\n-- HIDDEN AGENDAS SURFACED --\n");
    foreach ($report['agendas_triggered'] as $a) {
        fwrite(STDOUT, sprintf("  %s [%s] -> %s (%s): %s\n",
            $a['member'], $a['role'], $a['agenda'], strtoupper((string) $a['type']), $a['consequence']));
    }
} else {
    fwrite(STDOUT, "\n(No hidden agendas triggered this run.)\n");
}
fwrite(STDOUT, sprintf("\nFired conditions: %s\n", implode(', ', $report['fired_conditions'])));
