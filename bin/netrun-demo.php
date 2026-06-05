<?php
declare(strict_types=1);

/**
 * Deterministic netrun demo.
 * Usage: php bin/netrun-demo.php [--seed=N] [--arch=name] [--handle=Name]
 */

require __DIR__ . '/../includes/autoload.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Netrun\NetrunEngine;
use LastJob\Netrun\Netrunner;

$opts = getopt('', ['seed::', 'arch::', 'handle::']);
$seed = isset($opts['seed']) ? (int) $opts['seed'] : 1337;
$archName = $opts['arch'] ?? 'nightcity-apt-3floor';
$handle = $opts['handle'] ?? 'Glitch';

$rules = new Rules();
$rng = new Rng($seed);
$engine = new NetrunEngine($rules, $rng);
$arch = $rules->architecture($archName);
$runner = Netrunner::default($handle);

$result = $engine->run($arch, $runner);

fwrite(STDOUT, sprintf("=== NETRUN seed=%d arch=%s ===\n", $seed, $archName));
foreach ($result['log'] as $line) {
    fwrite(STDOUT, $line . "\n");
}
fwrite(STDOUT, sprintf(
    "\nRESULT: %s | eddies=%d | heat=%d | rounds=%d | floors=%d | deck=%d HP\n",
    $result['outcome'], $result['eddies'], $result['heat'],
    $result['rounds'], $result['floors_cleared'], $result['deck_hp_remaining']
));
