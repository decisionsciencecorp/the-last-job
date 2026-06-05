<?php
declare(strict_types=1);

namespace LastJob\Letta;

/**
 * Letta agent-box connection settings. Secrets live outside git:
 * - getenv LASTJOB_LETTA_* on the host, or
 * - public/includes/config.letta.local.php (return array, not committed).
 */
final class LettaConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $apiKey,
        public readonly string $agentId,
        public readonly int $timeoutSeconds = 90,
    ) {
    }

    public static function fromEnvironment(): ?self
    {
        $local = __DIR__ . '/../config.letta.local.php';
        if (is_file($local)) {
            /** @var array<string,mixed> $cfg */
            $cfg = require $local;
            return self::fromArray($cfg);
        }

        $base = getenv('LASTJOB_LETTA_BASE_URL') ?: '';
        $key = getenv('LASTJOB_LETTA_API_KEY') ?: '';
        $agent = getenv('LASTJOB_LETTA_AGENT_ID') ?: '';
        if ($base === '' || $key === '' || $agent === '') {
            return null;
        }

        return new self(
            rtrim($base, '/'),
            $key,
            $agent,
            (int) (getenv('LASTJOB_LETTA_TIMEOUT') ?: 90),
        );
    }

    /** @param array<string,mixed> $cfg */
    public static function fromArray(array $cfg): self
    {
        $base = (string) ($cfg['base_url'] ?? $cfg['LETTA_BASE_URL'] ?? '');
        $key = (string) ($cfg['api_key'] ?? $cfg['LETTA_SERVER_PASSWORD'] ?? $cfg['LETTA_API_KEY'] ?? '');
        $agent = (string) ($cfg['agent_id'] ?? $cfg['LETTA_AGENT_ID'] ?? '');
        if ($base === '' || $key === '' || $agent === '') {
            throw new \InvalidArgumentException('Letta config missing base_url, api_key, or agent_id');
        }

        return new self(
            rtrim($base, '/'),
            $key,
            $agent,
            (int) ($cfg['timeout_seconds'] ?? $cfg['timeout'] ?? 90),
        );
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '' && $this->agentId !== '';
    }
}
