<?php
declare(strict_types=1);

namespace LastJob\Letta;

/**
 * Resolves per-beat NPC intent/dialogue via Letta, with SQLite caching so
 * replays are deterministic and auditable. The engine still owns outcomes.
 */
final class NpcIntentBroker
{
    public function __construct(
        private LettaClient $client,
        private LettaResponseCache $cache,
    ) {
    }

    public static function runId(int $seed, string $jobId): string
    {
        return hash('sha256', $seed . '|' . $jobId);
    }

    /**
     * @param array<string,mixed> $beatContext
     * @return array{intent:string,dialogue:string,cached:bool}
     */
    public function intentForBeat(string $runId, string $beatId, string $npcId, array $beatContext): array
    {
        $safe = self::sanitizeContext($beatContext);
        $hash = LettaResponseCache::hashContext($safe);

        $hit = $this->cache->get($runId, $beatId, $npcId, $hash);
        if ($hit !== null) {
            return [
                'intent' => $hit['intent'],
                'dialogue' => $hit['dialogue'],
                'cached' => true,
            ];
        }

        $fresh = $this->client->npcIntent($safe);
        $this->cache->put($runId, $beatId, $npcId, $hash, $fresh);

        return [
            'intent' => $fresh['intent'],
            'dialogue' => $fresh['dialogue'],
            'cached' => false,
        ];
    }

    /**
     * @param array<string,mixed> $report JobRunner after-action report
     * @return array<string,mixed> report with beats[*].npc_* enriched
     */
    public function enrichReport(array $report, string $runId): array
    {
        if (!isset($report['beats']) || !is_array($report['beats'])) {
            return $report;
        }

        foreach ($report['beats'] as $i => $beat) {
            if (!is_array($beat)) {
                continue;
            }
            $beatId = (string) ($beat['obstacle'] ?? ('beat-' . $i));
            $npcId = (string) ($beat['member'] ?? 'unknown');
            $context = [
                'job' => (string) ($report['job'] ?? ''),
                'obstacle' => $beatId,
                'member' => $npcId,
                'role' => (string) ($beat['role'] ?? ''),
                'success' => (bool) ($beat['success'] ?? false),
                'total' => (int) ($beat['total'] ?? 0),
                'dv' => (int) ($beat['dv'] ?? 0),
                'crit' => $beat['crit'] ?? null,
            ];

            try {
                $npc = $this->intentForBeat($runId, $beatId, $npcId, $context);
                $report['beats'][$i]['npc_intent'] = $npc['intent'];
                $report['beats'][$i]['npc_dialogue'] = $npc['dialogue'];
                $report['beats'][$i]['npc_cached'] = $npc['cached'];
            } catch (\Throwable $e) {
                $report['beats'][$i]['npc_error'] = $e->getMessage();
            }
        }

        $report['letta_run_id'] = $runId;
        return $report;
    }

    /** @param array<string,mixed> $context */
    private static function sanitizeContext(array $context): array
    {
        $allowed = ['job', 'obstacle', 'member', 'role', 'success', 'total', 'dv', 'crit'];
        $out = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $context)) {
                $out[$key] = $context[$key];
            }
        }

        return $out;
    }
}
