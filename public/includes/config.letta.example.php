<?php
declare(strict_types=1);

/**
 * Copy to config.letta.local.php on the game host (outside git / not in repo).
 * Or set env: LASTJOB_LETTA_BASE_URL, LASTJOB_LETTA_API_KEY, LASTJOB_LETTA_AGENT_ID
 */
return [
    'base_url' => 'https://agents.the-last-job.decisionsciencecorp.com',
    'api_key' => 'YOUR_LETTA_SERVER_PASSWORD',
    'agent_id' => 'agent-8c6d5b9a-a05b-4a95-b2e0-13b15972e31e',
    'cache_db' => '/var/www/the-last-job.decisionsciencecorp.com/db/npc-intent.sqlite',
];
