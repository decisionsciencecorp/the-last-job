<?php
declare(strict_types=1);

/**
 * Humanity / cyberpsychosis demo: install chrome and watch EMP bleed.
 * Usage: php bin/chrome-demo.php [--seed=N] [--emp=8]
 */

require __DIR__ . '/../includes/autoload.php';

use LastJob\Rng;
use LastJob\Dice;
use LastJob\Rules;
use LastJob\Humanity;

$opts = getopt('', ['seed::', 'emp::']);
$seed = isset($opts['seed']) ? (int) $opts['seed'] : 1337;
$emp = isset($opts['emp']) ? (int) $opts['emp'] : 8;

$rules = new Rules();
$dice = new Dice(new Rng($seed));
$h = new Humanity($dice, $emp);

// A solo chasing power: backbone first, then escalating chrome.
$plan = [
    'cw.neural.neurallink',
    'cw.optics.cybereye',
    'cw.optics.targeting',
    'cw.neural.sandevistan',
    'cw.limb.cyberarm',
    'cw.limb.popupmelee',
    'cw.body.subdermal',
    'cw.borg.linearframe',
];

fwrite(STDOUT, sprintf("=== CHROME RUN seed=%d  EMP %d (Humanity %d) ===\n", $seed, $emp, $h->maxHumanity()));
foreach ($plan as $id) {
    $r = $h->install($rules->cyberwareItem($id));
    fwrite(STDOUT, sprintf(
        "%-26s -HL %2d (%s)  Humanity %3d->%3d  EMP %d->%d  [%s]\n",
        $r['cyberware'],
        $r['humanity_loss_rolled'],
        $r['humanity_loss_expr'],
        $r['humanity_before'],
        $r['humanity_after'],
        $r['emp_before'],
        $r['emp_after'],
        strtoupper($r['status'])
    ));
    if ($r['status'] === 'cyberpsychotic') {
        fwrite(STDOUT, "  >> CYBERPSYCHOSIS. Lost to the chrome. The crew has a problem.\n");
        break;
    }
}
fwrite(STDOUT, "\nFinal: " . json_encode($h->toArray(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n");
