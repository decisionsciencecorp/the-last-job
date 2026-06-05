<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Humanity / cyberpsychosis tracking (Cyberpunk RED model), deterministic.
 *
 * Humanity ceiling = EMP stat * 10. Installing cyberware rolls a Humanity Loss
 * (dice, e.g. "2d6") and subtracts it. Effective EMP = floor(currentHumanity/10).
 * As chrome stacks, EMP falls:
 *   EMP >= 3  -> stable
 *   EMP 1-2   -> at_risk (needs therapy; cyberpsychosis looming)
 *   EMP <= 0  -> cyberpsychotic (lost to the crew / GM-controlled)
 *
 * This is the tradeoff that makes power expensive: every edge costs a piece of
 * the person. The engine resolves it; the NPC's Letta mind reacts to it.
 */
final class Humanity
{
    private int $maxHumanity;
    private int $currentHumanity;
    /** @var array<int,array<string,mixed>> */
    private array $installed = [];

    public function __construct(
        private Dice $dice,
        int $empStat,
    ) {
        $this->maxHumanity = max(0, $empStat) * 10;
        $this->currentHumanity = $this->maxHumanity;
    }

    public function emp(): int
    {
        return max(0, intdiv($this->currentHumanity, 10));
    }

    public function currentHumanity(): int
    {
        return $this->currentHumanity;
    }

    public function maxHumanity(): int
    {
        return $this->maxHumanity;
    }

    public function status(): string
    {
        $emp = $this->emp();
        if ($emp <= 0) {
            return 'cyberpsychotic';
        }
        if ($emp <= 2) {
            return 'at_risk';
        }
        return 'stable';
    }

    /**
     * Install a piece of cyberware. Requirements (e.g. a Neural Link or a
     * cyberarm for options) must already be installed.
     *
     * @param array<string,mixed> $cw a cyberware rule row
     * @return array<string,mixed> install result
     */
    public function install(array $cw): array
    {
        $requires = $cw['requires'] ?? null;
        if ($requires !== null && !$this->isInstalled((string) $requires)) {
            throw new \RuntimeException(
                sprintf('Cannot install %s: requires %s first.', $cw['id'] ?? '?', $requires)
            );
        }

        $empBefore = $this->emp();
        $humanityBefore = $this->currentHumanity;

        $expr = (string) ($cw['humanity_loss'] ?? '0');
        $loss = $expr === '0' ? 0 : $this->dice->roll($expr);
        $this->currentHumanity = max(0, $this->currentHumanity - $loss);

        $this->installed[] = $cw;

        return [
            'cyberware' => $cw['name'] ?? ($cw['id'] ?? '?'),
            'id' => $cw['id'] ?? null,
            'humanity_loss_expr' => $expr,
            'humanity_loss_rolled' => $loss,
            'humanity_before' => $humanityBefore,
            'humanity_after' => $this->currentHumanity,
            'emp_before' => $empBefore,
            'emp_after' => $this->emp(),
            'status' => $this->status(),
        ];
    }

    public function isInstalled(string $id): bool
    {
        foreach ($this->installed as $cw) {
            if (($cw['id'] ?? null) === $id) {
                return true;
            }
        }
        return false;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'max_humanity' => $this->maxHumanity,
            'current_humanity' => $this->currentHumanity,
            'emp' => $this->emp(),
            'status' => $this->status(),
            'installed' => array_map(static fn ($c) => $c['name'] ?? $c['id'] ?? '?', $this->installed),
        ];
    }
}
