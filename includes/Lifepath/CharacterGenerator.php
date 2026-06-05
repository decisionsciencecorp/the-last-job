<?php
declare(strict_types=1);

namespace LastJob\Lifepath;

use LastJob\Rng;
use LastJob\Rules;

/**
 * Rolls a crew member's lifepath deterministically and assembles a Character.
 * Pure mechanics: every choice comes from the seeded Rng, so a given seed
 * reproduces the same crew. The Letta "mind" is layered on later.
 */
final class CharacterGenerator
{
    private const STAT_KEYS = ['INT', 'REF', 'DEX', 'TECH', 'COOL', 'WILL', 'LUCK', 'MOVE', 'BODY', 'EMP'];
    private const STAT_BASE = 4;
    private const STAT_MIN = 1;
    private const STAT_MAX = 10;

    private const HANDLE_PREFIX = ['Razor', 'Glitch', 'Ghost', 'Static', 'Wire', 'Neon', 'Ash', 'Vex', 'Cinder', 'Echo'];
    private const HANDLE_SUFFIX = ['jack', 'byte', 'fang', 'wolf', 'queen', 'king', 'saint', 'dog', 'eyes', 'hand'];

    public function __construct(
        private Rules $rules,
        private Rng $rng,
    ) {
    }

    public function generate(string $roleId): Character
    {
        $role = $this->rules->role($roleId);

        $stats = array_fill_keys(self::STAT_KEYS, self::STAT_BASE);

        // Role priorities nudge the top stats.
        $priority = $role['stat_priority'] ?? [];
        foreach (array_slice($priority, 0, 2) as $i => $stat) {
            if (isset($stats[$stat])) {
                $stats[$stat] += (2 - $i); // +2 to first, +1 to second
            }
        }

        $contacts = [];
        $enemies = [];

        $origin = $this->rollFrom('cultural_origin');
        $this->applyStatMod($stats, $origin['stat_mod'] ?? []);
        if (!empty($origin['contact'])) {
            $contacts[] = (string) $origin['contact'];
        }

        $personality = $this->rollFrom('personality');
        $this->applyStatMod($stats, $personality['stat_mod'] ?? []);

        $family = $this->rollFrom('family');
        $this->applyStatMod($stats, $family['stat_mod'] ?? []);

        // Key life event rolled 3x (childhood / teen / adult).
        $lifeEvents = [];
        for ($i = 0; $i < 3; $i++) {
            $event = $this->rollFrom('key_life_event');
            $this->applyStatMod($stats, $event['stat_mod'] ?? []);
            if (!empty($event['gain_contact'])) {
                $contacts[] = (string) $event['gain_contact'];
            }
            if (!empty($event['gain_enemy'])) {
                $enemies[] = (string) $event['gain_enemy'];
            }
            $lifeEvents[] = $event;
        }

        $lover = $this->rollFrom('lovers');
        $this->applyStatMod($stats, $lover['stat_mod'] ?? []);

        $this->clampStats($stats);

        $hiddenAgenda = $this->rollAgenda();
        $handle = $this->rollHandle();

        return new Character(
            handle: $handle,
            roleId: (string) $role['id'],
            roleName: (string) $role['name'],
            roleAbility: (string) ($role['ability'] ?? ''),
            stats: $stats,
            origin: $origin,
            personality: $personality,
            family: $family,
            lifeEvents: $lifeEvents,
            lover: $lover,
            contacts: $contacts,
            enemies: $enemies,
            hiddenAgenda: $hiddenAgenda,
        );
    }

    /** @return array<string,mixed> */
    private function rollFrom(string $table): array
    {
        $rows = $this->rules->lifepathTable($table);
        $roll = $this->rng->die(count($rows));
        foreach ($rows as $row) {
            if ((int) ($row['die_roll'] ?? 0) === $roll) {
                return $row;
            }
        }
        return $rows[$roll - 1]; // fallback by index
    }

    /** @return array<string,mixed> */
    private function rollAgenda(): array
    {
        $rows = $this->rules->hiddenAgendas();
        $roll = $this->rng->die(count($rows));
        return $rows[$roll - 1];
    }

    private function rollHandle(): string
    {
        $p = self::HANDLE_PREFIX[$this->rng->die(count(self::HANDLE_PREFIX)) - 1];
        $s = self::HANDLE_SUFFIX[$this->rng->die(count(self::HANDLE_SUFFIX)) - 1];
        return $p . $s;
    }

    /**
     * @param array<string,int> $stats
     * @param array<string,int> $mods
     */
    private function applyStatMod(array &$stats, array $mods): void
    {
        foreach ($mods as $stat => $delta) {
            if (isset($stats[$stat])) {
                $stats[$stat] += (int) $delta;
            }
        }
    }

    /** @param array<string,int> $stats */
    private function clampStats(array &$stats): void
    {
        foreach ($stats as $k => $v) {
            $stats[$k] = max(self::STAT_MIN, min(self::STAT_MAX, $v));
        }
    }
}
