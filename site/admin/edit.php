<?php
// Bestellung manuell anlegen (ohne id) oder bearbeiten (mit id).
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';
require __DIR__ . '/../inc/csrf.php';

$pdo = ago_kalender_db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$bestellung = [
    'name' => '', 'anzahl' => 1, 'bemerkung' => '', 'bezahlt' => 0, 'bezahlt_am' => '',
    'notiz_intern' => '', 'erstellt_am' => null, 'quelle' => 'manuell',
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM bestellungen WHERE id = ?');
    $stmt->execute([$id]);
    $bestellung = $stmt->fetch();
    if (!$bestellung) {
        http_response_code(404);
        die('Bestellung nicht gefunden.');
    }
}

$fehler = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$daten, $fehler] = bestellung_validieren($_POST);

    $bezahlt = !empty($_POST['bezahlt']);
    $bezahltAm = trim((string) ($_POST['bezahlt_am'] ?? ''));
    if ($bezahltAm !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bezahltAm)) {
        $fehler[] = 'Ungültiges Datum bei „bezahlt am“.';
    }
    if ($bezahlt && $bezahltAm === '') {
        $bezahltAm = jetzt()->format('Y-m-d');
    }
    if (!$bezahlt) {
        $bezahltAm = '';
    }
    $notiz = trim((string) ($_POST['notiz_intern'] ?? ''));

    if (!$fehler) {
        $werte = [$daten['name'], $daten['anzahl'], $daten['bemerkung'], $bezahlt ? 1 : 0,
            $bezahltAm === '' ? null : $bezahltAm, $notiz === '' ? null : $notiz];
        if ($id) {
            $stmt = $pdo->prepare('UPDATE bestellungen SET name = ?, anzahl = ?, bemerkung = ?, bezahlt = ?,
                bezahlt_am = ?, notiz_intern = ? WHERE id = ?');
            $stmt->execute([...$werte, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO bestellungen (name, anzahl, bemerkung, bezahlt, bezahlt_am,
                notiz_intern, erstellt_am, quelle) VALUES (?, ?, ?, ?, ?, ?, ?, 'manuell')");
            $stmt->execute([...$werte, jetzt()->format('c')]);
            $id = (int) $pdo->lastInsertId();
        }
        header('Location: index.php?gespeichert=1#b' . $id, true, 303);
        exit;
    }

    // Eingaben bei Fehlern erhalten
    $bestellung = array_merge($bestellung, [
        'name' => (string) ($_POST['name'] ?? ''),
        'anzahl' => (string) ($_POST['anzahl'] ?? ''),
        'bemerkung' => (string) ($_POST['bemerkung'] ?? ''),
        'bezahlt' => $bezahlt ? 1 : 0,
        'bezahlt_am' => $bezahltAm,
        'notiz_intern' => $notiz,
    ]);
}

csrf_token(); // Session-Cookie muss gesetzt sein, bevor unten HTML-Ausgabe beginnt
$ueberschrift = $id ? 'Bestellung bearbeiten' : 'Bestellung anlegen';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($ueberschrift) ?> – Admin Astrokalender <?= KALENDER_JAHR ?></title>
    <link rel="stylesheet" href="admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?: '1' ?>">
</head>
<body>
<div class="admin-wrap admin-schmal">
    <div class="admin-header">
        <h1><?= h($ueberschrift) ?></h1>
        <a href="index.php">&larr; Zur Übersicht</a>
    </div>

    <?php if ($id): ?>
        <p class="hinweis">
            Bestellt am <?= h(datum_kurz($bestellung['erstellt_am'], true)) ?>
            (<?= $bestellung['quelle'] === 'manuell' ? 'manuell angelegt' : 'über das Formular' ?>)
        </p>
    <?php endif; ?>

    <?php if ($fehler): ?>
        <div class="fehler">
            <?php foreach ($fehler as $meldung): ?>
                <div><?= h($meldung) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="edit.php<?= $id ? '?id=' . $id : '' ?>" class="admin-form">
        <?= csrf_field() ?>
        <?php if ($id): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" required maxlength="<?= NAME_MAX ?>" value="<?= h($bestellung['name']) ?>">

        <label for="anzahl">Anzahl (1–<?= ANZAHL_MAX ?>)</label>
        <input type="number" id="anzahl" name="anzahl" required min="1" max="<?= ANZAHL_MAX ?>" step="1"
               value="<?= h((string) $bestellung['anzahl']) ?>">

        <label for="bemerkung">Bemerkung (vom Mitglied)</label>
        <textarea id="bemerkung" name="bemerkung" maxlength="<?= BEMERKUNG_MAX ?>"><?= h($bestellung['bemerkung']) ?></textarea>

        <label class="checkbox">
            <input type="checkbox" name="bezahlt" value="1"<?= $bestellung['bezahlt'] ? ' checked' : '' ?>>
            bezahlt
        </label>

        <label for="bezahlt_am">bezahlt am</label>
        <input type="date" id="bezahlt_am" name="bezahlt_am" value="<?= h($bestellung['bezahlt_am']) ?>">
        <p class="hinweis">Leer lassen = heute, sobald „bezahlt“ angehakt ist.</p>

        <label for="notiz_intern">Interne Notiz</label>
        <textarea id="notiz_intern" name="notiz_intern"><?= h($bestellung['notiz_intern']) ?></textarea>

        <div class="admin-actions">
            <button type="submit" class="btn">Speichern</button>
        </div>
    </form>
</div>
</body>
</html>
