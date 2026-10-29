<?php
// Druckbare Übergabeliste für die Weihnachtsfeier: alphabetisch, mit offenem Betrag
// und Kästchen zum Abhaken von Hand.
require __DIR__ . '/../inc/admin.php';
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';

$bestellungen = ago_kalender_db()->query('SELECT * FROM bestellungen')->fetchAll();

usort($bestellungen, fn($a, $b) => strcmp(name_sortierschluessel($a['name']), name_sortierschluessel($b['name'])));

$summeKalender = array_sum(array_column($bestellungen, 'anzahl'));
$summeOffen = 0;
foreach ($bestellungen as $b) {
    if (!$b['bezahlt']) {
        $summeOffen += $b['anzahl'] * PREIS_CENT;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Übergabeliste – Astrokalender <?= KALENDER_JAHR ?></title>
    <link rel="stylesheet" href="admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?: '1' ?>">
    <script src="admin.js?v=<?= @filemtime(__DIR__ . '/admin.js') ?: '1' ?>" defer></script>
</head>
<body class="druckliste">
<div class="admin-wrap">
    <div class="admin-header nicht-drucken">
        <a href="index.php">&larr; Zur Übersicht</a>
        <button type="button" class="btn" data-drucken>Drucken</button>
    </div>

    <h1>Astrokalender <?= KALENDER_JAHR ?> – Übergabeliste</h1>
    <p class="hinweis">
        Stand <?= h(jetzt()->format('d.m.Y, H:i')) ?> Uhr ·
        <?= count($bestellungen) ?> Bestellungen · <?= $summeKalender ?> Kalender ·
        noch zu kassieren: <?= h(euro($summeOffen)) ?>
    </p>

    <table class="admin-tabelle uebergabe-tabelle">
        <thead>
        <tr>
            <th class="kaestchen">Übergeben</th>
            <th>Name</th>
            <th class="zahl">Anzahl</th>
            <th>Bezahlt</th>
            <th>Bemerkung</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bestellungen as $b): ?>
            <tr>
                <td class="kaestchen"><?= $b['uebergeben'] ? '☑' : '☐' ?></td>
                <td><?= h($b['name']) ?></td>
                <td class="zahl"><?= (int) $b['anzahl'] ?></td>
                <td class="nowrap">
                    <?= $b['bezahlt'] ? '✓ bezahlt' : '<strong>' . h(euro($b['anzahl'] * PREIS_CENT)) . ' kassieren</strong>' ?>
                </td>
                <td><?= nl2br(h($b['bemerkung'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bestellungen): ?>
            <tr><td colspan="5" class="leer">Noch keine Bestellungen.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <th></th>
            <th>Summe</th>
            <th class="zahl"><?= $summeKalender ?></th>
            <th colspan="2">offen: <?= h(euro($summeOffen)) ?></th>
        </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
