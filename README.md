# AG Orion – Astrokalender 2027

Bestellseite für den Astrokalender der Astronomischen Gesellschaft Orion Bad Homburg e.V.
Mitglieder bestellen über ein einfaches Formular, der Vorsitzende pflegt Bestellungen und
Zahlungseingänge in einem geschützten Admin-Bereich.

Live: https://kalender.agorion.de

* PHP 8.2 ohne Framework, SQLite über PDO, kein Build-Schritt
* Datensparsam: nur Name, Anzahl, optionale Bemerkung (siehe [DATENSCHUTZ.md](DATENSCHUTZ.md))
* Einstellungen (Preis, Bestellschluss, Konto, Jahr) zentral in [`site/inc/config.php`](site/inc/config.php)

| Pfad | Inhalt |
|---|---|
| `/` | Einleitung, Bestellformular, Bestätigung mit Kontoverbindung |
| `/datenschutz` | Datenschutzhinweis nach Art. 13 DSGVO |
| `/impressum` | Impressum |
| `/admin/` | Bestellungen, Kennzahlen, Druckmenge, CSV-Export (Basic Auth) |
| `/admin/liste.php` | Druckbare Übergabeliste für die Weihnachtsfeier |

Lokal starten und deployen: siehe [DEPLOYMENT.md](DEPLOYMENT.md).
