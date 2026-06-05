<?php
declare(strict_types=1);

require __DIR__ . '/includes/autoload.php';
require __DIR__ . '/includes/Layout.php';

use LastJob\Rng;
use LastJob\Rules;
use LastJob\Lifepath\CrewBuilder;
use LastJob\Lifepath\ChromePlanner;
use function LastJob\layout_header;
use function LastJob\layout_footer;
use function LastJob\layout_h;

$rules = new Rules();
$seed = isset($_GET['seed']) ? (int) $_GET['seed'] : 2077;
$roll = isset($_GET['roll']);
$campaign = isset($_GET['campaign']) ? (int) $_GET['campaign'] : 1;
$streetCred = isset($_GET['street_cred']) ? max(0, (int) $_GET['street_cred']) : 4;
$rolesCatalog = [];
foreach ($rules->roles() as $idx => $role) {
    $rolesCatalog[(string) ($role['id'] ?? $idx)] = $role;
}
$roleIds = array_keys($rolesCatalog);
$defaultRoles = CrewBuilder::DEFAULT_ROLES;
$allCyber = $rules->allCyberware();

$rolePick = [];
for ($i = 0; $i < 4; $i++) {
    $key = 'role' . $i;
    $picked = isset($_GET[$key]) ? (string) $_GET[$key] : ($defaultRoles[$i] ?? $roleIds[$i % count($roleIds)]);
    if (!isset($rolesCatalog[$picked])) {
        $picked = $defaultRoles[$i] ?? $roleIds[0];
    }
    $rolePick[] = $picked;
}

$chromePick = [];
for ($i = 0; $i < 4; $i++) {
    $key = 'chrome' . $i;
    if (isset($_GET[$key]) && is_array($_GET[$key])) {
        $chromePick[$i] = array_values(array_filter(array_map('strval', $_GET[$key]), static fn ($s) => $s !== ''));
    } else {
        $chromePick[$i] = ChromePlanner::parseSelection(isset($_GET[$key]) ? (string) $_GET[$key] : null);
    }
}

$crew = (new CrewBuilder($rules, new Rng($seed)))->build($rolePick);

layout_header('Build your crew', 'crew');
?>
<h1>Build your crew</h1>
<p class="lead">Roll lifepaths, pick roles, and load chrome — humanity and EMP update deterministically from the same seed.</p>

