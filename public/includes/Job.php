<?php
declare(strict_types=1);

namespace LastJob;

/**
 * A job/contract: the crew-side obstacles, the NET architecture the netrunner
 * has to crack, the payout, and the rep it builds. Loaded from canonical JSON.
 */
final class Job
{
    /**
     * @param array<int,array<string,mixed>> $obstacles
     * @param string[] $tags
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $fixer,
        public int $payoutEddies,
        public int $streetCredReward,
        public int $difficultyTicks,
        public string $archId,
        public array $obstacles,
        public array $tags,
        public int $minRepTier = 0,
        public string $briefing = '',
        public string $stakes = '',
        public string $complication = '',
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            name: (string) ($row['name'] ?? $row['id']),
            fixer: (string) ($row['fixer'] ?? 'Unknown'),
            payoutEddies: (int) ($row['payout_eddies'] ?? 0),
            streetCredReward: (int) ($row['street_cred_reward'] ?? 0),
            difficultyTicks: (int) ($row['difficulty_ticks'] ?? 10),
            archId: (string) ($row['arch'] ?? ''),
            obstacles: $row['obstacles'] ?? [],
            tags: $row['tags'] ?? [],
            minRepTier: (int) ($row['min_street_cred'] ?? 0),
            briefing: (string) ($row['briefing'] ?? ''),
            stakes: (string) ($row['stakes'] ?? ''),
            complication: (string) ($row['complication'] ?? ''),
        );
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    public function isBigPayout(): bool
    {
        return $this->payoutEddies >= 4000;
    }
}
