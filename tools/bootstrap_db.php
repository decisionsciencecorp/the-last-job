<?php
declare(strict_types=1);

/**
 * Idempotent: rebuild the SQLite rules cache from canonical JSON.
 * Safe to run on every deploy. Usage: php tools/bootstrap_db.php [db_path]
 */

require __DIR__ . '/../includes/autoload.php';

use LastJob\Rules;

$dbPath = $argv[1] ?? (__DIR__ . '/../db/the-last-job.sqlite');

$rules = new Rules();
$pdo = $rules->bootstrapSqlite($dbPath);

$ice = (int) $pdo->query('SELECT COUNT(*) FROM netrun_ice')->fetchColumn();
$prog = (int) $pdo->query('SELECT COUNT(*) FROM netrun_program')->fetchColumn();

fwrite(STDOUT, sprintf(
    "Bootstrapped %s\n  netrun_ice: %d rows\n  netrun_program: %d rows\n",
    $dbPath, $ice, $prog
));
