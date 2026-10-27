<?php
// PDO-Verbindung zur SQLite-Datenbank mit den Bestellungen.
// Die Datei liegt bewusst außerhalb des Webroots und außerhalb des Git-Repos; das
// Verzeichnis muss www-data gehören, weil SQLite dort Journal-Dateien anlegt.

function ago_kalender_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // Lokale Entwicklung kann den Pfad per Umgebungsvariable überschreiben.
    $pfad = getenv('AGO_KALENDER_DB') ?: '/var/www/kalender.agorion.de-data/bestellungen.sqlite';
    if (!is_dir(dirname($pfad))) {
        throw new RuntimeException('Datenverzeichnis nicht gefunden: ' . dirname($pfad));
    }

    $pdo = new PDO('sqlite:' . $pfad, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    // Gelöschte Datensätze werden überschrieben statt nur als frei markiert (Löschfrist).
    $pdo->exec('PRAGMA secure_delete = ON');

    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS bestellungen (
            id INTEGER PRIMARY KEY,
            erstellt_am TEXT NOT NULL,
            name TEXT NOT NULL,
            anzahl INTEGER NOT NULL CHECK (anzahl BETWEEN 1 AND 20),
            bemerkung TEXT,
            bezahlt INTEGER NOT NULL DEFAULT 0,
            bezahlt_am TEXT,
            notiz_intern TEXT,
            quelle TEXT DEFAULT 'formular',
            uebergeben INTEGER NOT NULL DEFAULT 0
        )
        SQL);

    // Spalten, die nach dem Start ergänzt wurden, in bestehenden Datenbanken nachziehen.
    $spalten = array_column($pdo->query('PRAGMA table_info(bestellungen)')->fetchAll(), 'name');
    if (!in_array('uebergeben', $spalten, true)) {
        $pdo->exec('ALTER TABLE bestellungen ADD COLUMN uebergeben INTEGER NOT NULL DEFAULT 0');
    }

    return $pdo;
}

// Summe aller bestellten Kalender (für die öffentliche Anzeige und die Kennzahlen).
function kalender_bestellt_summe(PDO $pdo): int {
    return (int) $pdo->query('SELECT COALESCE(SUM(anzahl), 0) FROM bestellungen')->fetchColumn();
}
