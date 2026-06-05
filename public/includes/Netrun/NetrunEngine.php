<?php
declare(strict_types=1);

namespace LastJob\Netrun;

use LastJob\Dice;
use LastJob\Rng;
use LastJob\Rules;

/**
 * Deterministic netrunner-vs-NET simulation. Given a seed, a NET architecture,
 * and a netrunner loadout, it produces an identical run log every time.
 *
 * Outcomes are 100% rules-driven here. In the full game the netrunner's *intent*
 * (which program to lead with, whether to push deeper) can be supplied by a
 * Letta agent, but the resolution below is what decides what actually happens.
 */
final class NetrunEngine
{
    private Dice $dice;

    public const OUT_SUCCESS = 'SUCCESS';
    public const OUT_FAIL_HEAT = 'FAIL_HEAT';
    public const OUT_FAIL_FLATLINE = 'FAIL_FLATLINE';
    public const OUT_DEAD = 'DEAD';

    public function __construct(
        private Rules $rules,
        private Rng $rng,
    ) {
        $this->dice = new Dice($rng);
    }

    /**
     * @param array<string,mixed> $arch architecture definition
     * @return array<string,mixed> structured run result with a 'log' array
     */
    public function run(array $arch, Netrunner $runner): array
    {
        $log = [];
        $heat = 0;
        $heatLimit = (int) ($arch['security_clock'] ?? 12);
        $rounds = 0;
        $floorsCleared = 0;
        $eddies = 0;
        $outcome = self::OUT_FAIL_HEAT;

        $hasArmor = $runner->hasProgram('prog.armor');
        $armorReduce = $hasArmor ? (int) ($this->rules->program('prog.armor')['reduce'] ?? 0) : 0;
        $hasSeeThrough = $runner->hasProgram('prog.see_through');
        $seeBonus = $hasSeeThrough ? (int) ($this->rules->program('prog.see_through')['bonus'] ?? 0) : 0;
        $attackProgId = $runner->hasProgram('prog.sword') ? 'prog.sword'
            : ($runner->hasProgram('prog.virus') ? 'prog.virus' : null);

        $log[] = sprintf(
            'JACK IN: %s [INT %d, deck %d HP] vs "%s" (heat limit %d).',
            $runner->handle, $runner->interface, $runner->deckHp, (string) $arch['name'], $heatLimit
        );
        if ($attackProgId === null) {
            $log[] = 'ABORT: no attack program loaded.';
            return $this->result(self::OUT_FAIL_FLATLINE, $log, $heat, $rounds, $floorsCleared, $eddies, $runner);
        }
        $attackProg = $this->rules->program($attackProgId);

        foreach ($arch['floors'] as $floor) {
            $idx = (int) $floor['index'];
            $heat += 1; // descending/traversal costs time
            $log[] = sprintf('--- Floor %d --- (heat %d/%d)', $idx, $heat, $heatLimit);
            if ($hasSeeThrough) {
                $log[] = sprintf('  See-Through maps the floor (+%d first-strike).', $seeBonus);
            }

            $firstStrike = true;
            foreach (($floor['ice'] ?? []) as $iceId) {
                $ice = $this->rules->ice($iceId);

                // Optional stealth bypass attempt (non-lethal handling skips the fight).
                if ($runner->hasProgram('prog.stealth')) {
                    $stealthProg = $this->rules->program('prog.stealth');
                    $sRoll = $this->dice->d10() + $runner->interface + (int) $stealthProg['bonus'];
                    $iRoll = $this->dice->d10() + (int) $ice['per'];
                    if ($sRoll > $iRoll) {
                        $log[] = sprintf(
                            '  Stealth past %s (%d vs %d) - no engagement.',
                            (string) $ice['name'], $sRoll, $iRoll
                        );
                        continue;
                    }
                    $log[] = sprintf(
                        '  Stealth on %s failed (%d vs %d) - it sees you.',
                        (string) $ice['name'], $sRoll, $iRoll
                    );
                }

                $iceHp = (int) $ice['def'];
                $log[] = sprintf('  Engage %s [HP %d, atk %d, dmg %s]%s.',
                    (string) $ice['name'], $iceHp, (int) $ice['atk'], (string) $ice['dmg'],
                    !empty($ice['lethal']) ? ' [BLACK ICE]' : '');

                while ($iceHp > 0) {
                    $rounds += 1;
                    $heat += 1;

                    // Netrunner action.
                    $atkRoll = $this->dice->d10() + $runner->interface + (int) $attackProg['bonus'];
                    if ($firstStrike) {
                        $atkRoll += $seeBonus;
                        $firstStrike = false;
                    }
                    $toHit = (int) $ice['atk'];
                    if ($atkRoll >= $toHit) {
                        $dmg = $this->dice->roll((string) $attackProg['dmg']);
                        $iceHp -= $dmg;
                        $log[] = sprintf('    %s hits (%d>=%d) for %d. ICE HP %d.',
                            (string) $attackProg['name'], $atkRoll, $toHit, $dmg, max(0, $iceHp));
                    } else {
                        $log[] = sprintf('    %s glances off (%d<%d).',
                            (string) $attackProg['name'], $atkRoll, $toHit);
                    }

                    // ICE counterattack if still alive.
                    if ($iceHp > 0) {
                        if (!empty($ice['alarm'])) {
                            $heat += 2;
                            $log[] = sprintf('    %s howls - alarm climbs (heat %d/%d).',
                                (string) $ice['name'], $heat, $heatLimit);
                        }
                        $raw = $this->dice->roll((string) $ice['dmg']);
                        $net = max(0, $raw - $armorReduce);
                        $runner->deckHp -= $net;
                        $log[] = sprintf('    %s burns back %d%s. Deck %d HP.',
                            (string) $ice['name'], $net,
                            $armorReduce > 0 ? sprintf(' (armor -%d of %d)', min($armorReduce, $raw), $raw) : '',
                            max(0, $runner->deckHp));

                        if ($runner->deckHp <= 0) {
                            if (!empty($ice['lethal'])) {
                                $log[] = sprintf('    FLATLINE to %s - brain burned. %s is dead.',
                                    (string) $ice['name'], $runner->handle);
                                return $this->result(self::OUT_DEAD, $log, $heat, $rounds, $floorsCleared, $eddies, $runner);
                            }
                            $log[] = sprintf('    Deck fried - %s ejected, alive.', $runner->handle);
                            return $this->result(self::OUT_FAIL_FLATLINE, $log, $heat, $rounds, $floorsCleared, $eddies, $runner);
                        }
                    }

                    if ($heat >= $heatLimit) {
                        $log[] = sprintf('    Security on site (heat %d/%d) - mission blown.', $heat, $heatLimit);
                        return $this->result(self::OUT_FAIL_HEAT, $log, $heat, $rounds, $floorsCleared, $eddies, $runner);
                    }
                }

                $log[] = sprintf('  %s de-rezzed.', (string) $ice['name']);
            }

            $floorsCleared += 1;

            if (!empty($floor['datavault'])) {
                $vault = $floor['datavault'];
                $eddies += (int) ($vault['value_eddies'] ?? 0);
                $log[] = sprintf('  VAULT: %s extracted (+%d eddies).',
                    (string) $vault['name'], (int) ($vault['value_eddies'] ?? 0));
                $outcome = self::OUT_SUCCESS;
            }
        }

        if ($outcome !== self::OUT_SUCCESS) {
            // Cleared everything but no vault: treat as a dry run.
            $log[] = 'JACK OUT: no vault on the stack - dry run.';
        } else {
            $log[] = sprintf('JACK OUT: clean. %s walks with %d eddies.', $runner->handle, $eddies);
        }

        return $this->result($outcome, $log, $heat, $rounds, $floorsCleared, $eddies, $runner);
    }

    /**
     * @param string[] $log
     * @return array<string,mixed>
     */
    private function result(
        string $outcome,
        array $log,
        int $heat,
        int $rounds,
        int $floorsCleared,
        int $eddies,
        Netrunner $runner
    ): array {
        return [
            'outcome' => $outcome,
            'eddies' => $eddies,
            'heat' => $heat,
            'rounds' => $rounds,
            'floors_cleared' => $floorsCleared,
            'deck_hp_remaining' => max(0, $runner->deckHp),
            'log' => $log,
        ];
    }
}
