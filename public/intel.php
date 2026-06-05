<?php
declare(strict_types=1);

require __DIR__ . '/includes/autoload.php';
require __DIR__ . '/includes/Layout.php';

use LastJob\Story\IntelDossier;
use function LastJob\layout_header;
use function LastJob\layout_footer;
use function LastJob\layout_h;

$dossier = new IntelDossier(__DIR__ . '/data/story/intel_threads.json');
$threads = $dossier->threads();

layout_header('Intel dossier', 'intel');
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Campaign Dossier</p>
        <h1>Everything points at the Tower.</h1>
        <p class="lead">The crew is not chasing disconnected gigs anymore. Every shard, extraction, black-ICE corpse, and half-heard broadcast is part of a pattern the corps are killing to keep unfinished.</p>
    </div>
    <div class="dossier-callout">
        <strong>Working theory</strong>
        <p>Arasaka is teaching dead minds to wear living faces. Militech is pricing tomorrow's casualties. The Last Job is where both trails stop being rumors.</p>
    </div>
</section>

<section class="intel-grid" aria-label="Active intel threads">
    <?php foreach ($threads as $thread): ?>
        <article class="intel-card" id="<?= layout_h((string) $thread['id']) ?>">
            <div class="meta-row">
                <span><?= layout_h((string) ($thread['status'] ?? 'unknown')) ?></span>
                <span><?= layout_h((string) ($thread['threat'] ?? 'unknown')) ?></span>
            </div>
            <h2><?= layout_h((string) $thread['title']) ?></h2>
            <p><?= layout_h((string) $thread['summary']) ?></p>

            <?php $evidence = $thread['evidence'] ?? []; ?>
            <?php if (is_array($evidence) && $evidence !== []): ?>
                <h3>Evidence</h3>
                <ul>
                    <?php foreach ($evidence as $item): ?>
                        <li><?= layout_h((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($thread['question'])): ?>
                <p class="intel-question"><?= layout_h((string) $thread['question']) ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<?php layout_footer(); ?>
