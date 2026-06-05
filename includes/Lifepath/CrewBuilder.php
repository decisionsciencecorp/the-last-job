<?php
declare(strict_types=1);

namespace LastJob\Lifepath;

use LastJob\Rng;
use LastJob\Rules;

/**
 * Builds a full crew deterministically from one seed. Default crew is the v1
 * core four: Solo, Netrunner, Tech, Fixer.
 */
final class CrewBuilder
{
    public const DEFAULT_ROLES = ['role.solo', 'role.netrunner', 'role.tech', 'role.fixer'];

    public function __construct(
        private Rules $rules,
        private Rng $rng,
    ) {
    }

    /**
     * @param string[] $roleIds
     * @return Character[]
     */
    public function build(array $roleIds = self::DEFAULT_ROLES): array
    {
        $gen = new CharacterGenerator($this->rules, $this->rng);
        $crew = [];
        foreach ($roleIds as $roleId) {
            $crew[] = $gen->generate($roleId);
        }
        return $this->dedupeHandles($crew);
    }

    /**
     * @param Character[] $crew
     * @return Character[]
     */
    private function dedupeHandles(array $crew): array
    {
        $seen = [];
        foreach ($crew as $member) {
            $base = $member->handle;
            $n = 2;
            while (isset($seen[$member->handle])) {
                $member->handle = $base . '-' . $n;
                $n++;
            }
            $seen[$member->handle] = true;
        }
        return $crew;
    }
}
