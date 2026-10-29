<?php
// CSV-Export aller Bestellungen: UTF-8 mit BOM und Semikolon, damit Excel und Numbers
// die Datei direkt korrekt öffnen.
require __DIR__ . '/../inc/admin.php';
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';

$bestellungen = ago_kalender_db()->query('SELECT * FROM bestellungen ORDER BY erstellt_am, id')->fetchAll();

// Zellen, die mit =, +, -, @ beginnen, würden Tabellenprogramme als Formel ausführen.
function csv_zelle(string $wert): string {
    return preg_match('/^[=+\-@\t\r]/', $wert) ? "'" . $wert : $wert;
}

$dateiname = 'astrokalender-' . KALENDER_JAHR . '-bestellungen-' . jetzt()->format('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $dateiname . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Datum', 'Name', 'Anzahl', 'Betrag (EUR)', 'Bemerkung', 'Bezahlt', 'Bezahlt am', 'Übergeben', 'Interne Notiz', 'Quelle'], ';', '"', '');
foreach ($bestellungen as $b) {
    fputcsv($out, [
        datum_kurz($b['erstellt_am'], true),
        csv_zelle($b['name']),
        $b['anzahl'],
        number_format($b['anzahl'] * PREIS_CENT / 100, 2, ',', ''),
        csv_zelle((string) $b['bemerkung']),
        $b['bezahlt'] ? 'ja' : 'nein',
        datum_kurz($b['bezahlt_am']),
        $b['uebergeben'] ? 'ja' : 'nein',
        csv_zelle((string) $b['notiz_intern']),
        $b['quelle'],
    ], ';', '"', '');
}
fclose($out);
