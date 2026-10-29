<?php
// Löscht eine einzelne Bestellung (id) oder alle Bestellungen (alle=1 mit Bestätigungstext).
require __DIR__ . '/../inc/admin.php';
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';
require __DIR__ . '/../inc/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Nur POST erlaubt.');
}

csrf_verify();

$pdo = ago_kalender_db();

if (($_POST['alle'] ?? '') === '1') {
    if (trim((string) ($_POST['bestaetigung'] ?? '')) !== 'ALLE LÖSCHEN') {
        http_response_code(400);
        die('Bestätigung fehlt: bitte genau „ALLE LÖSCHEN“ eintippen. <a href="index.php">Zurück</a>');
    }
    $pdo->exec('DELETE FROM bestellungen');
    // Datei verkleinern und freie Seiten neu schreiben, damit keine Reste der Daten bleiben.
    $pdo->exec('VACUUM');
    header('Location: index.php?alle_geloescht=1', true, 303);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    die('Fehlende ID.');
}

$stmt = $pdo->prepare('DELETE FROM bestellungen WHERE id = ?');
$stmt->execute([$id]);

header('Location: index.php?geloescht=1', true, 303);
exit;
