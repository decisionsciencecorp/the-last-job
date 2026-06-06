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

use function LastJob\layout_h;

$bootLines = [
    '[00.000] power reroute: motel wall jack / unstable',
    '[00.217] loading city map: watson, heywood, combat zone fragments',
    '[00.409] warning: corporate mesh watching public grids',
    '[00.633] fixer relay found: ANIMAL / encrypted / paid in advance',
    '[00.901] context: you are not browsing jobs. you are answering a line.',
    '[01.144] rule: no crew contact until the fixer makes the intro.',
    '[01.388] deck ready. city breathing behind the glass.',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Last Job — Deck Terminal</title>
    <link rel="stylesheet" href="/blog/assets/style.css">
    <link rel="stylesheet" href="/assets/game.css">
</head>
<body class="terminal-app-body">
<main class="terminal-app" aria-label="The Last Job terminal application">
    <header class="terminal-app-top">
        <div>
            <strong>./lastjob</strong>
            <span>tty://night-city/localdeck</span>
        </div>
        <button type="button" data-terminal-command="help">help</button>
    </header>

    <section class="terminal-output" id="terminal-output" aria-live="polite" aria-label="Terminal scrollback">
        <div class="terminal-block system">
            <?php foreach ($bootLines as $line): ?>
                <p><?= layout_h($line) ?></p>
            <?php endforeach; ?>
        </div>
        <div class="terminal-block">
            <p><span class="system-ok">login:</span> runner</p>
            <p>Night City does not hand you a menu. It opens a channel, names a price, and waits for you to make the first mistake.</p>
            <p><span class="system-warn">RING</span> ANIMAL wants to assemble a crew through you.</p>
                <p><span class="system-dim">NOTE</span> one action at a time: <code>answer</code> or <code>let it ring</code>.</p>
        </div>
    </section>

    <section class="terminal-command-bar" aria-label="Command suggestions">
        <button type="button" data-terminal-command="answer">answer</button>
            <button type="button" data-terminal-command="let it ring">let it ring</button>
            <button type="button" data-terminal-command="help">help</button>
            <button type="button" data-terminal-command="status">status</button>
            <button type="button" data-terminal-command="reset">reset</button>
    </section>

    <form class="terminal-input-row" id="terminal-form" autocomplete="off">
        <label for="terminal-command">$</label>
        <input id="terminal-command" name="command" type="text" inputmode="text" spellcheck="false" autofocus value="answer">
        <button type="submit">send</button>
    </form>
</main>

<script>
(() => {
    const output = document.getElementById('terminal-output');
    const form = document.getElementById('terminal-form');
    const input = document.getElementById('terminal-command');
    const commandButtons = Array.from(document.querySelectorAll('[data-terminal-command]'));

    function appendBlock(lines, className = '') {
        const block = document.createElement('div');
        block.className = `terminal-block ${className}`.trim();
        lines.forEach((line) => {
            const p = document.createElement('p');
            p.textContent = line;
            block.appendChild(p);
        });
        output.appendChild(block);
        output.scrollTop = output.scrollHeight;
    }

    function setSuggestions(suggestions) {
        if (!Array.isArray(suggestions) || suggestions.length === 0) {
            return;
        }
        commandButtons.forEach((button, index) => {
            if (suggestions[index]) {
                button.textContent = suggestions[index];
                button.dataset.terminalCommand = suggestions[index];
                button.hidden = false;
            } else {
                button.hidden = true;
            }
        });
    }

    async function runCommand(command) {
        command = command.trim();
        if (!command) {
            return;
        }
        appendBlock([`$ ${command}`], 'input');
        input.value = '';

        try {
            const response = await fetch('/api/terminal-command.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({command}),
            });
            const payload = await response.json();
            if (payload.status !== 'ok') {
                appendBlock(payload.lines || [payload.error || 'terminal fault'], 'error');
                return;
            }
            appendBlock(payload.lines || [], 'response');
            setSuggestions(payload.suggestions || []);
        } catch (error) {
            appendBlock([`terminal fault: ${error && error.message ? error.message : 'unknown error'}`], 'error');
        } finally {
            input.focus();
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        runCommand(input.value);
    });

    commandButtons.forEach((button) => {
        button.addEventListener('click', () => runCommand(button.dataset.terminalCommand || button.textContent || 'help'));
    });
})();
</script>
</body>
</html>
