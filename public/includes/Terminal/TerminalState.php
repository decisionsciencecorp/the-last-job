<?php
declare(strict_types=1);

namespace LastJob\Terminal;

use LastJob\Lifepath\CrewBuilder;

final class TerminalState
{
    /** @param array<string,mixed> $data */
    public function __construct(
        private array $data,
    ) {
    }

    public static function fresh(): self
    {
        return new self([
            'seed' => 2077,
            'street_cred' => 4,
            'eddies' => 500,
            'episode_stage' => 'boot',
            'answered' => false,
            'crew_requested' => false,
            'roles' => CrewBuilder::DEFAULT_ROLES,
            'selected_contract' => null,
            'last_report' => null,
            'intake_answers' => [],
            'intake_index' => 0,
            'first_crew_choice' => null,
            'first_contract_state' => 'none',
            'walked_once' => false,
            'first_shard_seen' => false,
            'second_call_seen' => false,
            'history' => [],
        ]);
    }

    /** @param array<string,mixed>|null $raw */
    public static function fromSession(?array $raw): self
    {
        $fresh = self::fresh()->toArray();
        return new self(is_array($raw) ? array_replace($fresh, $raw) : $fresh);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function seed(): int
    {
        return max(1, (int) $this->get('seed', 2077));
    }

    public function streetCred(): int
    {
        return max(0, (int) $this->get('street_cred', 4));
    }

    public function eddies(): int
    {
        return max(0, (int) $this->get('eddies', 500));
    }

    /** @return string[] */
    public function roles(): array
    {
        $roles = $this->get('roles', CrewBuilder::DEFAULT_ROLES);
        return is_array($roles) ? array_values(array_map('strval', $roles)) : CrewBuilder::DEFAULT_ROLES;
    }

    /** @param string[] $lines */
    public function appendHistory(string $command, array $lines): void
    {
        $history = $this->get('history', []);
        $history = is_array($history) ? $history : [];
        $history[] = [
            'command' => $command,
            'lines' => array_values($lines),
            'at' => gmdate('c'),
        ];
        $this->data['history'] = array_slice($history, -20);
    }
}
