<?php
// Übersicht aller Bestellungen mit Kennzahlen und Bezahlt-Umschalter.
// Zugriff nur über nginx auth_basic, siehe deploy/nginx/kalender.agorion.de.conf.
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';
require __DIR__ . '/../inc/csrf.php';

$pdo = ago_kalender_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $wert = ($_POST['wert'] ?? '') === '1';
    $aktion = $_POST['aktion'] ?? '';
    if ($aktion === 'bezahlt') {
        $stmt = $pdo->prepare('UPDATE bestellungen SET bezahlt = ?, bezahlt_am = ? WHERE id = ?');
        $stmt->execute([$wert ? 1 : 0, $wert ? jetzt()->format('Y-m-d') : null, $id]);
    } elseif ($aktion === 'uebergeben') {
        $stmt = $pdo->prepare('UPDATE bestellungen SET uebergeben = ? WHERE id = ?');
        $stmt->execute([$wert ? 1 : 0, $id]);
    }
    header('Location: index.php#b' . $id, true, 303);
    exit;
}

// Umschalt-Knopf für bezahlt/übergeben (funktioniert ohne JavaScript).
function umschalter(array $b, string $aktion, string $an, string $aus): string {
    $istAn = (bool) $b[$aktion];
    return '<form method="post" action="index.php">' . csrf_field()
        . '<input type="hidden" name="aktion" value="' . $aktion . '">'
        . '<input type="hidden" name="id" value="' . (int) $b['id'] . '">'
        . '<input type="hidden" name="wert" value="' . ($istAn ? '0' : '1') . '">'
        . '<button type="submit" class="umschalter' . ($istAn ? ' ist-an' : '') . '">'
        . ($istAn ? '☑ ' . $an : '☐ ' . $aus) . '</button></form>';
}

csrf_token(); // Session-Cookie muss gesetzt sein, bevor unten HTML-Ausgabe beginnt

$bestellungen = $pdo->query('SELECT * FROM bestellungen ORDER BY erstellt_am, id')->fetchAll();

$kalenderBestellt = 0;
$kalenderBezahlt = 0;
$kalenderUebergeben = 0;
foreach ($bestellungen as $b) {
    $kalenderBestellt += $b['anzahl'];
    if ($b['bezahlt']) {
        $kalenderBezahlt += $b['anzahl'];
    }
    if ($b['uebergeben']) {
        $kalenderUebergeben += $b['anzahl'];
    }
}
$kalenderOffen = $kalenderBestellt - $kalenderBezahlt;
[$druckmenge, $druckHinweis] = druckmenge($kalenderBestellt);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellungen – Admin Astrokalender <?= KALENDER_JAHR ?></title>
    <link rel="stylesheet" href="admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?: '1' ?>">
    <script src="admin.js?v=<?= @filemtime(__DIR__ . '/admin.js') ?: '1' ?>" defer></script>
