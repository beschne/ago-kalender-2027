# AG Orion – Astrokalender 2027

![Astrokalender 2027: Milchstraße über dem Horizont, davor Teleskop, Smartscope und Kamera](site/img/banner-940.jpg)

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
| `/termine` | Zeitplan der Kalenderaktion |
| `/datenschutz` | Datenschutzhinweis nach Art. 13 DSGVO |
| `/impressum` | Impressum |
| `/admin/` | Bestellungen, Kennzahlen, Druckmenge, CSV-Export, Bestellinfo als Signal-Nachricht (Basic Auth) |
| `/admin/liste.php` | Druckbare Übergabeliste für die Weihnachtsfeier |

## Dokumentation

| Datei | Inhalt |
|---|---|
| [DEPLOYMENT.md](DEPLOYMENT.md) | Lokal starten, Server einrichten, deployen, Backup |
| [DATENSCHUTZ.md](DATENSCHUTZ.md) | DSGVO-Rahmen der Bestellseite: Rechtsgrundlage, gespeicherte Daten, Löschfrist |
| [BESTELLINFO.md](BESTELLINFO.md) | Wie Mitglieder datenschutzkonform erfahren, was sie bestellt haben (Abwägung der Varianten) |
| [SECURITY_AUDIT.md](SECURITY_AUDIT.md) | Sicherheitsprüfung von Code und Server |
