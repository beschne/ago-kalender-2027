<?php
// Gemeinsame Helfer für die öffentliche Seite und den Admin-Bereich.

require_once __DIR__ . '/config.php';

// Unbehandelte Fehler (z. B. Datenbank nicht erreichbar): ins PHP-Log schreiben und
// statt einer leeren Seite die statische Störungsseite zeigen.
set_exception_handler(function (Throwable $fehler): void {
    error_log('AGO Kalender: ' . $fehler);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store');
    }
    readfile(__DIR__ . '/../50x.html');
});

function h(?string $text): string {
    return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Betrag in Cent als "10,00 €" (geschütztes Leerzeichen vor dem Euro-Zeichen).
function euro(int $cent): string {
    return number_format($cent / 100, 2, ',', '.') . "\u{00A0}€";
}

// Aktueller Zeitpunkt; lokal per AGO_KALENDER_JETZT überschreibbar, um den
// Zustand nach Bestellschluss zu testen.
function jetzt(): DateTimeImmutable {
    return new DateTimeImmutable(getenv('AGO_KALENDER_JETZT') ?: 'now');
}

function bestellschluss(): DateTimeImmutable {
    return new DateTimeImmutable(BESTELLSCHLUSS);
}

function bestellung_offen(): bool {
    return jetzt() <= bestellschluss();
}

const WOCHENTAGE = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

// "Sonntag, 22.11.2026, 23:59 Uhr"
function datum_lang(DateTimeImmutable $datum): string {
    return WOCHENTAGE[(int) $datum->format('w')] . ', ' . $datum->format('d.m.Y, H:i') . ' Uhr';
}

// ISO-8601-Text aus der Datenbank als "22.11.2026" bzw. mit Uhrzeit.
function datum_kurz(?string $iso, bool $mitZeit = false): string {
    if ($iso === null || $iso === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable($iso))->format($mitZeit ? 'd.m.Y H:i' : 'd.m.Y');
    } catch (Exception) {
        // Ungültiger Wert in der Datenbank: lieber roh anzeigen als die ganze Seite abbrechen.
        return $iso;
    }
}

// Datum "JJJJ-MM-TT" aus einem Formularfeld; null, wenn es das Datum nicht gibt (z. B. 31.02.).
function eingabe_datum(string $wert): ?string {
    $datum = DateTimeImmutable::createFromFormat('!Y-m-d', $wert);
    return $datum && $datum->format('Y-m-d') === $wert ? $wert : null;
}

// Text aus einem Formularfeld. Nicht-Strings (z. B. name[]=…) und ungültiges UTF-8 werden zu '',
// Steuer- und Formatzeichen entfernt, etwa der Richtungswechsel U+202E, mit dem sich Namen in
// der Admin-Liste optisch fälschen ließen. Einzeilig: Leerraum zu einem Leerzeichen zusammengefasst.
function eingabe_text(mixed $wert, bool $mehrzeilig = false): string {
    if (!is_string($wert) || !mb_check_encoding($wert, 'UTF-8')) {
        return '';
    }
    $wert = $mehrzeilig ? str_replace("\r\n", "\n", $wert) : preg_replace('/\s+/u', ' ', $wert);
    return trim(preg_replace('/(?!\n)[\p{Cc}\p{Cf}]/u', '', $wert));
}

// Öffentlicher Zähler (HTML), z. B. "Bisher wurden <strong>12 Kalender</strong> bestellt."
function zaehler_satz(int $bestellt): string {
    if ($bestellt === 0) {
        return 'Bisher wurde noch kein Kalender bestellt.';
    }
    $verb = $bestellt === 1 ? 'wurde' : 'wurden';
    return "Bisher {$verb} <strong>{$bestellt} Kalender</strong> bestellt.";
}

// Schlüssel für alphabetische Sortierung, Umlaute wie ihr Grundbuchstabe (ohne intl-Erweiterung).
function name_sortierschluessel(string $name): string {
    return mb_strtolower(strtr($name, [
        'Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U', 'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
    ]));
}

function verwendungszweck(string $name): string {
    return VERWENDUNGSZWECK_PRAEFIX . $name;
}

// Prüft Name, Anzahl und Bemerkung serverseitig (öffentliches Formular und Admin).
// Liefert [bereinigte Daten, Fehlermeldungen].
function bestellung_validieren(array $eingabe): array {
    $fehler = [];

    $name = eingabe_text($eingabe['name'] ?? '');
    if ($name === '') {
        $fehler[] = 'Bitte gib deinen Namen an.';
    } elseif (mb_strlen($name) > NAME_MAX) {
        $fehler[] = 'Der Name darf höchstens ' . NAME_MAX . ' Zeichen lang sein.';
    }

    $anzahl = filter_var($eingabe['anzahl'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => ANZAHL_MAX],
    ]);
    if ($anzahl === false) {
        $fehler[] = 'Bitte gib als Anzahl eine ganze Zahl von 1 bis ' . ANZAHL_MAX . ' an.';
    }

    $bemerkung = eingabe_text($eingabe['bemerkung'] ?? '', true);
    if (mb_strlen($bemerkung) > BEMERKUNG_MAX) {
        $fehler[] = 'Die Bemerkung darf höchstens ' . BEMERKUNG_MAX . ' Zeichen lang sein.';
    }

    return [
        [
            'name' => $name,
            'anzahl' => $anzahl === false ? null : $anzahl,
            'bemerkung' => $bemerkung === '' ? null : $bemerkung,
        ],
        $fehler,
    ];
}

// Empfohlene Druckmenge: bestellt + Reserve, mindestens DRUCK_MIN, höchstens DRUCK_MAX.
// Liefert [Menge, Hinweis oder null, wenn eine Grenze greift].
function druckmenge(int $bestellt): array {
    $roh = $bestellt + DRUCK_RESERVE;
    if ($roh < DRUCK_MIN) {
        return [DRUCK_MIN, 'Mindestmenge ' . DRUCK_MIN . ' greift (rechnerisch ' . $roh . ').'];
    }
    if ($roh > DRUCK_MAX) {
        return [DRUCK_MAX, 'Höchstmenge ' . DRUCK_MAX . ' greift (rechnerisch ' . $roh . ').'];
    }
    return [$roh, null];
}
