<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Cyberpunk RED skill-check resolver, deterministic via the seeded Rng.
 *
 * Core check: 1d10 + STAT + SKILL + modifiers vs a Difficulty Value (DV).
 * Critical rules (CP RED):
 *   - Natural 10: "Critical Success" - roll another d10 and ADD it.
 *   - Natural 1:  "Critical Failure" - roll another d10 and SUBTRACT it.
 *   The extra die does not itself crit (single follow-up).
 *
 * Opposed check: both sides roll a full check; higher total wins; ties go to
 * the defender (the second argument).
 */
final class SkillCheck
{
    public function __construct(
        private Dice $dice,
    ) {
    }

    /**
     * @param int $base  STAT + SKILL (already summed by caller)
     * @param int $dv    Difficulty Value to beat (>=)
     * @param int $mod   situational modifier (+/-)
     * @return array{roll:int,crit:string,die_total:int,total:int,dv:int,success:bool,margin:int}
     */
    public function check(int $base, int $dv, int $mod = 0): array
    {
        $roll = $this->dice->d10();
        $crit = 'none';
        $dieTotal = $roll;

        if ($roll === 10) {
            $crit = 'up';
            $dieTotal = 10 + $this->dice->d10();
        } elseif ($roll === 1) {
            $crit = 'down';
            $dieTotal = 1 - $this->dice->d10();
        }

        $total = $dieTotal + $base + $mod;

        return [
            'roll' => $roll,
            'crit' => $crit,
            'die_total' => $dieTotal,
            'total' => $total,
            'dv' => $dv,
            'success' => $total >= $dv,
            'margin' => $total - $dv,
        ];
    }

    /**
     * Opposed check. Ties go to the defender (b).
     *
     * @return array{a:array<string,mixed>,b:array<string,mixed>,winner:string,margin:int}
     */
    public function opposed(int $baseA, int $baseB, int $modA = 0, int $modB = 0): array
    {
        $a = $this->check($baseA, 0, $modA);
        $b = $this->check($baseB, 0, $modB);
        $winner = $a['total'] > $b['total'] ? 'a' : 'b'; // tie -> defender (b)

        return [
            'a' => $a,
            'b' => $b,
            'winner' => $winner,
            'margin' => abs($a['total'] - $b['total']),
        ];
    }
}
