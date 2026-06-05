<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Loads canonical JSON rules from data/ and (optionally) mirrors them into
 * SQLite. JSON is the versioned source of truth; SQLite is a runtime query
 * cache rebuilt idempotently on bootstrap.
 */
final class Rules
{
    private string $dataDir;

    /** @var array<string,array<string,mixed>> */
    private array $ice = [];
    /** @var array<string,array<string,mixed>> */
    private array $programs = [];

    public function __construct(?string $dataDir = null)
    {
        $this->dataDir = $dataDir ?? dirname(__DIR__) . '/data';
        $this->ice = $this->indexById($this->loadJson('netrun/ice.json'));
        $this->programs = $this->indexById($this->loadJson('netrun/programs.json'));
    }

    /** @return array<int,array<string,mixed>> */
    public function loadJson(string $relative): array
    {
        $path = $this->dataDir . '/' . ltrim($relative, '/');
        if (!is_file($path)) {
            throw new \RuntimeException("Rules file missing: {$path}");
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("Invalid JSON in {$path}");
        }
        return $decoded;
    }

    /** @return array<string,mixed> */
    public function ice(string $id): array
    {
        if (!isset($this->ice[$id])) {
            throw new \RuntimeException("Unknown ICE id: {$id}");
        }
        return $this->ice[$id];
    }

    /** @return array<string,mixed> */
    public function program(string $id): array
    {
        if (!isset($this->programs[$id])) {
            throw new \RuntimeException("Unknown program id: {$id}");
        }
        return $this->programs[$id];
    }

    /** @return array<string,array<string,mixed>> */
    public function allIce(): array
    {
        return $this->ice;
    }

    /** @return array<string,array<string,mixed>> */
    public function allPrograms(): array
    {
        return $this->programs;
    }

    /** @return array<string,mixed> */
    public function architecture(string $name): array
    {
        $arch = json_decode(
            (string) file_get_contents($this->dataDir . "/netrun/architectures/{$name}.json"),
            true
        );
        if (!is_array($arch)) {
            throw new \RuntimeException("Unknown architecture: {$name}");
        }
        return $arch;
    }

    /**
     * Idempotent SQLite bootstrap: create tables if missing, upsert every rule
     * keyed by id. Safe to run on every deploy / boot.
     */
    public function bootstrapSqlite(string $dbPath): \PDO
    {
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new \PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE IF NOT EXISTS netrun_ice (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            subcategory TEXT,
            per INTEGER, atk INTEGER, def INTEGER,
            dmg TEXT, lethal INTEGER, alarm INTEGER,
            effect TEXT, flavor TEXT, source TEXT
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS netrun_program (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            subcategory TEXT, kind TEXT,
            slot_cost INTEGER, bonus INTEGER, reduce INTEGER,
            dmg TEXT, effect TEXT, flavor TEXT, source TEXT
        )');

        $iceStmt = $pdo->prepare('INSERT OR REPLACE INTO netrun_ice
            (id,name,subcategory,per,atk,def,dmg,lethal,alarm,effect,flavor,source)
            VALUES (:id,:name,:subcategory,:per,:atk,:def,:dmg,:lethal,:alarm,:effect,:flavor,:source)');
        foreach ($this->ice as $row) {
            $iceStmt->execute([
                ':id' => $row['id'],
                ':name' => $row['name'],
                ':subcategory' => $row['subcategory'] ?? null,
                ':per' => $row['per'] ?? 0,
                ':atk' => $row['atk'] ?? 0,
                ':def' => $row['def'] ?? 0,
                ':dmg' => $row['dmg'] ?? '0',
                ':lethal' => !empty($row['lethal']) ? 1 : 0,
                ':alarm' => !empty($row['alarm']) ? 1 : 0,
                ':effect' => $row['effect'] ?? null,
                ':flavor' => $row['flavor'] ?? null,
                ':source' => $row['source'] ?? null,
            ]);
        }

        $progStmt = $pdo->prepare('INSERT OR REPLACE INTO netrun_program
            (id,name,subcategory,kind,slot_cost,bonus,reduce,dmg,effect,flavor,source)
            VALUES (:id,:name,:subcategory,:kind,:slot_cost,:bonus,:reduce,:dmg,:effect,:flavor,:source)');
        foreach ($this->programs as $row) {
            $progStmt->execute([
                ':id' => $row['id'],
                ':name' => $row['name'],
                ':subcategory' => $row['subcategory'] ?? null,
                ':kind' => $row['kind'] ?? null,
                ':slot_cost' => $row['slot_cost'] ?? 1,
                ':bonus' => $row['bonus'] ?? 0,
                ':reduce' => $row['reduce'] ?? 0,
                ':dmg' => $row['dmg'] ?? '0',
                ':effect' => $row['effect'] ?? null,
                ':flavor' => $row['flavor'] ?? null,
                ':source' => $row['source'] ?? null,
            ]);
        }

        return $pdo;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array<string,array<string,mixed>>
     */
    private function indexById(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (!isset($row['id'])) {
                throw new \RuntimeException('Rule row missing id');
            }
            $out[(string) $row['id']] = $row;
        }
        return $out;
    }
}
