<?php
declare(strict_types=1);

namespace LastJob\Letta;

/**
 * Factory helpers for wiring Letta on the game host.
 */
final class LettaServices
{
    /** Paths checked in order (first match wins). */
    public static function configPaths(): array
    {
        $paths = [];
        $local = __DIR__ . '/../config.letta.local.php';
        if (is_file($local)) {
            $paths[] = $local;
        }
        // Multihost: {DB_PARENT}/config/letta.php (html/includes/Letta -> up 3).
        $parent = dirname(__DIR__, 3) . '/config/letta.php';
        if (is_file($parent)) {
            $paths[] = $parent;
        }

        return $paths;
    }

    public static function loadConfigArray(): ?array
    {
        foreach (self::configPaths() as $path) {
            /** @var array<string,mixed> $cfg */
            $cfg = require $path;

            return $cfg;
        }

        $base = getenv('LASTJOB_LETTA_BASE_URL') ?: '';
        $key = getenv('LASTJOB_LETTA_API_KEY') ?: '';
        $agent = getenv('LASTJOB_LETTA_AGENT_ID') ?: '';
        if ($base === '' || $key === '' || $agent === '') {
            return null;
        }

        return [
            'base_url' => $base,
            'api_key' => $key,
            'agent_id' => $agent,
            'cache_db' => getenv('LASTJOB_LETTA_CACHE_DB') ?: '',
        ];
    }

    public static function brokerFromEnvironment(): ?NpcIntentBroker
    {
        $cfg = self::loadConfigArray();
        if ($cfg === null) {
            return null;
        }

        $config = LettaConfig::fromArray($cfg);
        $cachePath = (string) ($cfg['cache_db'] ?? '');
        if ($cachePath === '') {
            $cachePath = dirname(__DIR__, 3) . '/db/npc-intent.sqlite';
        }

        return new NpcIntentBroker(
            new LettaClient($config),
            new LettaResponseCache($cachePath),
        );
    }
}
