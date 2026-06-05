<?php
declare(strict_types=1);

namespace LastJob;

/**
 * The shared mission clock. Both the crew's on-site actions and the netrunner's
 * descent burn the same ticks. When it runs out, security closes the window —
 * this is the bottleneck that links the two parallel runs in one heist.
 */
final class MissionClock
{
    private int $spent = 0;

    public function __construct(
        private int $total,
    ) {
    }

    public function tick(int $n = 1): void
    {
        $this->spent += max(0, $n);
    }

    public function remaining(): int
    {
        return max(0, $this->total - $this->spent);
    }

    public function total(): int
    {
        return $this->total;
    }

    public function spent(): int
    {
        return $this->spent;
    }

    public function expired(): bool
    {
        return $this->spent >= $this->total;
    }
}
