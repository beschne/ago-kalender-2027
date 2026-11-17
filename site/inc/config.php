<?php
// Zentrale Einstellungen der Kalenderaktion. Für 2028 nur diese Datei anpassen.

date_default_timezone_set('Europe/Berlin');

const KALENDER_JAHR = 2027;
// Absolute Adresse, nötig für die Link-Vorschau (Open Graph).
const SITE_URL = 'https://kalender.agorion.de';
const KALENDER_FORMAT = 'Wandkalender 30 x 45 cm (A3 hoch), Premium-Papier matt, 13 Blätter';

// Preis in Cent, damit nicht mit Gleitkommazahlen gerechnet wird.
const PREIS_CENT = 1000;

// Bestellschluss (Europe/Berlin), danach ist das Formular automatisch geschlossen.
const BESTELLSCHLUSS = '2026-11-22 23:59:59';

const UEBERGABE = 'bei der Weihnachtsfeier am 18.12.2026';
const LOESCHFRIST = '31.01.2027';

// Zeitplan für die öffentliche Seite /termine, chronologisch. "datum" (JJJJ-MM-TT) wird als
// "Fr, 20.11.2026" angezeigt, "wann" ersetzt das für ungefähre Termine. Ohne "erledigt" gilt ein
// Termin mit Datum ab dem Folgetag als erledigt; Aufgaben besser von Hand auf true setzen.
const TERMINE = [
    ['datum' => '2026-10-27', 'titel' => 'Angebot der Druckerei Saal Digital liegt vor', 'erledigt' => true],
    ['datum' => '2026-11-08', 'titel' => 'Fotos der Mitglieder gesammelt', 'erledigt' => true],
    ['datum' => '2026-11-09', 'titel' => 'Jury beginnt mit der Bildauswahl', 'erledigt' => true],
    ['datum' => '2026-11-12', 'titel' => 'Jury hat die Bilder ausgewählt, Gestaltung beginnt', 'erledigt' => true],
    ['datum' => '2026-11-17', 'titel' => 'Gestaltung freigegeben, Testdruck bestellt', 'text' => '(vorzeitig, geplant war der 20.11.2026)', 'erledigt' => true],
    ['datum' => '2026-11-22', 'titel' => 'Bestellschluss', 'text' => 'um 23:59 Uhr, danach nur noch per Signal oder PN an Benno'],
    ['datum' => '2026-11-27', 'titel' => 'Testdruck geprüft, Kalender in Druck gegeben', 'erledigt' => false],
    ['datum' => '2026-12-11', 'titel' => 'Erste Kalender für ausgewählte Mitglieder', 'erledigt' => false],
    ['datum' => '2026-12-18', 'titel' => 'Übergabe der Kalender bei der Weihnachtsfeier', 'erledigt' => false],
    ['datum' => '2027-01-31', 'titel' => 'Löschung aller Bestelldaten', 'text' => 'spätestens an diesem Tag', 'erledigt' => false],
];

// Grenzen für das Formular (Anzahl auch per CHECK in der Datenbank abgesichert).
const ANZAHL_MAX = 25;
const NAME_MAX = 100;
const BEMERKUNG_MAX = 500;
const BESTELLUNGEN_PRO_SESSION = 5;

// Öffentliche Anzeige "Bisher wurden X Kalender bestellt" (nur Summe, keine Namen).
const ZAEHLER_ANZEIGEN = true;

// Empfohlene Druckmenge im Admin-Bereich: bestellt + Reserve, mindestens DRUCK_MIN.
// Keine Obergrenze: Saal Digital gewährt den Rabatt für jede Menge.
const DRUCK_RESERVE = 5;
const DRUCK_MIN = 45;

const KONTO_EMPFAENGER = 'Astronomische Gesellschaft Orion Bad Homburg e.V.';
const KONTO_BANK = 'Taunus Sparkasse';
const KONTO_IBAN = 'DE30 5125 0000 0001 0574 80';
const KONTO_BIC = 'HELADEF1TSK';
// Bindestrich statt Gedankenstrich: SEPA erlaubt nur einen eingeschränkten Zeichensatz.
const VERWENDUNGSZWECK_PRAEFIX = 'Astrokalender 2027 - ';

// Öffentlicher Navigationspunkt "Bilder hochladen". Leer = ausgeblendet. Bewusst leer: Der
// Upload-Link steht nur im Admin-Bereich (upload-url.txt neben der Datenbank, siehe DEPLOYMENT.md).
const UPLOAD_URL = '';

const KONTAKT_NAME = 'Benno Schneider';
const KONTAKT_EMAIL = 'benno.schneider@agorion.de';