</head>
<body>
<div class="admin-wrap">
    <div class="admin-header">
        <h1>Astrokalender <?= KALENDER_JAHR ?> – Bestellungen</h1>
        <div class="admin-header-aktionen">
            <a href="liste.php">Übergabeliste</a>
            <a href="export.php">CSV-Export</a>
            <a href="edit.php" class="btn">+ Bestellung anlegen</a>
        </div>
    </div>

    <?php if (isset($_GET['gespeichert'])): ?>
        <p class="erfolg">Bestellung gespeichert.</p>
    <?php endif; ?>
    <?php if (isset($_GET['geloescht'])): ?>
        <p class="erfolg">Bestellung gelöscht.</p>
    <?php endif; ?>
    <?php if (isset($_GET['alle_geloescht'])): ?>
        <p class="erfolg">Alle Bestellungen wurden gelöscht.</p>
    <?php endif; ?>

    <p class="admin-status">
        Bestellschluss: <?= h(datum_lang(bestellschluss())) ?> –
        <?= bestellung_offen() ? '<strong>Formular ist offen</strong>' : '<strong>Formular ist geschlossen</strong>' ?>
    </p>

    <div class="kennzahlen">
        <div class="kennzahl">
            <h2>Kalender</h2>
            <dl>
                <dt>bestellt</dt><dd><?= $kalenderBestellt ?></dd>
                <dt>bezahlt</dt><dd><?= $kalenderBezahlt ?></dd>
                <dt>offen</dt><dd><?= $kalenderOffen ?></dd>
                <dt>übergeben</dt><dd><?= $kalenderUebergeben ?></dd>
            </dl>
        </div>
        <div class="kennzahl">
            <h2>Betrag</h2>
            <dl>
                <dt>erwartet</dt><dd><?= h(euro($kalenderBestellt * PREIS_CENT)) ?></dd>
                <dt>eingegangen</dt><dd><?= h(euro($kalenderBezahlt * PREIS_CENT)) ?></dd>
                <dt>offen</dt><dd><?= h(euro($kalenderOffen * PREIS_CENT)) ?></dd>
            </dl>
        </div>
        <div class="kennzahl kennzahl-druck">
            <h2>Empfohlene Druckmenge</h2>
            <p class="druckmenge"><?= $druckmenge ?></p>
            <p class="hinweis">
                bestellt + <?= DRUCK_RESERVE ?>, mindestens <?= DRUCK_MIN ?>, höchstens <?= DRUCK_MAX ?>
            </p>
            <?php if ($druckHinweis !== null): ?>
                <p class="warnung"><?= h($druckHinweis) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <p class="admin-anzahl"><?= count($bestellungen) ?> Bestellungen</p>

    <div class="tabelle-scroll">
    <table class="admin-tabelle">
        <thead>
        <tr>
            <th>Datum</th>
            <th>Name</th>
            <th class="zahl">Anzahl</th>
            <th class="zahl">Betrag</th>
            <th>Bemerkung</th>
            <th>Bezahlt</th>
            <th>Übergeben</th>
            <th>Interne Notiz</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bestellungen as $b): ?>
            <tr id="b<?= (int) $b['id'] ?>" class="<?= $b['bezahlt'] ? 'ist-bezahlt' : '' ?>">
                <td class="nowrap"><?= h(datum_kurz($b['erstellt_am'], true)) ?></td>
                <td>
                    <?= h($b['name']) ?>
                    <?php if ($b['quelle'] === 'manuell'): ?>
                        <span class="tag">manuell</span>
                    <?php endif; ?>
                </td>
                <td class="zahl"><?= (int) $b['anzahl'] ?></td>
                <td class="zahl nowrap"><?= h(euro($b['anzahl'] * PREIS_CENT)) ?></td>
                <td><?= nl2br(h($b['bemerkung'])) ?></td>
                <td class="nowrap">
                    <?= umschalter($b, 'bezahlt', 'bezahlt', 'offen') ?>
                    <?php if ($b['bezahlt'] && $b['bezahlt_am']): ?>
                        <span class="hinweis"><?= h(datum_kurz($b['bezahlt_am'])) ?></span>
                    <?php endif; ?>
                </td>
                <td class="nowrap"><?= umschalter($b, 'uebergeben', 'übergeben', 'nein') ?></td>
                <td><?= nl2br(h($b['notiz_intern'])) ?></td>
                <td class="nowrap aktionen">
                    <a href="edit.php?id=<?= (int) $b['id'] ?>">Bearbeiten</a>
                    <form method="post" action="delete.php" data-confirm="Bestellung von <?= h($b['name']) ?> wirklich löschen?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                        <button type="submit" class="link-gefahr">Löschen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bestellungen): ?>
            <tr><td colspan="9" class="leer">Noch keine Bestellungen.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <details class="gefahrenzone">
        <summary>Alle Bestellungen löschen (nach Abwicklung, spätestens <?= h(LOESCHFRIST) ?>)</summary>
        <p>
            Löscht <strong>alle</strong> Bestellungen endgültig. Vorher ggf. den
            <a href="export.php">CSV-Export</a> für den Kassenwart sichern.
            Zur Bestätigung bitte <code>ALLE LÖSCHEN</code> eintippen.
        </p>
        <form method="post" action="delete.php" class="admin-form" data-confirm="Wirklich ALLE Bestellungen endgültig löschen?">
            <?= csrf_field() ?>
            <input type="hidden" name="alle" value="1">
            <input type="text" name="bestaetigung" autocomplete="off" required placeholder="ALLE LÖSCHEN">
            <button type="submit" class="btn btn-gefahr">Alle Bestellungen löschen</button>
        </form>
    </details>
</div>
</body>
</html>
