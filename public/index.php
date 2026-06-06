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
<section class="terminal-screen" aria-labelledby="deck-title">
    <div class="terminal-bar">
        <span>tty://night-city/localdeck</span>
        <a href="#deck-ready">skip intro</a>
    </div>

    <pre class="boot-sequence" aria-label="Skippable intro boot sequence"><span data-boot-line>[00.000] power reroute: motel wall jack / unstable</span>
<span data-boot-line>[00.217] loading city map: watson, heywood, combat zone fragments</span>
<span data-boot-line>[00.409] warning: corporate mesh watching public grids</span>
<span data-boot-line>[00.633] fixer relay found: ANIMAL / encrypted / paid in advance</span>
<span data-boot-line>[00.901] context: you are not browsing jobs. you are answering a line.</span>
<span data-boot-line>[01.144] rule: no crew contact until the fixer makes the intro.</span>
<span data-boot-line>[01.388] deck ready. city breathing behind the glass.</span></pre>

    <div id="deck-ready" class="terminal-grid">
        <section class="terminal-pane terminal-pane-primary" aria-labelledby="deck-title">
            <p class="terminal-path">~/lastjob/deck</p>
            <h1 id="deck-title">login: runner</h1>
            <p class="terminal-copy">Night City does not hand you a menu. It opens a channel, names a price, and waits for you to make the first mistake.</p>

            <div class="terminal-log">
                <p><span class="prompt">$</span> wake deck</p>
                <p><span class="system-ok">OK</span> handshake with fixer relay</p>
                <p><span class="system-warn">RING</span> ANIMAL wants to assemble a crew through you</p>
                <p><span class="system-dim">NOTE</span> the team does not know you yet. the fixer does.</p>
            </div>

            <div class="terminal-actions" aria-label="First actions">
                <a class="terminal-command" href="/play.php?wire=call">run take-call</a>
                <a class="terminal-command secondary" href="/play.php?wire=ring">run let-it-ring</a>
                <a class="terminal-command secondary" href="/crew.php?via=fixer">open fixer.roster</a>
            </div>
        </section>

        <aside class="terminal-pane" aria-labelledby="intro-title">
            <p class="terminal-path">incoming/world.txt</p>
            <h2 id="intro-title">before you answer</h2>
            <div class="terminal-log">
                <p>Arasaka still casts a shadow even when nobody says the name.</p>
                <p>Militech buys futures by killing the people who remember alternatives.</p>
                <p>Your fixer is not your friend. Your fixer is the only reason the crew will pick up.</p>
                <p>The last job is not available yet. The city has to teach you what it costs first.</p>
            </div>
        </aside>
    </div>
</section>
<script>
(() => {
    const lines = document.querySelectorAll('[data-boot-line]');
    lines.forEach((line, index) => {
        line.style.animationDelay = `${index * 220}ms`;
    });
})();
</script>
<?php layout_footer(); ?>
