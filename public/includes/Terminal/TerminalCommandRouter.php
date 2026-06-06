<?php
declare(strict_types=1);

namespace LastJob\Terminal;

use LastJob\Economy;
use LastJob\Job;
use LastJob\JobRunner;
use LastJob\Lifepath\Character;
use LastJob\Lifepath\CrewBuilder;
use LastJob\Rng;
use LastJob\Rules;
use LastJob\Story\IntelDossier;

final class TerminalCommandRouter
{
    /** @var array<int,array{prompt:string}> */
    private const INTAKE_PROMPTS = [
        ['prompt' => "ANIMAL> where'd you learn to lie so clean?"],
        ['prompt' => 'ANIMAL> who still has a piece of you?'],
        ['prompt' => 'ANIMAL> when things go loud, what do people pay you for?'],
        ['prompt' => "ANIMAL> what don't you do, even when the eddies beg?"],
    ];

    public function __construct(
        private Rules $rules,
    ) {
    }

    /** @return array<string,mixed> */
    public function handle(TerminalState $state, string $rawCommand): array
    {
        $command = $this->normalize($rawCommand);
        if ($command === '') {
            return $this->response($state, '', ['line open. answer or choose the next move.']);
        }

        if (in_array($command, ['help', '?'], true)) {
            $lines = $this->help();
            $state->appendHistory($rawCommand, $lines);
            return $this->response($state, $rawCommand, $lines);
        }

        if (in_array($command, ['status', 'whoami'], true)) {
            $lines = $this->status($state);
            $state->appendHistory($rawCommand, $lines);
            return $this->response($state, $rawCommand, $lines);
        }

        if (in_array($command, ['boot', 'wake deck', 'reset'], true)) {
            $lines = $this->boot($state, $command);
            $state->appendHistory($rawCommand, $lines);
            return $this->response($state, $rawCommand, $lines);
        }

        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage === 'intake' && !$this->isReservedCommand($command)) {
            $lines = $this->handleIntakeAnswer($state, $rawCommand);
            $state->appendHistory($rawCommand, $lines);
            return $this->response($state, $rawCommand, $lines);
        }

        $lines = match (true) {
            in_array($command, ['answer', 'take call', 'run take-call'], true) => $this->answer($state),
            in_array($command, ['let it ring', 'run let-it-ring'], true) => $this->letItRing($state),
            in_array($command, ['bring kojo', 'run light'], true) => $this->chooseCrew($state, $command),
            in_array($command, ['ask fixer crew', 'request crew files', 'open fixer.roster', 'show dossiers'], true) => $this->crew($state),
            in_array($command, ['list contracts', 'contracts', 'list contract packets'], true) => $this->contracts($state),
            str_starts_with($command, 'inspect contract') => $this->inspectContract($state, $command),
            in_array($command, ['accept', 'negotiate', 'walk'], true) => $this->handleOfferChoice($state, $command),
            str_starts_with($command, 'run contract') => $this->runContract($state, $command),
            in_array($command, ['wake', 'show wake'], true) => $this->wake($state),
            in_array($command, ['file', 'open file', 'show file'], true) => $this->file($state),
            in_array($command, ['answer next call', 'next call'], true) => $this->answerNextCall($state),
            default => [
                "unknown command: {$rawCommand}",
                'try: answer | bring kojo | list contracts | accept | run contract 1 | wake | file',
            ],
        };

