<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Dice notation evaluation backed by the deterministic Rng.
 * Supports "NdS", "NdS+K", "NdS-K", flat integers ("5"), and "0".
 */
final class Dice
{
    public function __construct(private Rng $rng)
    {
    }

    /** Roll an expression like "3d6+1" and return the total. */
    public function roll(string $expr): int
    {
        $expr = trim($expr);
        if ($expr === '' || $expr === '0') {
            return 0;
        }

        $modifier = 0;
        if (preg_match('/^(.*?)([+-]\d+)$/', $expr, $m)) {
            $modifier = (int) $m[2];
            $expr = trim($m[1]);
        }

        if (ctype_digit($expr)) {
            return (int) $expr + $modifier;
        }

        if (!preg_match('/^(\d+)d(\d+)$/', $expr, $m)) {
            throw new \InvalidArgumentException("Bad dice expression: {$expr}");
        }

        $count = (int) $m[1];
        $sides = (int) $m[2];
        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $total += $this->rng->die($sides);
        }
        return $total + $modifier;
    }

    /** A single exploding-free d10 (the core check die). */
    public function d10(): int
    {
        return $this->rng->die(10);
    }
}
