<?php
declare(strict_types=1);

namespace LastJob\Lifepath;

/**
 * A generated crew member. The hidden agenda is SEALED: it is engine-only state
 * (in the full game it lives in the NPC's Letta agent). Per Athena's hard rule,
 * the player NEVER reads it directly, so the public card omits it. Use
 * toPublicArray() for anything player-facing; toSealedArray() is engine-only.
 */
final class Character
{
    /**
     * @param array<string,int> $stats
     * @param array<string,mixed> $origin
     * @param array<string,mixed> $personality
     * @param array<string,mixed> $family
     * @param array<int,array<string,mixed>> $lifeEvents
     * @param array<string,mixed> $lover
     * @param string[] $contacts
     * @param string[] $enemies
     * @param array<string,mixed> $hiddenAgenda SEALED
     */
    public function __construct(
        public string $handle,
        public string $roleId,
        public string $roleName,
        public string $roleAbility,
        public array $stats,
        public array $origin,
        public array $personality,
        public array $family,
        public array $lifeEvents,
        public array $lover,
        public array $contacts,
        public array $enemies,
        private array $hiddenAgenda,
    ) {
    }

    /** @return array<string,mixed> SEALED hidden agenda - engine only. */
    public function sealedAgenda(): array
    {
        return $this->hiddenAgenda;
    }

    /** @return array<string,mixed> Player-facing card. Never includes the hidden agenda. */
    public function toPublicArray(): array
    {
        return [
            'handle' => $this->handle,
            'role' => $this->roleName,
            'role_ability' => $this->roleAbility,
            'stats' => $this->stats,
            'origin' => $this->origin['result'] ?? null,
            'language' => $this->origin['language'] ?? null,
            'personality' => $this->personality['result'] ?? null,
            'personality_effect' => $this->personality['effect'] ?? null,
            'family' => $this->family['result'] ?? null,
            'family_morale' => $this->family['family_morale'] ?? null,
            'life_events' => array_map(static fn ($e) => $e['result'] ?? null, $this->lifeEvents),
            'lover' => $this->lover['result'] ?? null,
            'public_hook' => $this->publicHook(),
            'contacts' => $this->contacts,
            'enemies' => $this->enemies,
        ];
    }

    /** @return array<string,mixed> Full record incl. sealed agenda - engine/persistence only. */
    public function toSealedArray(): array
    {
        return $this->toPublicArray() + ['_hidden_agenda' => $this->hiddenAgenda];
    }

    private function publicHook(): string
    {
        $event = $this->lifeEvents[0] ?? [];
        $pieces = array_filter([
            (string) ($this->personality['flavor'] ?? ''),
            (string) ($event['flavor'] ?? ''),
            (string) ($this->lover['leverage'] ?? ''),
        ], static fn (string $s): bool => trim($s) !== '');

        if ($pieces === []) {
            return 'They keep the important parts of their story behind their eyes.';
        }

        return implode(' ', array_slice($pieces, 0, 3));
    }
}
