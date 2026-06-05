<?php
declare(strict_types=1);

namespace LastJob;

use LastJob\Lifepath\Character;
use LastJob\Netrun\NetrunEngine;
use LastJob\Netrun\Netrunner;

/**
 * The core loop: crew + job -> run -> aftermath. This is the state machine that
 * ties the slices together. Crew members resolve on-site obstacles via skill
 * checks while the netrunner descends the NET; both burn the same mission clock.
 * The aftermath pays out, moves street cred, and surfaces any hidden agendas
 * whose trigger conditions fired this run.
 *
 * Fully deterministic: one Rng drives every check and the netrun, so a given
 * (crew, job, seed) reproduces the same after-action report.
 */
final class JobRunner
{
    public function __construct(
        private Rules $rules,
    ) {
    }

    /**
     * @param Character[] $crew
     * @return array<string,mixed> after-action report
     */
    public function run(array $crew, Job $job, Rng $rng, Economy $economy): array
    {
        $clock = new MissionClock($job->difficultyTicks);
        $skill = new SkillCheck(new Dice($rng));

        $beats = [];
        $obstaclesFailed = 0;
        $obstaclesPassed = 0;
        $timeRanOut = false;

        foreach ($job->obstacles as $ob) {
            if ($clock->expired()) {
                $timeRanOut = true;
                break;
            }
            $member = $this->bestFor($crew, (string) $ob['stat'], (string) ($ob['role_pref'] ?? ''));
            $statVal = (int) ($member->stats[(string) $ob['stat']] ?? 0);
            $roleBonus = ($member->roleName === ($ob['role_pref'] ?? null)) ? 3 : 1;
            $base = $statVal + $roleBonus;
            $res = $skill->check($base, (int) $ob['dv']);
            $clock->tick((int) ($ob['ticks'] ?? 1));

            if ($res['success']) {
                $obstaclesPassed++;
            } else {
                $obstaclesFailed++;
            }

            $beats[] = [
                'obstacle' => (string) $ob['name'],
                'member' => $member->handle,
                'role' => $member->roleName,
                'stat' => (string) $ob['stat'],
                'roll' => $res['roll'],
                'crit' => $res['crit'],
                'total' => $res['total'],
                'dv' => $res['dv'],
                'success' => $res['success'],
                'clock_remaining' => $clock->remaining(),
            ];
        }

        // Netrunner descends the NET (same Rng -> same determinism).
        $netMember = $this->firstRole($crew, 'Netrunner');
        $runner = Netrunner::default($netMember?->handle ?? 'Glitch');
        $arch = $this->rules->architecture($job->archId);
        $net = (new NetrunEngine($this->rules, $rng))->run($arch, $runner);
        $clock->tick((int) ($net['rounds'] ?? 0));

        $netSuccess = $net['outcome'] === NetrunEngine::OUT_SUCCESS;
        $netFlatline = in_array($net['outcome'], [NetrunEngine::OUT_FAIL_FLATLINE, NetrunEngine::OUT_DEAD], true);
        $crewDied = $net['outcome'] === NetrunEngine::OUT_DEAD;

        if ($clock->expired() && !$netSuccess) {
            $timeRanOut = true;
        }

        // Mission success: netrun pulled the data, the crew held the ground
        // (more obstacles passed than failed), and the clock didn't blow.
        $success = $netSuccess && ($obstaclesPassed >= $obstaclesFailed) && !$timeRanOut;

        $payout = 0;
        $credGain = 0;
        if ($success) {
            $payout = $job->payoutEddies + (int) ($net['eddies'] ?? 0);
            $credGain = $job->streetCredReward;
            $economy->payout($payout, $credGain);
        }

        // Fired conditions for hidden-agenda evaluation.
        $fired = [];
        $fired[] = $success ? 'run_success' : 'run_failed';
        if ($success && $job->isBigPayout()) {
            $fired[] = 'big_payout';
        }
        if ($job->hasTag('corp')) {
            $fired[] = 'corp_job';
        }
        if ($obstaclesFailed > 0 || $netFlatline) {
            $fired[] = 'crew_wounded';
        }
        if ($job->hasTag('enemy')) {
            $fired[] = 'enemy_present';
        }

        $triggered = $this->evaluateAgendas($crew, $fired);

        return [
            'job' => $job->id,
            'job_name' => $job->name,
            'fixer' => $job->fixer,
            'success' => $success,
            'debrief' => $success ? $job->successDebrief : $job->failureDebrief,
            'time_ran_out' => $timeRanOut,
            'crew_member_died' => $crewDied,
            'obstacles_passed' => $obstaclesPassed,
            'obstacles_failed' => $obstaclesFailed,
            'beats' => $beats,
            'netrun' => [
                'outcome' => $net['outcome'],
                'eddies' => $net['eddies'] ?? 0,
                'rounds' => $net['rounds'] ?? 0,
                'floors_cleared' => $net['floors_cleared'] ?? 0,
                'deck_hp_remaining' => $net['deck_hp_remaining'] ?? 0,
            ],
            'clock' => ['total' => $clock->total(), 'spent' => $clock->spent(), 'remaining' => $clock->remaining()],
            'payout_eddies' => $payout,
            'street_cred_gained' => $credGain,
            'economy' => $economy->toArray(),
            'fired_conditions' => $fired,
            'agendas_triggered' => $triggered,
        ];
    }

    /**
     * Reveal any hidden agendas whose trigger condition fired. Until a trigger
     * fires the agenda stays sealed; this is the only sanctioned way it surfaces.
     *
     * @param Character[] $crew
     * @param string[] $fired
     * @return array<int,array<string,mixed>>
     */
    private function evaluateAgendas(array $crew, array $fired): array
    {
        $out = [];
        foreach ($crew as $member) {
            $agenda = $member->sealedAgenda();
            $on = (string) ($agenda['trigger_on'] ?? 'never');
            $fires = $on === 'always' || ($on !== 'never' && in_array($on, $fired, true));
            if (!$fires) {
                continue;
            }
            $out[] = [
                'member' => $member->handle,
                'role' => $member->roleName,
                'type' => $agenda['type'] ?? null,
                'agenda' => $agenda['result'] ?? null,
                'trigger_on' => $on,
                'consequence' => $agenda['consequence'] ?? null,
            ];
        }
        return $out;
    }

    /**
     * @param Character[] $crew
     */
    private function bestFor(array $crew, string $stat, string $rolePref): Character
    {
        $best = $crew[0];
        $bestScore = -999;
        foreach ($crew as $m) {
            $score = (int) ($m->stats[$stat] ?? 0) + ($m->roleName === $rolePref ? 3 : 0);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $m;
            }
        }
        return $best;
    }

    /**
     * @param Character[] $crew
     */
    private function firstRole(array $crew, string $roleName): ?Character
    {
        foreach ($crew as $m) {
            if ($m->roleName === $roleName) {
                return $m;
            }
        }
        return null;
    }
}
