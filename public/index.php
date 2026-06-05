<?php
declare(strict_types=1);

/**
 * Dev subdomain and main site entry routing.
 * - dev.the-last-job.decisionsciencecorp.com -> dev blog
 * - apex -> game landing (UI slices later)
 */

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
if (str_starts_with($host, 'dev.')) {
    require __DIR__ . '/blog/router.php';
    exit;
}

require __DIR__ . '/includes/Layout.php';

use function LastJob\layout_header;
use function LastJob\layout_footer;

layout_header('The Deck', 'deck');
?>
<section class="deck-shell" aria-labelledby="deck-title">
    <p class="deck-kicker">Runner cyberdeck / first contact</p>
    <h1 id="deck-title" class="deck-title">The Deck</h1>

    <div class="flow-rail" aria-label="First-session flow">
        <div class="flow-step active">Boot Deck</div>
        <div class="flow-step ready">Take Call</div>
        <div class="flow-step">Answer Intake</div>
        <div class="flow-step">Read Contract</div>
        <div class="flow-step">Jack In</div>
    </div>

    <div class="deck-grid">
        <section class="deck-panel" aria-labelledby="deck-boot">
            <p class="deck-kicker" id="deck-boot">Deck status</p>
            <div class="terminal-lines">
                <p>&gt; deck online. checking the wire.</p>
                <p>&gt; 1 new call — fixer: ANIMAL</p>
            </div>
            <p class="lead">The city already sold you out. Take the line, read the contract, and decide who you trust least.</p>
            <div class="deck-cta-row">
                <a class="deck-cta" href="/play.php?wire=call">Take Call</a>
                <a class="deck-cta secondary" href="/play.php?wire=ring">Let It Ring</a>
            </div>
            <p class="context-line">Context line: calls are text comms in the Wire. No audio pipeline, no generated voice files, no dashboard setup.</p>
        </section>

        <section class="wire-panel" aria-labelledby="wire-preview">
            <p class="deck-kicker" id="wire-preview">The Wire / active line</p>
            <div class="wire-status">
                <span>signal: encrypted</span>
                <span>carrier: watson-relay-3</span>
                <span>ambient: rain / jukebox</span>
            </div>
            <div class="waveform" aria-hidden="true"></div>
            <div class="wire-log">
                <p><strong>ANIMAL</strong> &gt; you free, or just standing around the booth?</p>
                <p><strong>ANIMAL</strong> &gt; got a mid-level job on the waterfront.</p>
                <p><strong>ANIMAL</strong> &gt; bring a runner or get loud.</p>
            </div>
            <div class="wire-contract-teaser">
                <strong>Contract teaser: NCART waterfront / 8k / heat low</strong>
                <span class="muted">Next: open the contract packet, not a table of rows.</span>
            </div>
        </section>
    </div>
</section>
<?php layout_footer(); ?>
