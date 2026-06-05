<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Deterministic pseudo-random number generator (Park-Miller minimal standard
 * LCG). Pure PHP, stays inside 64-bit signed integer range, so a given seed
 * reproduces the exact same stream on any platform/PHP version.
 *
 * The engine uses this for EVERY random decision. Dice + rules decide outcomes;
 * the LLM never rolls. Same seed -> identical run.
 */
final class Rng
{
    private const MODULUS = 2147483647; // 2^31 - 1 (prime)
    private const MULTIPLIER = 16807;

    private int $state;

    public function __construct(int $seed)
    {
        $s = $seed % self::MODULUS;
        if ($s <= 0) {
            $s += self::MODULUS - 1;
        }
        $this->state = $s;
    }

    /** Advance the stream and return the raw state (1..MODULUS-1). */
    public function next(): int
    {
        $this->state = (int) (($this->state * self::MULTIPLIER) % self::MODULUS);
        return $this->state;
    }

    /** Uniform float in [0, 1). */
    public function float(): float
    {
        return ($this->next() - 1) / (float) (self::MODULUS - 1);
    }

    /** Uniform integer in [$min, $max] inclusive. */
    public function intRange(int $min, int $max): int
    {
        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }
        return $min + (int) floor($this->float() * ($max - $min + 1));
    }

    /** Roll a single die with the given number of sides. */
    public function die(int $sides): int
    {
        return $this->intRange(1, $sides);
    }
}
