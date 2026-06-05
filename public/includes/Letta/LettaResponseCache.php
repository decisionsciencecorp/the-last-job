<?php
declare(strict_types=1);

namespace LastJob\Letta;

/**
 * SQLite cache for NPC intent/dialogue — replayable, auditable runs.
 * Key: (run_id, beat_id, npc_id, context_hash).
 */
final class LettaResponseCache
{
    private \PDO $pdo;

    public function __construct(string $dbPath)
    {
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->pdo = new \PDO('sqlite:' . $dbPath, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        $this->bootstrap();
    }

    public function bootstrap(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS letta_npc_response (
            run_id TEXT NOT NULL,
            beat_id TEXT NOT NULL,
            npc_id TEXT NOT NULL,
            context_hash TEXT NOT NULL,
            intent TEXT NOT NULL,
            dialogue TEXT NOT NULL,
            raw_response TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            PRIMARY KEY (run_id, beat_id, npc_id, context_hash)
        )');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_letta_npc_run ON letta_npc_response (run_id)');
    }

    /** @return array{intent:string,dialogue:string,raw:string}|null */
    public function get(string $runId, string $beatId, string $npcId, string $contextHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT intent, dialogue, raw_response FROM letta_npc_response
             WHERE run_id = :run AND beat_id = :beat AND npc_id = :npc AND context_hash = :hash'
        );
        $stmt->execute([
            ':run' => $runId,
            ':beat' => $beatId,
            ':npc' => $npcId,
            ':hash' => $contextHash,
        ]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return [
            'intent' => (string) $row['intent'],
            'dialogue' => (string) $row['dialogue'],
            'raw' => (string) ($row['raw_response'] ?? ''),
        ];
    }

    /** @param array{intent:string,dialogue:string,raw:string} $response */
    public function put(string $runId, string $beatId, string $npcId, string $contextHash, array $response): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT OR REPLACE INTO letta_npc_response
             (run_id, beat_id, npc_id, context_hash, intent, dialogue, raw_response)
             VALUES (:run, :beat, :npc, :hash, :intent, :dialogue, :raw)'
        );
        $stmt->execute([
            ':run' => $runId,
            ':beat' => $beatId,
            ':npc' => $npcId,
            ':hash' => $contextHash,
            ':intent' => $response['intent'],
            ':dialogue' => $response['dialogue'],
            ':raw' => $response['raw'],
        ]);
    }

    /** Stable hash of player-safe beat context (no sealed agenda fields). */
    public static function hashContext(array $context): string
    {
        ksort($context);
        return hash('sha256', json_encode($context, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
