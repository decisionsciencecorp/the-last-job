<?php
declare(strict_types=1);

namespace LastJob\Netrun;

/**
 * A netrunner's deck state for a single run. The "mind" (intent/dialogue) comes
 * from a Letta agent; this struct is only the mechanical state the engine owns.
 */
final class Netrunner
{
    /**
     * @param string[] $programs program ids loaded in the deck
     */
    public function __construct(
        public string $handle,
        public int $interface,
        public int $deckHp,
        public array $programs,
    ) {
    }

    public function hasProgram(string $id): bool
    {
        return in_array($id, $this->programs, true);
    }

    public static function default(string $handle = 'Glitch'): self
    {
        return new self(
            handle: $handle,
            interface: 6,
            deckHp: 30,
            programs: ['prog.sword', 'prog.armor', 'prog.see_through', 'prog.stealth'],
        );
    }
}
