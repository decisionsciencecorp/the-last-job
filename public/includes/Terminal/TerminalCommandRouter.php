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
    public function __construct(
        private Rules $rules,
    ) {
    }

    /** @return array<string,mixed> */
    public function handle(TerminalState $state, string $rawCommand): array
    {
        $command = $this->normalize($rawCommand);
        if ($command === '') {
            return $this->response($state, '', ['type `help` if the line goes quiet.']);
        }

        $lines = match (true) {
            in_array($command, ['help', '?'], true) => $this->help(),
            in_array($command, ['boot', 'wake deck', 'reset'], true) => $this->boot($state, $command),
            in_array($command, ['answer', 'take call', 'run take-call'], true) => $this->answer($state),
            in_array($command, ['let it ring', 'run let-it-ring'], true) => $this->letItRing($state),
            in_array($command, ['ask fixer crew', 'request crew files', 'open fixer.roster', 'show dossiers'], true) => $this->crew($state),
            in_array($command, ['list contracts', 'contracts', 'list contract packets'], true) => $this->contracts($state),
            str_starts_with($command, 'inspect contract') => $this->inspectContract($state, $command),
            str_starts_with($command, 'run contract') => $this->runContract($state, $command),
            in_array($command, ['wake', 'show wake'], true) => $this->wake($state),
            in_array($command, ['file', 'open file', 'show file'], true) => $this->file(),
            in_array($command, ['status', 'whoami'], true) => $this->status($state),
            default => [
                "unknown command: {$rawCommand}",
                'try: answer | ask fixer crew | list contracts | inspect contract 1 | run contract 1 | wake | file',
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
            '  ask fixer crew        receive fixer-vouched dossier files',
            '  list contracts        list available contract packets',
            '  inspect contract 1    read packet details',
            '  run contract 1        jack in with current crew',
            '  wake                  read the last aftermath',
            '  file                  open the shard wall',
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
            '[01.144] rule: no crew contact until the fixer makes the intro.',
            '[01.388] deck ready. city breathing behind the glass.',
        ];
    }

    /** @return string[] */
    private function answer(TerminalState $state): array
    {
        $state->set('answered', true);
        return [
            'LINK encrypted relay / watson-relay-3',
            'ANIMAL > you free, or just standing around the booth?',
            'YOU    > depends who is asking.',
            'ANIMAL > someone with a job and enough shame to use a middleman.',
            'ANIMAL > you want bodies, you ask me. nobody gets your direct line yet.',
            'next: `ask fixer crew` or `list contracts`',
        ];
    }

    /** @return string[] */
    private function letItRing(TerminalState $state): array
    {
        $state->set('answered', false);
        return [
            'RING line dropped.',
            'rain fills the channel for four seconds.',
            'ANIMAL leaves one text: "city charges extra for hesitation."',
            'next: `answer`',
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
    private function runContract(TerminalState $state, string $command): array
    {
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

        $lines = [
            "--- run {$job->id} ---",
            'crew channel opened by ANIMAL. nobody has your direct line.',
            !empty($report['success']) ? 'RESULT clean enough to get paid.' : 'RESULT burned. somebody is bleeding or lying.',
            'clock: ' . (int) ($report['clock']['spent'] ?? 0) . '/' . (int) ($report['clock']['total'] ?? 0) . ' ticks',
            'net: ' . (string) ($report['netrun']['outcome'] ?? 'unknown'),
            'payout: ' . (int) ($report['payout_eddies'] ?? 0) . 'eb',
            'rep: +' . (int) ($report['street_cred_gained'] ?? 0),
        ];
        if (!empty($report['debrief'])) {
            $lines[] = 'wake: ' . (string) $report['debrief'];
        }
        $lines[] = 'next: `wake` or `file`';
        return $lines;
    }

    /** @return string[] */
    private function wake(TerminalState $state): array
    {
        $report = $state->get('last_report');
        if (!is_array($report)) {
            return ['no wake yet. run a contract first.'];
        }

        $lines = [
            '--- wake.after_action ---',
            'job: ' . (string) ($report['job_name'] ?? 'unknown'),
            !empty($report['success']) ? 'status: paid' : 'status: burned',
            'debrief: ' . (string) (($report['debrief'] ?? '') ?: 'nobody wants to talk about it yet.'),
        ];
        $agendas = is_array($report['agendas_triggered'] ?? null) ? $report['agendas_triggered'] : [];
        foreach ($agendas as $agenda) {
            if (is_array($agenda)) {
                $lines[] = 'pressure: ' . (string) ($agenda['member'] ?? 'crew') . ' / ' . (string) ($agenda['consequence'] ?? 'unreadable');
            }
        }
        return $lines;
    }

    /** @return string[] */
    private function file(): array
    {
        $dossier = new IntelDossier(dirname(__DIR__, 2) . '/data/story/intel_threads.json');
        $lines = ['--- shard.wall ---'];
        foreach ($dossier->threads() as $idx => $thread) {
            $lines[] = '[' . ($idx + 1) . '] ' . (string) $thread['title'];
            $lines[] = '    ' . (string) $thread['summary'];
        }
        return $lines;
    }

    /** @return string[] */
    private function status(TerminalState $state): array
    {
        return [
            'deck: awake',
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
}
