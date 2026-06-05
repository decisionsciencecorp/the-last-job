<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/autoload.php';

use LastJob\Economy;
use LastJob\JobRunner;
use LastJob\Letta\LettaServices;
use LastJob\Letta\NpcIntentBroker;
use LastJob\Lifepath\CrewBuilder;
use LastJob\Rng;
use LastJob\Rules;

header('Content-Type: application/json; charset=utf-8');

try {
    $rules = new Rules();
    $seed = isset($_GET['seed']) ? (int) $_GET['seed'] : 2077;
    $jobId = isset($_GET['job']) ? (string) $_GET['job'] : 'job.arasaka-substation';
    $streetCred = isset($_GET['street_cred']) ? max(0, (int) $_GET['street_cred']) : 4;
    $maxLive = isset($_GET['max_live']) ? max(0, (int) $_GET['max_live']) : 1;
    $roleIds = [];
    for ($i = 0; $i < 4; $i++) {
        if (isset($_GET['role' . $i]) && $_GET['role' . $i] !== '') {
            $roleIds[] = (string) $_GET['role' . $i];
        }
    }
    if ($roleIds === []) {
        $roleIds = CrewBuilder::DEFAULT_ROLES;
    }

    if (!$rules->hasJob($jobId)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'error' => "Unknown contract '{$jobId}'.",
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    if (!$rules->isJobUnlocked($jobId, $streetCred)) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'error' => "Street cred {$streetCred} is too low for this contract.",
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    $broker = LettaServices::brokerFromEnvironment();
    if ($broker === null) {
        http_response_code(503);
        echo json_encode([
            'status' => 'error',
            'error' => 'Letta is not configured on this host.',
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    $crew = (new CrewBuilder($rules, new Rng($seed)))->build($roleIds);
    $report = (new JobRunner($rules))->run(
        $crew,
        $rules->job($jobId),
        new Rng($seed * 7 + 1),
        new Economy(500, $streetCred),
    );

    $runId = NpcIntentBroker::runId($seed, $jobId);
    $enriched = $broker->enrichReport($report, $runId, $maxLive <= 0 ? 0 : $maxLive);
    $beats = is_array($enriched['beats'] ?? null) ? $enriched['beats'] : [];
    $total = count($beats);
    $cached = 0;
    $fresh = 0;
    $errors = 0;
    $deferred = 0;
    foreach ($beats as $beat) {
        if (!is_array($beat)) {
            continue;
        }
        if (!empty($beat['npc_skipped'])) {
            $deferred++;
            continue;
        }
        if (!empty($beat['npc_error'])) {
            $errors++;
            continue;
        }
        if (array_key_exists('npc_cached', $beat)) {
            if (!empty($beat['npc_cached'])) {
                $cached++;
            } else {
                $fresh++;
            }
        }
    }

    echo json_encode([
        'status' => 'ok',
        'seed' => $seed,
        'job' => $jobId,
        'run_id' => $runId,
        'max_live' => $maxLive,
        'beats_total' => $total,
        'cached_hits' => $cached,
        'fresh_fetched' => $fresh,
        'deferred' => $deferred,
        'errors' => $errors,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
}

