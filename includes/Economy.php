<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Crew wallet + reputation. Eddies (the take) and Street Cred (which gates job
 * offers, backup, and eventually The Last Job). Deterministic, no randomness.
 */
final class Economy
{
    public function __construct(
        private int $eddies = 0,
        private int $streetCred = 0,
    ) {
    }

    public function eddies(): int
    {
        return $this->eddies;
    }

    public function streetCred(): int
    {
        return $this->streetCred;
    }

    public function payout(int $eddies, int $cred = 0): void
    {
        $this->eddies += max(0, $eddies);
        $this->streetCred += max(0, $cred);
    }

    /** @return bool true if the spend succeeded (enough eddies). */
    public function spend(int $eddies): bool
    {
        if ($eddies < 0 || $eddies > $this->eddies) {
            return false;
        }
        $this->eddies -= $eddies;
        return true;
    }

    /**
     * Reputation tiers gate what jobs a fixer will even offer.
     * 0-2 nobody, 3-5 known, 6-8 reputable, 9+ legend (eligible for The Last Job).
     */
    public function repTier(): string
    {
        return match (true) {
            $this->streetCred >= 9 => 'legend',
            $this->streetCred >= 6 => 'reputable',
            $this->streetCred >= 3 => 'known',
            default => 'nobody',
        };
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'eddies' => $this->eddies,
            'street_cred' => $this->streetCred,
            'rep_tier' => $this->repTier(),
        ];
    }
}