        $state->appendHistory($rawCommand, $lines);
        return $this->response($state, $rawCommand, $lines);
    }

    private function normalize(string $command): string
    {
        $command = strtolower(trim($command));
        $command = preg_replace('/\s+/', ' ', $command) ?? $command;
        return trim($command, " \t\n\r\0\x0B$");
    }

    /** @return string[] */
    private function help(): array
    {
        return [
            'available commands:',
            '  answer                answer the fixer line',
            '  bring kojo            take Animal\'s first tech offer',
            '  run light             refuse first tech offer',
            '  ask fixer crew        receive fixer-vouched dossier files',
            '  list contracts        list available contract packets',
            '  accept / negotiate    choose first contract terms',
            '  walk                  refuse first offer (pressure returns)',
            '  inspect contract 1    read packet details',
            '  run contract 1        jack in with current crew',
            '  wake                  read the last aftermath',
            '  file                  open the shard wall',
            '  answer next call      pick up the hook after first shard',
            '  status                show deck state',
            '  reset                 wipe this browser session and reboot',
        ];
    }

    /** @return string[] */
    private function boot(TerminalState $state, string $command): array
    {
        if ($command === 'reset') {
            $fresh = TerminalState::fresh();
            foreach ($fresh->toArray() as $key => $value) {
                $state->set($key, $value);
            }
        }

        return [
            '[00.000] power reroute: motel wall jack / unstable',
            '[00.217] loading city map: watson, heywood, combat zone fragments',
            '[00.409] warning: corporate mesh watching public grids',
            '[00.633] fixer relay found: ANIMAL / encrypted / paid in advance',
            '[00.901] context: you are not browsing jobs. you are answering a line.',
            '[01.144] rule: one action at a time. line first. crew second.',
            '[01.388] deck ready. city breathing behind the glass.',
            'next: `answer` or `let it ring`',
        ];
    }

    /** @return string[] */
    private function answer(TerminalState $state): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage === 'second_call_ready') {
            return $this->answerNextCall($state);
        }
        if ($stage !== 'boot' && $stage !== 'animal_call') {
            return ['line already open. finish what is in front of you.'];
        }

        $state->set('answered', true);
        $state->set('episode_stage', 'intake');
        $state->set('intake_index', 0);
        $state->set('intake_answers', []);
        return [
            'LINK encrypted relay / watson-relay-3',
            "ANIMAL> you up. coffee's burnt again.",
            'ANIMAL> got something small enough to survive and ugly enough to pay.',
            'ANIMAL> before i put your name near it, remind me what kind of trouble follows you.',
            self::INTAKE_PROMPTS[0]['prompt'],
        ];
    }

    /** @return string[] */
    private function letItRing(TerminalState $state): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage !== 'boot') {
            return ['wrong moment to ghost the line. finish what is live first.'];
        }
        return [
            '> the line dies.',
            '> it rings again before the room gets quiet.',
            'ANIMAL> city charges extra for hesitation.',
            'next: `answer`',
        ];
    }

    /** @return string[] */
    private function chooseCrew(TerminalState $state, string $command): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage !== 'booth_choice') {
            return ['crew choice is not live yet. answer the line first.'];
        }

        if ($command === 'bring kojo') {
            $state->set('roles', CrewBuilder::DEFAULT_ROLES);
            $state->set('first_crew_choice', 'bring_kojo');
            $state->set('episode_stage', 'offer');
            return [
                'ANIMAL> good. kojo is in. he makes broken things apologize.',
                'TILDE> then we move while the loop is still ours.',
                ...$this->episodeOfferLines(),
            ];
        }

        $state->set('roles', ['role.solo', 'role.netrunner', 'role.fixer']);
        $state->set('first_crew_choice', 'run_light');
        $state->set('episode_stage', 'offer');
        return [
            'ANIMAL> running light. bold or broke, we find out soon.',
            'TILDE> no spare hands, then. keep your timing clean.',
            ...$this->episodeOfferLines(),
        ];
    }

    /** @return string[] */
    private function crew(TerminalState $state): array
    {
        $state->set('answered', true);
        $state->set('crew_requested', true);

        $crew = $this->buildCrew($state);
        $lines = [
            'ANIMAL > you get dossiers first. no direct line until they say yes.',
            'ANIMAL > i filtered out the obvious corpses. no promises on the subtle ones.',
            '--- fixer.roster ---',
        ];

        foreach ($crew as $idx => $member) {
            $c = $member->toPublicArray();
            $events = array_values(array_filter(array_map('strval', $c['life_events'] ?? [])));
            $notes = $events === [] ? 'keeps the past off-record' : implode('; ', array_slice($events, 0, 2));
            $lines[] = sprintf(
                '[%d] %s / %s / %s',
                $idx + 1,
                $member->handle,
                $member->roleName,
                (string) ($c['origin'] ?? 'origin scrubbed'),
            );
            $lines[] = '    cv: ' . (string) ($c['personality'] ?? 'hard to read');
            $lines[] = '    tell: ' . (string) ($c['public_hook'] ?? 'watch the silences');
            $lines[] = '    fixer notes: ' . $notes;
        }

        $lines[] = 'next: `list contracts`';
        return $lines;
    }

    /** @return string[] */
    private function contracts(TerminalState $state): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage === 'booth_choice') {
            return [
                'ANIMAL> choose your first body before i price your first mistake.',
                'next: `bring kojo` or `run light`',
            ];
        }
        if ($stage === 'offer' || $stage === 'offer_locked') {
            return $this->episodeOfferLines();
        }
        if ($stage === 'open_play') {
            return $this->followupContractLines($state);
        }

        $lines = ['--- contract packets visible to current rep ---'];
        $jobs = $this->availableJobs($state);
        foreach ($jobs as $idx => $job) {
            $lines[] = sprintf(
                '[%d] %s / fixer:%s / %deb / rep+%d',
                $idx + 1,
                $job->name,
                $job->fixer,
                $job->payoutEddies,
                $job->streetCredReward,
            );
            $lines[] = '    ' . ($job->briefing !== '' ? $job->briefing : 'packet summary redacted until inspection.');
        }
        $lines[] = 'next: `inspect contract 1` or `run contract 1`';
        return $lines;
    }

    /** @return string[] */
    private function inspectContract(TerminalState $state, string $command): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage === 'offer' || $stage === 'offer_locked') {
            $state->set('selected_contract', 'episode.ncart');
            return [
                '--- contract.packet episode.ncart ---',
                'name: NCART Empty Cage',
                'fixer: Animal',
                'payout: 8000eb minus Animal bite',
                'rep gate: 0',
                'brief: NCART waterfront. maintenance platform 6.',
                'stakes: package in a Faraday cage. no listed weight.',
                'complication: camera loop is six seconds. guard change is too clean.',
                'next: `accept` | `negotiate` | `walk`',
            ];
        }
        if ($stage === 'open_play') {
            $packet = $this->followupPacketFromCommand($state, $command);
            if (!is_array($packet)) {
                return ['no such second-call packet. try `list contracts`.'];
            }
            $state->set('selected_contract', (string) $packet['id']);
            $state->set('selected_followup_contract', (string) $packet['id']);
            return [
                '--- contract.packet ' . (string) $packet['id'] . ' ---',
                'name: ' . (string) $packet['name'],
                'fixer: ' . (string) $packet['fixer'],
                'payout: ' . (int) ($packet['payout'] ?? 0) . 'eb',
                'rep gate: ' . (int) ($packet['rep'] ?? 0),
                'brief: ' . (string) ($packet['brief'] ?? 'redacted'),
                'stakes: ' . (string) ($packet['stakes'] ?? 'unknown'),
                'complication: ' . (string) ($packet['complication'] ?? 'none reported'),
                'next: `run contract ' . (int) ($packet['number'] ?? 1) . '`',
            ];
        }

        $job = $this->resolveContract($state, $command);
        if (!$job instanceof Job) {
            return ['no such visible contract. try `list contracts`.'];
        }

        $state->set('selected_contract', $job->id);
        return [
            "--- contract.packet {$job->id} ---",
            "name: {$job->name}",
            "fixer: {$job->fixer}",
            "payout: {$job->payoutEddies}eb",
            "rep gate: {$job->minRepTier}",
            'brief: ' . ($job->briefing ?: 'redacted'),
            'stakes: ' . ($job->stakes ?: 'unknown'),
            'complication: ' . ($job->complication ?: 'none reported, which means somebody is lying'),
            'next: `run contract ' . $this->contractNumber($state, $job) . '`',
        ];
    }

    /** @return string[] */
    private function handleOfferChoice(TerminalState $state, string $command): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage !== 'offer' && $stage !== 'offer_locked') {
            return ['no live offer to respond to yet.'];
        }

        if ($command === 'walk') {
            $state->set('walked_once', true);
            $state->set('episode_stage', 'offer_locked');
            return [
                'ANIMAL> cleanest choice you made all night.',
                '> wire quiet.',
                '> package ping arrives anyway.',
                'ANIMAL> hate that for us.',
                'next: `accept` or `negotiate`',
            ];
        }

        $state->set('first_contract_state', $command);
        $state->set('selected_contract', 'episode.ncart');
        $state->set('episode_stage', 'offer_locked');
        if ($command === 'negotiate') {
            return [
                'ANIMAL> you want prettier terms, you pay in future favors.',
                'ANIMAL> fine. tiny bump. no guarantees.',
                'TILDE> done. move before the loop drifts.',
                'next: `run contract 1`',
            ];
        }

        return [
            'ANIMAL> good. quick hands, quiet shoes.',
            'TILDE> platform six. loop starts at 02:17.',
            'next: `run contract 1`',
        ];
    }

    /** @return string[] */
    private function runContract(TerminalState $state, string $command): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if (($stage === 'offer' || $stage === 'offer_locked') && (string) $state->get('first_contract_state', 'none') === 'none') {
            return ['choose `accept` or `negotiate` first.'];
        }

        $job = $this->resolveContract($state, $command);
        if (!$job instanceof Job) {
            return ['no such visible contract. try `list contracts`.'];
        }

        $state->set('answered', true);
        $state->set('crew_requested', true);
        $state->set('selected_contract', $job->id);

        $crew = $this->buildCrew($state);
        $economy = new Economy($state->eddies(), $state->streetCred());
        $report = (new JobRunner($this->rules))->run($crew, $job, new Rng($state->seed() * 7 + 1), $economy);
        $state->set('last_report', $report);
        if (!empty($report['success'])) {
            $econ = $economy->toArray();
            $state->set('eddies', (int) ($econ['eddies'] ?? $state->eddies()));
            $state->set('street_cred', (int) ($econ['street_cred'] ?? $state->streetCred()));
        }
        $axes = $this->lifeAxes($state);
        $state->set('life_axes', $axes);
        $axisBonus = $this->axisRunBonus($axes, !empty($report['success']));
        if ($axisBonus['eddies'] > 0) {
            $state->set('eddies', $state->eddies() + $axisBonus['eddies']);
        }
        if ($axisBonus['rep'] > 0) {
            $state->set('street_cred', $state->streetCred() + $axisBonus['rep']);
        }
        $risk = $this->updateRiskTrack($state, !empty($report['success']), $axes['method']);
        $isEpisodeContract = in_array($stage, ['offer', 'offer_locked'], true);
        $drift = $this->crewDriftEffect(
            max(0, min(5, (int) $state->get('heat', 0))),
            max(0, min(5, (int) $state->get('pressure', 0))),
            $axes['bond'],
            !empty($report['success']),
            $isEpisodeContract,
        );
        if ($drift['payout_delta'] !== 0) {
            $state->set('eddies', max(0, $state->eddies() + $drift['payout_delta']));
        }
        if ($drift['rep_delta'] !== 0) {
            $state->set('street_cred', max(0, $state->streetCred() + $drift['rep_delta']));
        }
        if ($isEpisodeContract) {
            $state->set('first_shard_seen', true);
            $state->set('episode_stage', 'wake_ready');
        } else {
            $state->set('episode_stage', 'open_play');
        }
        $report['episode_contract'] = $isEpisodeContract;
        $report['life_axes'] = $axes;
        $report['axis_bonus_eddies'] = $axisBonus['eddies'];
        $report['axis_bonus_rep'] = $axisBonus['rep'];
        $report['risk_heat'] = $risk['heat'];
        $report['risk_pressure'] = $risk['pressure'];
        $report['risk_delta_heat'] = $risk['delta_heat'];
        $report['risk_delta_pressure'] = $risk['delta_pressure'];
        $report['crew_drift'] = $drift;
        $state->set('last_report', $report);

        $lines = ['--- run ' . $job->id . ' ---'];
        if ($isEpisodeContract) {
            $lines[] = '> ncart platform 6 smells like rain and brake dust.';
            $lines[] = '> cage sits under a bench where cameras pretend not to look.';
            $lines[] = 'TILDE> loop is six seconds. maybe seven if kojo is right.';
            $lines[] = $this->runMethodLine($axes);
        } else {
            $lines[] = '> second-call channel open. no fixer watermark.';
            $lines[] = $this->runMethodLine($axes);
        }
        $lines = array_merge($lines, [
            'crew channel opened by ANIMAL. nobody has your direct line.',
            !empty($report['success']) ? 'RESULT clean enough to get paid.' : 'RESULT burned. somebody is bleeding or lying.',
            'clock: ' . (int) ($report['clock']['spent'] ?? 0) . '/' . (int) ($report['clock']['total'] ?? 0) . ' ticks',
            'net: ' . (string) ($report['netrun']['outcome'] ?? 'unknown'),
            'payout: ' . (int) ($report['payout_eddies'] ?? 0) . 'eb',
            'rep: +' . (int) ($report['street_cred_gained'] ?? 0),
        ]);
        if (!empty($report['debrief'])) {
            $lines[] = 'wake: ' . (string) $report['debrief'];
        }
        if ($axisBonus['eddies'] > 0 || $axisBonus['rep'] > 0) {
            $lines[] = 'edge: lifepath read paid out (' . $axisBonus['eddies'] . 'eb / rep+' . $axisBonus['rep'] . ').';
        }
        if ($drift['payout_delta'] !== 0 || $drift['rep_delta'] !== 0) {
            $lines[] = sprintf(
                'crew drift: %s (%+deb / rep%+d)',
                $drift['label'],
                $drift['payout_delta'],
                $drift['rep_delta'],
            );
        }
        $lines[] = sprintf(
            'risk: heat %d (%+d) / pressure %d (%+d)',
            $risk['heat'],
            $risk['delta_heat'],
            $risk['pressure'],
            $risk['delta_pressure'],
        );
        if ($drift['run_line'] !== '') {
            $lines[] = $drift['run_line'];
        }
        if ($isEpisodeContract) {
            $lines[] = '> you lift the cage.';
            $lines[] = '> it is lighter than it should be.';
            $lines[] = "TILDE> don't open it there.";
            $lines[] = 'next: `wake` or `file`';
        } else {
            $lines[] = 'VOICE> you still breathing? good.';
            $lines[] = 'next: `wake` or `list contracts`';
        }
        return $lines;
    }

    /** @return string[] */
    private function wake(TerminalState $state): array
    {
        $report = $state->get('last_report');
        if (!is_array($report)) {
            return ['no wake yet. run a contract first.'];
        }

        $lines = ['--- wake.after_action ---'];
        if (($report['episode_contract'] ?? false) === true) {
            $axes = $report['life_axes'] ?? $this->lifeAxes($state);
            $axes = is_array($axes) ? $axes : $this->lifeAxes($state);
            $lines[] = '> you put the gear down.';
            $lines[] = '> you sit.';
            $lines[] = '> nobody talks until the transfer clears.';
            $lines[] = 'WAKE> payout wired: ' . (int) ($report['payout_eddies'] ?? 0) . 'eb minus animal bite.';
            $lines[] = !empty($report['success']) ? 'WAKE> heat: low. nobody important is looking yet.' : 'WAKE> heat: rising. somebody noticed.';
            $lines[] = $this->wakePlaceLine($axes);
            $lines[] = 'TILDE> clean enough.';
            $lines[] = $state->get('first_crew_choice') === 'bring_kojo'
                ? 'KOJO> cage was empty, choom. empty things cost extra.'
                : '> the empty chair says the rest.';
            foreach ($this->wakeBondLines($axes) as $bondLine) {
                $lines[] = $bondLine;
            }
            $lines[] = $this->wakeMethodLine($axes);
            $lines[] = 'ANIMAL> check your pocket.';
            $lines[] = 'next: `file`';
            $state->set('episode_stage', 'file_ready');
            return $lines;
        }

        $lines[] = 'job: ' . (string) ($report['job_name'] ?? 'unknown');
        $lines[] = !empty($report['success']) ? 'status: paid' : 'status: burned';
        $lines[] = 'debrief: ' . (string) (($report['debrief'] ?? '') ?: 'nobody wants to talk about it yet.');
        $heat = max(0, min(5, (int) $state->get('heat', 0)));
        $pressure = max(0, min(5, (int) $state->get('pressure', 0)));
        $lines[] = $this->openPlayWakeRiskLine($heat, $pressure);
        $drift = $report['crew_drift'] ?? null;
        if (is_array($drift)) {
            $wakeLines = $drift['wake_lines'] ?? [];
            if (is_array($wakeLines)) {
                foreach ($wakeLines as $line) {
                    if (is_string($line) && $line !== '') {
                        $lines[] = $line;
                    }
                }
            }
        }
        $agendas = is_array($report['agendas_triggered'] ?? null) ? $report['agendas_triggered'] : [];
        foreach ($agendas as $agenda) {
            if (is_array($agenda)) {
                $lines[] = 'pressure: ' . (string) ($agenda['member'] ?? 'crew') . ' / ' . (string) ($agenda['consequence'] ?? 'unreadable');
            }
        }
        return $lines;
    }

    /** @return string[] */
    private function file(TerminalState $state): array
    {
        $lines = ['--- shard.wall ---'];
        if ((bool) $state->get('first_shard_seen')) {
            $axes = $this->lifeAxes($state);
            $lines[] = '[new] shard.ncart.empty-cage';
            $lines[] = '    type: ' . $this->shardType($axes);
            $lines[] = '    visible: ' . $this->shardTargetHint($axes);
            $lines[] = '    memo: ' . $this->shardMemo($axes);
            $state->set('episode_stage', 'second_call_ready');
        }

        $dossier = new IntelDossier(dirname(__DIR__, 2) . '/data/story/intel_threads.json');
        foreach ($dossier->threads() as $idx => $thread) {
            $lines[] = '[' . ($idx + 1) . '] ' . (string) $thread['title'];
            $lines[] = '    ' . (string) $thread['summary'];
        }
        return $lines;
    }

    /** @return string[] */
    private function status(TerminalState $state): array
    {
        $followups = $state->get('followup_packets', []);
        $followupCount = is_array($followups) ? count($followups) : 0;
        $heat = max(0, min(5, (int) $state->get('heat', 0)));
        $pressure = max(0, min(5, (int) $state->get('pressure', 0)));
        return [
            'deck: awake',
            'episode stage: ' . (string) $state->get('episode_stage', 'boot'),
            'life axes: ' . implode(' / ', array_values($this->lifeAxes($state))),
            'second-call packets: ' . $followupCount,
            'risk track: heat ' . $heat . '/5, pressure ' . $pressure . '/5',
            'fixer line: ' . ($state->get('answered') ? 'answered' : 'ringing'),
            'crew files: ' . ($state->get('crew_requested') ? 'received' : 'not requested'),
            'cash: ' . $state->eddies() . 'eb',
            'rep: ' . $state->streetCred(),
        ];
    }

    /** @return Character[] */
    private function buildCrew(TerminalState $state): array
    {
        return (new CrewBuilder($this->rules, new Rng($state->seed())))->build($state->roles());
    }

    /** @return Job[] */
    private function availableJobs(TerminalState $state): array
    {
        return array_values(array_filter(
            $this->rules->jobs(),
            fn (Job $job): bool => $this->rules->isJobUnlocked($job->id, $state->streetCred()),
        ));
    }

    private function resolveContract(TerminalState $state, string $command): ?Job
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        if ($stage === 'open_play') {
            $packet = $this->followupPacketFromCommand($state, $command);
            if (is_array($packet) && isset($packet['job_id']) && is_string($packet['job_id'])) {
                return $this->rules->job($packet['job_id']);
            }
        }
        if ($stage === 'offer' || $stage === 'offer_locked' || (string) $state->get('selected_contract') === 'episode.ncart') {
            $token = trim((string) preg_replace('/^(inspect|run) contract\s*/', '', $command));
            if ($token === '' || $token === '1' || $token === 'episode.ncart') {
                return $this->firstEpisodeJob($state);
            }
        }

        $token = trim((string) preg_replace('/^(inspect|run) contract\s*/', '', $command));
        $jobs = $this->availableJobs($state);

        if ($token === '' && is_string($state->get('selected_contract'))) {
            $token = (string) $state->get('selected_contract');
        }

        if (ctype_digit($token)) {
            return $jobs[((int) $token) - 1] ?? null;
        }

        foreach ($jobs as $job) {
            if ($job->id === $token || str_ends_with($job->id, $token)) {
                return $job;
            }
        }

        return null;
    }

    private function firstEpisodeJob(TerminalState $state): ?Job
    {
        $id = $this->rules->firstUnlockedJobId($state->streetCred());
        if ($id === null) {
            $jobs = $this->availableJobs($state);
            return $jobs[0] ?? null;
        }
        return $this->rules->job($id);
    }

    private function contractNumber(TerminalState $state, Job $needle): int
    {
        foreach ($this->availableJobs($state) as $idx => $job) {
            if ($job->id === $needle->id) {
                return $idx + 1;
            }
        }
        return 1;
    }

    /** @param string[] $lines @return array<string,mixed> */
    private function response(TerminalState $state, string $command, array $lines): array
    {
        return [
            'status' => 'ok',
            'command' => $command,
            'lines' => array_values($lines),
            'state' => [
                'episode_stage' => (string) $state->get('episode_stage', 'boot'),
                'answered' => (bool) $state->get('answered'),
                'crew_requested' => (bool) $state->get('crew_requested'),
                'selected_contract' => $state->get('selected_contract'),
                'street_cred' => $state->streetCred(),
                'eddies' => $state->eddies(),
            ],
            'suggestions' => $this->suggestions($state),
        ];
    }

    /** @return string[] */
    private function suggestions(TerminalState $state): array
    {
        $stage = (string) $state->get('episode_stage', 'boot');
        return match ($stage) {
            'boot', 'animal_call' => ['answer', 'let it ring', 'help'],
            'intake' => ['status', 'help', 'reset'],
            'booth_choice' => ['bring kojo', 'run light', 'ask fixer crew'],
            'offer', 'offer_locked' => ['inspect contract 1', 'accept', 'run contract 1'],
            'wake_ready' => ['wake', 'file', 'status'],
            'file_ready' => ['file', 'answer next call', 'status'],
            'second_call_ready' => ['answer next call', 'file', 'status'],
            default => $this->openPlaySuggestions($state),
        };
    }

    /** @return string[] */
    private function openPlaySuggestions(TerminalState $state): array
    {
        if (!$state->get('answered')) {
            return ['answer', 'let it ring', 'help'];
        }
        if (!$state->get('crew_requested')) {
            return ['ask fixer crew', 'list contracts', 'status'];
        }
        if ($state->get('last_report') === null) {
            return ['list contracts', 'inspect contract 1', 'run contract 1'];
        }
        return ['wake', 'file', 'list contracts'];
    }

    private function isReservedCommand(string $command): bool
    {
        return in_array($command, [
            'help', '?', 'status', 'whoami', 'boot', 'wake deck', 'reset',
            'answer', 'take call', 'run take-call', 'let it ring', 'run let-it-ring',
            'bring kojo', 'run light', 'ask fixer crew', 'request crew files',
            'open fixer.roster', 'show dossiers', 'list contracts', 'contracts',
            'list contract packets', 'accept', 'negotiate', 'walk', 'wake', 'show wake',
            'file', 'open file', 'show file', 'answer next call', 'next call',
        ], true) || str_starts_with($command, 'inspect contract') || str_starts_with($command, 'run contract');
    }

    /** @return string[] */
    private function handleIntakeAnswer(TerminalState $state, string $answer): array
    {
        $index = max(0, (int) $state->get('intake_index', 0));
        $answers = $state->get('intake_answers', []);
        $answers = is_array($answers) ? $answers : [];
        $answer = trim($answer);
        $answers[] = $answer;
        $state->set('intake_answers', $answers);
        $index++;
        $state->set('intake_index', $index);

        $reaction = $this->intakeReaction($answer, $index);
        if ($index < count(self::INTAKE_PROMPTS)) {
            return [
                "YOU> {$answer}",
                $reaction,
                self::INTAKE_PROMPTS[$index]['prompt'],
            ];
        }

        $state->set('episode_stage', 'booth_choice');
        $state->set('crew_requested', true);
        $state->set('life_axes', $this->lifeAxes($state));
        return [
            "YOU> {$answer}",
            $reaction,
            '> booth relay opens.',
            '> tilde is already in the room.',
            '> comm on the table. hand out. no eye contact.',
            "TILDE> the call is real. animal's nervous, which means animal is early.",
            'TILDE> take the runner if offered. refuse the sermon.',
            'ANIMAL> reading you as ' . implode(' / ', array_values($this->lifeAxes($state))) . '.',
            'ANIMAL> got a tech nearby. kojo. makes broken things apologize.',
            "ANIMAL> you want him on this, say so. you want lonely, that's your disease.",
            'next: `bring kojo` or `run light`',
        ];
    }

    private function intakeReaction(string $answer, int $index): string
    {
        $a = strtolower($answer);
        if (preg_match('/corp|arasaka|militech|biotechnica/', $a)) {
            return 'ANIMAL> corpo edges. clean enough to look dirty.';
        }
        if (preg_match('/nomad|clan|road|convoy/', $a)) {
            return 'ANIMAL> road blood. good. timing matters more than charm.';
        }
        if (preg_match('/never|wont|won\'t|dont|don\'t/', $a) && $index >= 4) {
            return 'ANIMAL> good. people with lines are easier to price.';
        }
        if (preg_match('/love|loss|dead|gone|hurt/', $a)) {
            return 'ANIMAL> copy that. keep your hands steady anyway.';
        }
        return 'ANIMAL> yeah. that tracks.';
    }

    /** @return string[] */
    private function episodeOfferLines(): array
    {
        return [
            '--- contract packets visible to current rep ---',
            '[1] NCART Empty Cage / fixer:Animal / 8000eb / rep+1',
            '    NCART waterfront. maintenance platform 6.',
            '    package in a Faraday cage. no listed weight.',
            '    cameras loop for six seconds at 02:17. guard change is too clean.',
            'TILDE> the package is hot, the cage is colder. standard terms.',
            "TILDE> pay's not the question. walking is.",
            'next: `inspect contract 1` or `accept` | `negotiate` | `walk`',
        ];
    }

    /** @return string[] */
    private function answerNextCall(TerminalState $state): array
    {
        if (!(bool) $state->get('first_shard_seen')) {
            return ['no second call yet. finish the first run and check the file.'];
        }
        $axes = $this->lifeAxes($state);
        $state->set('episode_stage', 'open_play');
        $state->set('second_call_seen', true);
        $state->set('followup_packets', $this->buildFollowupPackets($state, $axes));
        return [
            '> you do not recognize the number.',
            '> you answer anyway.',
            'VOICE> the package was empty. you knew that, didn\'t you.',
            'VOICE> we like how you work: ' . $axes['method'] . '. keep it that way.',
            'next: `list contracts`',
        ];
    }

    /** @return string[] */
    private function followupContractLines(TerminalState $state): array
    {
        $packets = $this->buildFollowupPackets($state, $this->lifeAxes($state));
        $lines = ['--- second-call packets ---', 'VOICE> pick one. prove the first run was not luck.'];
        foreach ($packets as $packet) {
            if (!is_array($packet)) {
                continue;
            }
            $lines[] = sprintf(
                '[%d] %s / fixer:%s / %deb / rep+%d',
                (int) ($packet['number'] ?? 1),
                (string) ($packet['name'] ?? 'unknown'),
                (string) ($packet['fixer'] ?? 'unknown'),
                (int) ($packet['payout'] ?? 0),
                (int) ($packet['rep'] ?? 0),
            );
            $lines[] = '    ' . (string) ($packet['brief'] ?? 'packet summary redacted');
        }
        $lines[] = 'next: `inspect contract 1` or `run contract 1`';
        return $lines;
    }

    /**
     * @param array{roots:string,method:string,bond:string} $axes
     * @return array<int,array<string,mixed>>
     */
    private function buildFollowupPackets(TerminalState $state, array $axes): array
    {
        $existing = $state->get('followup_packets', []);
        if (is_array($existing) && $existing !== []) {
            return array_values(array_filter($existing, 'is_array'));
        }

        $jobs = $this->availableJobs($state);
        if ($jobs === []) {
            return [];
        }

        $heat = max(0, min(5, (int) $state->get('heat', 0)));
        $pressure = max(0, min(5, (int) $state->get('pressure', 0)));
        $axisTag = $axes['roots'] . '-' . $axes['method'] . '-' . $axes['bond'];
        $offset = (int) ((crc32($axisTag) + ($heat * 17) + ($pressure * 31)) % count($jobs));
        $profiles = $this->followupProfiles($axes);
        if ($heat >= 4) {
            $profiles[0]['name'] = 'Cooling Burn Sweep';
            $profiles[0]['brief'] = 'Lower-signature cleanup run while half the city is watching your wake.';
            $profiles[0]['complication'] = 'Any loud move spikes heat into open pursuit.';
        }
        if ($pressure >= 4) {
            $profiles[2]['name'] = 'Deadline Debt Sprint';
            $profiles[2]['brief'] = 'Two clocks, one carrier, no spare route if timing slips.';
            $profiles[2]['complication'] = 'Delay penalties compound every tick after first miss.';
        }

        $packets = [];
        foreach ($profiles as $idx => $profile) {
            $job = $jobs[($offset + $idx) % count($jobs)];
            $basePayout = max($job->payoutEddies, (int) ($profile['payout'] ?? $job->payoutEddies));
            $baseRep = max($job->streetCredReward, (int) ($profile['rep'] ?? $job->streetCredReward));
            $payout = max(1200, $basePayout - ($heat * 120) + ($pressure * 80));
            $rep = max(1, $baseRep + (int) floor($pressure / 2));
            $packets[] = [
                'id' => 'fup.' . ($idx + 1),
                'number' => $idx + 1,
                'job_id' => $job->id,
                'name' => (string) ($profile['name'] ?? $job->name),
                'fixer' => (string) ($profile['fixer'] ?? 'Unknown Voice'),
                'payout' => $payout,
                'rep' => $rep,
                'brief' => (string) ($profile['brief'] ?? $job->briefing),
                'stakes' => (string) ($profile['stakes'] ?? $job->stakes),
                'complication' => (string) ($profile['complication'] ?? $job->complication) . ' [heat ' . $heat . '/5 pressure ' . $pressure . '/5]',
            ];
        }

        $state->set('followup_packets', $packets);
        return $packets;
    }

    /** @param array{roots:string,method:string,bond:string} $axes @return array<int,array<string,mixed>> */
    private function followupProfiles(array $axes): array
    {
        $rootsName = match ($axes['roots']) {
            'coast' => 'Tide Relay Ghost',
            'road' => 'Convoy Echo Lift',
            'corporate' => 'Tower Ghost Ledger',
            default => 'Watson Ghost Relay',
        };
        $rootsBrief = match ($axes['roots']) {
            'coast' => 'Dockside relay handoff with half-burned manifests and no customs trail.',
            'road' => 'Hijack a moving ledger before convoy routing rotates at dawn.',
            'corporate' => 'Pull a dead badge trail before tower auditors scrub the floor.',
            default => 'Lift a neighborhood relay key before the block goes dark.',
        };

        $methodName = match ($axes['method']) {
            'loud' => 'Hammerline Breach',
            'chair' => 'Chairline Mirage',
            default => 'Quietline Cut',
        };
        $methodBrief = match ($axes['method']) {
            'loud' => 'Forced entry on a guarded node. fast and obvious.',
            'chair' => 'Chair-only extraction with thin latency budget and no field rerolls.',
            default => 'Silent splice through two watchers and one bad camera angle.',
        };

        $bondName = $axes['bond'] === 'solo' ? 'Solo Debt Sweep' : 'Crew Debt Sweep';
        $bondBrief = $axes['bond'] === 'solo'
            ? 'Personal pickup. no backup, no witness, no retries.'
            : 'Shared pickup with split liability and three synchronized exits.';

        return [
            [
                'name' => $rootsName,
                'fixer' => 'Unknown Voice',
                'brief' => $rootsBrief,
                'stakes' => 'If missed, your first shard trail collapses.',
                'complication' => 'Packet metadata rewrites every six minutes.',
                'payout' => 4200,
                'rep' => 1,
            ],
            [
                'name' => $methodName,
                'fixer' => 'Unknown Voice',
                'brief' => $methodBrief,
                'stakes' => 'Method becomes your signature if this goes sideways.',
                'complication' => 'Counter-team expects your preferred style.',
                'payout' => 4800,
                'rep' => 2,
            ],
            [
                'name' => $bondName,
                'fixer' => 'Unknown Voice',
                'brief' => $bondBrief,
                'stakes' => 'This decides who owns the next wake.',
                'complication' => 'Someone in channel knows your intake answers.',
                'payout' => 5200,
                'rep' => 2,
            ],
        ];
    }

    /** @return array<string,mixed>|null */
    private function followupPacketFromCommand(TerminalState $state, string $command): ?array
    {
        $token = trim((string) preg_replace('/^(inspect|run) contract\s*/', '', $command));
        if ($token === '') {
            $selected = $state->get('selected_followup_contract');
            $token = is_string($selected) ? $selected : '1';
        }

        $packets = $this->buildFollowupPackets($state, $this->lifeAxes($state));
        if (ctype_digit($token)) {
            return $packets[((int) $token) - 1] ?? null;
        }
        foreach ($packets as $packet) {
            if (!is_array($packet)) {
                continue;
            }
            if ((string) ($packet['id'] ?? '') === $token) {
                return $packet;
            }
        }
        return null;
    }

    /** @return array{roots:string,method:string,bond:string} */
    private function lifeAxes(TerminalState $state): array
    {
        $stored = $state->get('life_axes');
        if (is_array($stored) && isset($stored['roots'], $stored['method'], $stored['bond'])) {
            return [
                'roots' => (string) $stored['roots'],
                'method' => (string) $stored['method'],
                'bond' => (string) $stored['bond'],
            ];
        }

        $answers = $state->get('intake_answers', []);
        $answers = is_array($answers) ? array_map('strval', $answers) : [];
        $blob = strtolower(implode(' ', $answers));
        $first = strtolower((string) ($answers[0] ?? ''));
        $third = strtolower((string) ($answers[2] ?? $blob));
        $fourth = strtolower((string) ($answers[3] ?? $blob));

        $roots = match (true) {
            preg_match('/coast|sea|harbor|port|bay/', $first) === 1 => 'coast',
            preg_match('/road|nomad|clan|convoy|drift/', $first) === 1 => 'road',
            preg_match('/corp|tower|office|board/', $first) === 1 => 'corporate',
            default => 'local',
        };

        $method = match (true) {
            preg_match('/quiet|sneak|ghost|silent|stealth/', $third) === 1 => 'quiet',
            preg_match('/chair|net|deck|hack|code/', $third) === 1 => 'chair',
            preg_match('/loud|gun|fight|blade|hit|breach/', $third) === 1 => 'loud',
            default => 'quiet',
        };

        $bond = match (true) {
            preg_match('/alone|solo|myself|nobody|no one|dont trust|don\'t trust/', $fourth . ' ' . $blob) === 1 => 'solo',
            default => 'crew',
        };

        return ['roots' => $roots, 'method' => $method, 'bond' => $bond];
    }

    /** @param array{roots:string,method:string,bond:string} $axes @return array{eddies:int,rep:int} */
    private function axisRunBonus(array $axes, bool $success): array
    {
        if (!$success) {
            return ['eddies' => 0, 'rep' => 0];
        }

        $eddies = match ($axes['method']) {
            'loud' => 300,
            'chair' => 150,
            default => 0,
        };
        $rep = ($axes['method'] === 'quiet' ? 1 : 0) + ($axes['bond'] === 'crew' ? 1 : 0);

        return ['eddies' => $eddies, 'rep' => $rep];
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function runMethodLine(array $axes): string
    {
        return match ($axes['method']) {
            'loud' => 'TILDE> you solved it with noise. city heard enough.',
            'chair' => 'TILDE> you solved it from the chair. clean fingers.',
            default => 'TILDE> you solved it quiet. cameras never quite caught up.',
        };
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function wakePlaceLine(array $axes): string
    {
        return match ($axes['roots']) {
            'coast' => 'WAKE> coast-weather in your bones, even this far inland.',
            'road' => 'WAKE> road habits show. you packed before the adrenaline died.',
            'corporate' => 'WAKE> tower manners still there. you inventory fear like assets.',
            default => 'WAKE> watson hum under the floor. familiar, never safe.',
        };
    }

    /** @param array{roots:string,method:string,bond:string} $axes @return string[] */
    private function wakeBondLines(array $axes): array
    {
        if ($axes['bond'] === 'solo') {
            return ['> nobody crowds your silence.'];
        }
        return [
            'KOJO> next time i want two seconds more on that loop.',
            'ANIMAL> see? better with witnesses.',
            '> booth noise covers the shaking in your hands.',
        ];
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function wakeMethodLine(array $axes): string
    {
        return match ($axes['method']) {
            'loud' => 'TILDE> loud works. until it does not.',
            'chair' => 'TILDE> chairwork bought us seconds. seconds bought us life.',
            default => 'TILDE> quiet kept us breathing tonight.',
        };
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function shardType(array $axes): string
    {
        return match ($axes['method']) {
            'chair' => 'intercepted audio splice',
            'loud' => 'personal effects inventory card',
            default => 'intercepted routing memo',
        };
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function shardTargetHint(array $axes): string
    {
        return match ($axes['roots']) {
            'coast' => 'recipient [redacted] / coastal relay alias',
            'road' => 'recipient [redacted] / transit convoy marker',
            'corporate' => 'recipient [redacted] / tower badge fragment',
            default => 'recipient [redacted] / watson neighborhood tag',
        };
    }

    /** @param array{roots:string,method:string,bond:string} $axes */
    private function shardMemo(array $axes): string
    {
        $voice = $axes['bond'] === 'solo'
            ? 'YOU CARRY THIS ALONE. KEEP MOVING.'
            : 'YOU DO NOT CARRY THIS ALONE. KEEP THEM CLOSE.';
        return 'CAGE EMPTY BY DESIGN. LIVE SIGNAL CONFIRMED. ' . $voice;
    }

    /** @return array{heat:int,pressure:int,delta_heat:int,delta_pressure:int} */
    private function updateRiskTrack(TerminalState $state, bool $success, string $method): array
    {
        $heat = max(0, min(5, (int) $state->get('heat', 0)));
        $pressure = max(0, min(5, (int) $state->get('pressure', 0)));

        $deltaHeat = $success ? -1 : 1;
        $deltaPressure = $success ? 1 : 2;
        if ($method === 'loud') {
            $deltaHeat += 1;
        } elseif ($method === 'quiet') {
            $deltaHeat -= 1;
        } elseif ($method === 'chair') {
            $deltaPressure += 1;
        }

        $heat = max(0, min(5, $heat + $deltaHeat));
        $pressure = max(0, min(5, $pressure + $deltaPressure));
        $state->set('heat', $heat);
        $state->set('pressure', $pressure);
        $state->set('followup_packets', []);

        return [
            'heat' => $heat,
            'pressure' => $pressure,
            'delta_heat' => $deltaHeat,
            'delta_pressure' => $deltaPressure,
        ];
    }

    private function openPlayWakeRiskLine(int $heat, int $pressure): string
    {
        if ($heat >= 4 && $pressure >= 4) {
            return 'WAKE> too hot and too late. every call sounds like a trap.';
        }
        if ($heat >= 4) {
            return 'WAKE> heat spiking. faces on the street keep looking twice.';
        }
        if ($pressure >= 4) {
            return 'WAKE> pressure stacking. deadlines arrive faster than sleep.';
        }
        if ($heat <= 1 && $pressure <= 2) {
            return 'WAKE> low-signature window. you can still choose your ground.';
        }
        return 'WAKE> city still tolerating you, for now.';
    }

    /**
     * @return array{
     *   label:string,
     *   payout_delta:int,
     *   rep_delta:int,
     *   run_line:string,
     *   wake_lines:array<int,string>
     * }
     */
    private function crewDriftEffect(int $heat, int $pressure, string $bond, bool $success, bool $isEpisodeContract): array
    {
        if ($isEpisodeContract) {
            return [
                'label' => 'intro discipline',
                'payout_delta' => 0,
                'rep_delta' => 0,
                'run_line' => '',
                'wake_lines' => [],
            ];
        }

        if (!$success && $pressure >= 4) {
            return [
                'label' => 'fracture',
                'payout_delta' => 0,
                'rep_delta' => -1,
                'run_line' => 'TILDE> channel buckled. everyone talked, nobody listened.',
                'wake_lines' => [
                    'WAKE> voices overlap until the room goes quiet on purpose.',
                    'ANIMAL> next run gets one caller, not five.',
                ],
            ];
        }

        if ($pressure >= 4 && $heat >= 3) {
            return [
                'label' => 'tight-frayed',
                'payout_delta' => -450,
                'rep_delta' => -1,
                'run_line' => 'TILDE> timing held, trust did not.',
                'wake_lines' => [
                    'WAKE> every chair creaks like an accusation.',
                    'KOJO> we still breathing. that is the whole win tonight.',
                ],
            ];
        }

        if ($pressure >= 4) {
            return [
                'label' => 'friction',
                'payout_delta' => -300,
                'rep_delta' => 0,
                'run_line' => 'ANIMAL> pressure made the crew sharp and mean.',
                'wake_lines' => [
                    'WAKE> short tempers, short sentences, fast exits.',
                ],
            ];
        }

        if ($heat <= 1 && $pressure <= 2) {
            return [
                'label' => 'sync',
                'payout_delta' => 250,
                'rep_delta' => 1,
                'run_line' => 'TILDE> channel synced clean. no wasted words.',
                'wake_lines' => [
                    'WAKE> clean rhythm in the booth. everyone lands on the same beat.',
                ],
            ];
        }

        if ($bond === 'solo' && $heat >= 4) {
            return [
                'label' => 'isolation drag',
                'payout_delta' => -200,
                'rep_delta' => 0,
                'run_line' => 'VOICE> running solo while hot costs more every block.',
                'wake_lines' => [
                    'WAKE> one set of footsteps in the hall, none coming back.',
                ],
            ];
        }

        return [
            'label' => 'steady',
            'payout_delta' => 0,
            'rep_delta' => 0,
            'run_line' => '',
            'wake_lines' => [],
        ];
    }
}
