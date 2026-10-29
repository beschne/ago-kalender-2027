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

const UEBERGABE = 'bei der Weihnachtsfeier im Dezember 2026';
const LOESCHFRIST = '31.01.2027';

// Grenzen für das Formular (Anzahl auch per CHECK in der Datenbank abgesichert).
const ANZAHL_MAX = 25;
const NAME_MAX = 100;
const BEMERKUNG_MAX = 500;
const BESTELLUNGEN_PRO_SESSION = 5;

// Öffentliche Anzeige "Bisher wurden X Kalender bestellt" (nur Summe, keine Namen).
const ZAEHLER_ANZEIGEN = true;

// Empfohlene Druckmenge im Admin-Bereich: bestellt + Reserve, begrenzt auf Min/Max.
const DRUCK_RESERVE = 5;
const DRUCK_MIN = 45;
const DRUCK_MAX = 55;

const KONTO_EMPFAENGER = 'Astronomische Gesellschaft Orion Bad Homburg e.V.';
const KONTO_BANK = 'Taunus Sparkasse';
const KONTO_IBAN = 'DE30 5125 0000 0001 0574 80';
const KONTO_BIC = 'HELADEF1TSK';
// Bindestrich statt Gedankenstrich: SEPA erlaubt nur einen eingeschränkten Zeichensatz.
const VERWENDUNGSZWECK_PRAEFIX = 'Astrokalender 2027 - ';

// Upload-Seite für Bilder (HiDrive-Freigabe), erscheint in der Navigation. Leer = ausgeblendet.
const UPLOAD_URL = '';

const KONTAKT_NAME = 'Benno Schneider';
const KONTAKT_EMAIL = 'benno.schneider@agorion.de';
