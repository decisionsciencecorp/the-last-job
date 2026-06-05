<?php
declare(strict_types=1);

/**
 * Deterministic crew generation demo (lifepath -> crew).
 * Usage: php bin/crew-demo.php [--seed=N] [--sealed]
 *
 * --sealed reveals hidden agendas (engine/debug view). Without it you see only
 * the player-facing card, exactly as the game would show it.
 */

require __DIR__ . '/../public/includes/autoload.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Lifepath\CrewBuilder;

$opts = getopt('', ['seed::', 'sealed']);
$seed = isset($opts['seed']) ? (int) $opts['seed'] : 2077;
$sealed = isset($opts['sealed']);

$rules = new Rules();
$crew = (new CrewBuilder($rules, new Rng($seed)))->build();

fwrite(STDOUT, sprintf("=== CREW seed=%d%s ===\n", $seed, $sealed ? ' (SEALED/debug)' : ''));
foreach ($crew as $m) {
    $card = $m->toPublicArray();
    fwrite(STDOUT, sprintf("\n%s  [%s] - %s\n", $card['handle'], $card['role'], $card['role_ability']));
    fwrite(STDOUT, '  STATS  ' . implode('  ', array_map(
        static fn ($k, $v) => "{$k}{$v}",
        array_keys($card['stats']),
        array_values($card['stats'])
    )) . "\n");
    fwrite(STDOUT, sprintf("  ORIGIN %s (%s)\n", $card['origin'], $card['language']));
    fwrite(STDOUT, sprintf("  PERSON %s - %s\n", $card['personality'], $card['personality_effect']));
    fwrite(STDOUT, sprintf("  FAMILY %s (morale %s)\n", $card['family'], (string) $card['family_morale']));
    fwrite(STDOUT, sprintf("  EVENTS %s\n", implode(' / ', $card['life_events'])));
    fwrite(STDOUT, sprintf("  LOVER  %s\n", $card['lover']));
    if ($card['contacts']) {
        fwrite(STDOUT, '  CONTACTS ' . implode('; ', $card['contacts']) . "\n");
    }
    if ($card['enemies']) {
        fwrite(STDOUT, '  ENEMIES  ' . implode('; ', $card['enemies']) . "\n");
    }
    if ($sealed) {
        $a = $m->sealedAgenda();
        fwrite(STDOUT, sprintf("  [SEALED AGENDA] %s (%s): %s\n", $a['result'], $a['type'], $a['consequence']));
    }
}
fwrite(STDOUT, "\n(Hidden agendas are sealed; run with --sealed to debug.)\n");
