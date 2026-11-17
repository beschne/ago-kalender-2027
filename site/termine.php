<?php
// Öffentlicher Zeitplan der Kalenderaktion, gepflegt in TERMINE (inc/config.php).
require __DIR__ . '/inc/helpers.php';
$title = 'Termine – AG Orion Astrokalender ' . KALENDER_JAHR;
$activeNav = 'termine';
require __DIR__ . '/inc/header.php';
?>
            <h1>Termine</h1>
            <p class="lead">
                So geht es mit dem Astrokalender <?= KALENDER_JAHR ?> weiter. Erledigte Schritte
                sind abgehakt.
            </p>

            <ol class="termine">
<?php foreach (TERMINE as $termin): ?>
    <?php [$wann, $erledigt] = termin_status($termin); ?>
                <li class="<?= $erledigt ? 'erledigt' : 'offen' ?>">
                    <span class="termin-status" aria-hidden="true"><?= $erledigt ? '✓' : '' ?></span>
                    <span class="termin-wann"><?= h($wann) ?></span>
                    <span class="termin-titel">
                        <?= h($termin['titel']) ?><?php if ($erledigt): ?><span class="visually-hidden"> (erledigt)</span><?php endif; ?>
                    </span>
    <?php if (isset($termin['text'])): ?>
                    <span class="termin-text"><?= h($termin['text']) ?></span>
    <?php endif; ?>
                </li>
<?php endforeach; ?>
            </ol>

            <p class="muted">
                Fragen zum Ablauf? Schreib <?= h(KONTAKT_NAME) ?> per Signal oder PN.
            </p>
<?php require __DIR__ . '/inc/footer.php';
