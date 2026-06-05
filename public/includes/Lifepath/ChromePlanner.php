<?php
declare(strict_types=1);

namespace LastJob\Lifepath;

use LastJob\Dice;
use LastJob\Humanity;
use LastJob\Rng;
use LastJob\Rules;
use Throwable;

/** Deterministic chrome load simulation for crew builder UI. */
final class ChromePlanner
{
    /**
     * @param string[] $wareIds
     * @return array{humanity: array<string,mixed>, log: array<int,array<string,mixed>>}
     */
    public function simulate(int $empStat, array $wareIds, Rules $rules, Rng $rng): array
    {
        $humanity = new Humanity(new Dice($rng), $empStat);
        $log = [];

        foreach ($wareIds as $id) {
            $id = trim($id);
            if ($id === '') {
                continue;
            }
            try {
                $log[] = $humanity->install($rules->cyberwareItem($id));
            } catch (Throwable $e) {
                $log[] = [
                    'id' => $id,
                    'error' => $e->getMessage(),
                    'humanity_after' => $humanity->toArray()['current_humanity'],
                    'status' => $humanity->toArray()['status'],
                ];
            }
        }

        return ['humanity' => $humanity->toArray(), 'log' => $log];
    }

    /** @return string[] */
    public static function parseSelection(?string $csv): array
    {
        if ($csv === null || $csv === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $csv)), static fn ($s) => $s !== ''));
    }
}