<form class="form-grid" method="get">
    <input type="hidden" name="campaign" value="<?= $campaign === 0 ? 0 : 1 ?>">
    <input type="hidden" name="street_cred" value="<?= (int) $streetCred ?>">
    <label>Seed
        <input type="number" name="seed" value="<?= layout_h((string) $seed) ?>" min="1">
    </label>

    <div class="field-label">Roles (4 slots)</div>
    <?php for ($i = 0; $i < 4; $i++): ?>
        <label>Slot <?= $i + 1 ?>
            <select name="role<?= $i ?>">
                <?php foreach ($rolesCatalog as $id => $row): ?>
                    <option value="<?= layout_h($id) ?>"<?= $rolePick[$i] === $id ? ' selected' : '' ?>><?= layout_h((string) ($row['name'] ?? $id)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endfor; ?>

    <button type="submit" name="roll" value="1">Roll crew</button>
</form>

<?php if ($crew !== null): ?>
    <?php
    $playParams = ['seed' => $seed, 'campaign' => $campaign === 0 ? 0 : 1, 'street_cred' => $streetCred];
    for ($i = 0; $i < 4; $i++) {
        $playParams['role' . $i] = $rolePick[$i];
    }
    ?>
    <div class="actions-row">
        <a class="btn" href="/play.php?<?= layout_h(http_build_query($playParams + ['run' => 1])) ?>">Run job with this crew</a>
        <a class="btn btn-secondary" href="/play.php?<?= layout_h(http_build_query($playParams)) ?>">Pick a contract first</a>
    </div>

    <form method="get">
        <input type="hidden" name="seed" value="<?= (int) $seed ?>">
        <input type="hidden" name="campaign" value="<?= $campaign === 0 ? 0 : 1 ?>">
        <input type="hidden" name="street_cred" value="<?= (int) $streetCred ?>">
        <input type="hidden" name="roll" value="1">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <input type="hidden" name="role<?= $i ?>" value="<?= layout_h($rolePick[$i]) ?>">
        <?php endfor; ?>

        <div class="crew-grid">
        <?php foreach ($crew as $idx => $member):
            $c = $member->toPublicArray();
            $emp = (int) ($member->stats['EMP'] ?? 6);
            $sim = (new ChromePlanner())->simulate(
                $emp,
                $chromePick[$idx],
                $rules,
                new Rng($seed * 31 + $idx + 1),
            );
            $h = $sim['humanity'];
            $pct = $h['max_humanity'] > 0 ? (int) round(($h['current_humanity'] / $h['max_humanity']) * 100) : 0;
            $statusClass = match ($h['status']) {
                'stable' => 'status-ok',
                'empathy_warning' => 'status-warn',
                default => 'status-bad',
            };
        ?>
            <article class="crew-card">
                <div class="role"><?= layout_h($c['role']) ?></div>
                <h3><?= layout_h($c['handle']) ?></h3>
                <p class="muted"><?= layout_h((string) $c['personality']) ?> · <?= layout_h((string) $c['origin']) ?></p>
                <?php if (!empty($c['public_hook'])): ?>
                    <p class="crew-hook"><?= layout_h((string) $c['public_hook']) ?></p>
                <?php endif; ?>

                <div class="stat-pills">
                    <?php foreach ($member->stats as $stat => $val): ?>
                        <span><?= layout_h(strtoupper($stat)) ?> <?= (int) $val ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="lifepath-details">
                    <div><strong>Family:</strong> <?= layout_h((string) $c['family']) ?></div>
                    <?php if (!empty($c['life_events'])): ?>
                        <div><strong>Life events</strong>
                            <ul>
                                <?php foreach ($c['life_events'] as $ev): ?>
                                    <li><?= layout_h((string) $ev) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($c['contacts'])): ?>
                        <div><strong>Contacts:</strong> <?= layout_h(implode(', ', $c['contacts'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($c['enemies'])): ?>
                        <div><strong>Enemies:</strong> <?= layout_h(implode(', ', $c['enemies'])) ?></div>
                    <?php endif; ?>
                </div>

                <div class="chrome-section">
                    <strong>Chrome shop</strong>
                    <div class="humanity-bar-wrap">
                        <div class="meta-row">
                            <span>Humanity <?= (int) $h['current_humanity'] ?> / <?= (int) $h['max_humanity'] ?></span>
                            <span>EMP <?= (int) $h['emp'] ?></span>
                            <span class="<?= $statusClass ?>"><?= layout_h(str_replace('_', ' ', (string) $h['status'])) ?></span>
                        </div>
                        <div class="humanity-bar"><span style="width:<?= max(0, min(100, $pct)) ?>%"></span></div>
                    </div>

                    <div class="chrome-pick">
                        <?php
                        $selected = array_flip($chromePick[$idx]);
                        foreach ($allCyber as $cw):
                            $cid = (string) ($cw['id'] ?? '');
                            if ($cid === '') {
                                continue;
                            }
                            $checked = isset($selected[$cid]);
                        ?>
                            <label>
                                <input type="checkbox" name="chrome<?= $idx ?>[]" value="<?= layout_h($cid) ?>"<?= $checked ? ' checked' : '' ?>>
                                <span><?= layout_h((string) ($cw['name'] ?? $cid)) ?>
                                    <span class="muted">— <?= layout_h((string) ($cw['humanity_loss'] ?? '0')) ?> HL · <?= (int) ($cw['eddies'] ?? 0) ?>eb</span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($sim['log'] !== []): ?>
                        <details>
                            <summary>Install log (seeded rolls)</summary>
                            <ul class="lifepath-details">
                                <?php foreach ($sim['log'] as $entry): ?>
                                    <li>
                                        <?php if (isset($entry['error'])): ?>
                                            <span class="status-bad"><?= layout_h((string) $entry['error']) ?></span>
                                        <?php else: ?>
                                            <?= layout_h((string) ($entry['cyberware'] ?? '?')) ?> — rolled <?= (int) ($entry['humanity_loss_rolled'] ?? 0) ?> HL
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        </div>

        <button type="submit" name="roll" value="1">Recalculate chrome</button>
    </form>
<?php endif; ?>

<?php layout_footer(); ?>
