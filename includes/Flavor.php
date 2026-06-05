<?php
declare(strict_types=1);

namespace LastJob;

/**
 * Night City ambient layer: fixer one-liners, a news ticker, and district
 * ambiance. Pure presentation - it never touches outcomes. Picks are
 * deterministic when driven by a seeded Rng.
 */
final class Flavor
{
    /** @var array<int,array<string,mixed>> */
    private array $fixerQuotes;
    /** @var array<int,array<string,mixed>> */
    private array $news;
    /** @var array<int,array<string,mixed>> */
    private array $ambiance;

    public function __construct(Rules $rules)
    {
        $this->fixerQuotes = $rules->loadJson('flavor/fixer_quotes.json');
        $this->news = $rules->loadJson('flavor/news_ticker.json');
        $this->ambiance = $rules->loadJson('flavor/district_ambiance.json');
    }

    public function fixerQuote(Rng $rng): string
    {
        return (string) $this->pick($this->fixerQuotes, $rng)['text'];
    }

    public function newsHeadline(Rng $rng): string
    {
        return (string) $this->pick($this->news, $rng)['text'];
    }

    /**
     * @param string|null $district filter to a district when provided
     */
    public function ambiance(Rng $rng, ?string $district = null): string
    {
        $pool = $this->ambiance;
        if ($district !== null) {
            $filtered = array_values(array_filter(
                $pool,
                static fn ($a) => strcasecmp((string) ($a['district'] ?? ''), $district) === 0
            ));
            if ($filtered) {
                $pool = $filtered;
            }
        }
        return (string) $this->pick($pool, $rng)['text'];
    }

    /**
     * @param int $count number of distinct headlines for a ticker
     * @return string[]
     */
    public function newsTicker(Rng $rng, int $count = 3): array
    {
        $pool = $this->news;
        $out = [];
        $count = min($count, count($pool));
        $used = [];
        while (count($out) < $count) {
            $i = $rng->intRange(0, count($pool) - 1);
            if (isset($used[$i])) {
                continue;
            }
            $used[$i] = true;
            $out[] = (string) $pool[$i]['text'];
        }
        return $out;
    }

    /**
     * @param array<int,array<string,mixed>> $pool
     * @return array<string,mixed>
     */
    private function pick(array $pool, Rng $rng): array
    {
        if ($pool === []) {
            return ['text' => ''];
        }
        return $pool[$rng->intRange(0, count($pool) - 1)];
    }
}
