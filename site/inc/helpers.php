<?php
// Gemeinsame Helfer für die öffentliche Seite und den Admin-Bereich.

require_once __DIR__ . '/config.php';

function h(?string $text): string {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
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
    return (new DateTimeImmutable($iso))->format($mitZeit ? 'd.m.Y H:i' : 'd.m.Y');
}

function verwendungszweck(string $name): string {
    return VERWENDUNGSZWECK_PRAEFIX . $name;
}

// Prüft Name, Anzahl und Bemerkung serverseitig (öffentliches Formular und Admin).
// Liefert [bereinigte Daten, Fehlermeldungen].
function bestellung_validieren(array $eingabe): array {
    $fehler = [];

    $name = trim(preg_replace('/\s+/u', ' ', (string) ($eingabe['name'] ?? '')));
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

    $bemerkung = trim((string) ($eingabe['bemerkung'] ?? ''));
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
