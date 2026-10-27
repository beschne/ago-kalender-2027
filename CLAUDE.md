# CLAUDE.md

Diese Datei gibt Claude Code Kontext für die Arbeit in diesem Repository.

## Projekt

AG Orion – Bestellseite für den **AG Orion Astrokalender 2027**.
Mitglieder bestellen Kalender über ein einfaches Formular, der Vorsitzende (Benno) pflegt
Bestellungen und Zahlungseingänge in einem geschützten Admin-Bereich.

Veröffentlicht unter **https://kalender.agorion.de** (Subdomain ggf. anpassen, DNS-Eintrag existiert).

Vorbild in Aufbau, Stil, Sicherheit und Deployment ist das Schwesterprojekt
**ago-sofi-site** (https://github.com/beschne/ago-sofi-site, sofi.agorion.de).
Dessen `DEPLOYMENT.md`, `deploy/deploy.sh`, `deploy/nginx/*.conf`, `site/inc/csrf.php`,
`site/inc/header.php`/`footer.php` und `site/css/style.css` dienen als Vorlage.
Muster von dort übernehmen statt neu erfinden.

## Tech-Stack

* PHP 8.2 (Server-Templating, kein Framework, kein Build-Schritt, Dateien werden 1:1 deployt)
* **SQLite** über PDO (eine Datei außerhalb des Webroots, kein MariaDB-User nötig)
* CSS ohne Präprozessor, Vanilla JavaScript nur wo nötig
* nginx + PHP-FPM (`unix:/run/php/php8.2-fpm.sock`), TLS per Certbot
* Server, SSH-User und Deploy-Weg wie bei ago-sofi-site (rsync vom Mac, kein Git auf dem Server)

## Fachliche Eckdaten

* Produkt: Wandkalender 30 x 45 (A3 hoch), Premium-Papier matt, 13 Blätter
* **Preis: 10,00 € pro Kalender**
* **Bestellschluss: Sonntag, 22.11.2026, 23:59 Uhr (Europe/Berlin)**. Danach ist das Formular
  automatisch geschlossen und zeigt einen Hinweis (Nachzügler: per Signal/PN an Benno).
* Zahlung per Überweisung auf das Konto der AG Orion:
  Taunus Sparkasse · IBAN DE30 5125 0000 0001 0574 80 · BIC HELADEF1TSK
  Verwendungszweck: `Astrokalender 2027 – <Name>`
* Übergabe bei der Weihnachtsfeier im Dezember 2026
* Ein eventueller Überschuss geht als Spende an den Verein (auf der Seite erwähnen)
* Konfigurierbare Werte (Preis, Bestellschluss, Kontodaten, Jahr) zentral in
  `site/inc/config.php`, damit die Seite 2028 wiederverwendet werden kann

## Seiten

### Öffentlich `/`
* Kurze Einleitung zum Kalender (Format, Preis, Bestellschluss, Übergabe)
* Formular: **Name** (Pflicht), **Anzahl** (Pflicht, ganze Zahl 1–25), **Bemerkung** (optional,
  max. 500 Zeichen). Keine E-Mail, keine Adresse, keine Telefonnummer (Datensparsamkeit).
* Pflicht-Checkbox: Kenntnisnahme des Datenschutzhinweises
* Nach dem Absenden: Bestätigungsseite mit Anzahl, Gesamtbetrag, **deutlich hervorgehobener**
  Kontoverbindung und Verwendungszweck. Keine Bestellnummer nötig.
* Optional: Anzeige „Bisher wurden X Kalender bestellt“ (nur Summe, **keine Namen**)
* Nach Bestellschluss: Formular ausgeblendet, Hinweistext stattdessen
* Änderungen und Stornos laufen per PN über Benno (kein Self-Service)

### Datenschutz `/datenschutz`
Kurzer, verständlicher Hinweis nach Art. 13 DSGVO (Text siehe unten), Link im Footer und am
Formular. Impressum wie bei ago-sofi-site.

### Admin `/admin/` (nginx Basic Auth, eigene htpasswd-Datei)
* Tabelle aller Bestellungen: Datum, Name, Anzahl, Betrag, Bemerkung, **bezahlt** (Checkbox),
  interne Notiz
* Bestellung manuell anlegen, bearbeiten, löschen (für Nachzügler und Korrekturen)
* Kennzahlen oben:
  * Kalender bestellt / bezahlt / offen
  * Betrag erwartet / eingegangen / offen
  * **Empfohlene Druckmenge = bestellte Kalender + 5, mindestens 45, höchstens 55**
    (Hinweis anzeigen, wenn die Grenzen greifen)
* CSV-Export (UTF-8 mit BOM, Semikolon, damit Excel/Numbers es direkt öffnen)
* Button „Alle Bestellungen löschen“ mit Sicherheitsabfrage (für die Löschung nach Abwicklung)
* Reservekalender werden **nicht** in der öffentlichen Seite erwähnt

## Datenmodell (SQLite)

Tabelle `bestellungen`:
`id INTEGER PK`, `erstellt_am TEXT (ISO 8601)`, `name TEXT NOT NULL`, `anzahl INTEGER NOT NULL
CHECK(anzahl BETWEEN 1 AND 25)`, `bemerkung TEXT`, `bezahlt INTEGER NOT NULL DEFAULT 0`,
`bezahlt_am TEXT`, `notiz_intern TEXT`, `quelle TEXT DEFAULT 'formular'` (`formular`/`manuell`),
`uebergeben INTEGER NOT NULL DEFAULT 0` (nachträglich ergänzt, `db.php` zieht die Spalte per
`ALTER TABLE` nach).

Datei: `/var/www/kalender.agorion.de-data/bestellungen.sqlite`, Verzeichnis gehört `www-data`
(SQLite braucht Schreibrecht auf das Verzeichnis für Journal-Dateien). Schema wird beim ersten
Zugriff automatisch angelegt (`CREATE TABLE IF NOT EXISTS`). Pfad per Umgebungsvariable für die
lokale Entwicklung überschreibbar, analog zu `ago_sofi_db()`.

## Sicherheit

* CSRF-Schutz für alle POST-Formulare (Muster aus ago-sofi-site)
* Honeypot-Feld gegen Bots, einfache Rate-Begrenzung (z. B. max. 5 Bestellungen pro Session)
* Alle Ausgaben mit `htmlspecialchars`, alle DB-Zugriffe mit Prepared Statements
* Serverseitige Validierung (Anzahl, Längen, Bestellschluss), nicht nur per HTML
* Admin ausschließlich über nginx `auth_basic`, zusätzlich `X-Robots-Tag: noindex` für die ganze Seite
* SQLite-Datei und Secrets **nie** im Webroot, nie im Repo, nie per rsync

## Datenschutz

Rechtsgrundlage: Art. 6 Abs. 1 lit. b DSGVO (Abwicklung der Bestellung).
Gespeichert werden nur Name, Anzahl, optionale Bemerkung, Zeitpunkt und Zahlungsstatus.
Kein Tracking, keine Cookies außer dem technisch notwendigen Session-Cookie (CSRF),
keine externen Ressourcen (Schriften und Skripte lokal ausliefern).
nginx-Access-Log für diese Seite mit anonymisierten IP-Adressen (letztes Oktett auf 0) oder abschalten.
Löschung aller Bestelldaten nach Abwicklung, spätestens 31.01.2027.

Textvorschlag für `/datenschutz`:

> **Datenschutzhinweis zur Kalenderbestellung**
> Verantwortlich: Astronomische Gesellschaft Orion Bad Homburg e.V., vertreten durch den
> Vorsitzenden Benno Schneider, Elisabethenschneise 2, Peter-Schall-Haus, 61350 Bad Homburg,
> benno.schneider@agorion.de.
> Wir speichern deinen Namen, die bestellte Anzahl und eine eventuelle Bemerkung ausschließlich,
> um deine Kalenderbestellung abzuwickeln, Zahlungseingänge zuzuordnen und die Kalender zu
> übergeben (Art. 6 Abs. 1 lit. b DSGVO). Die Daten sehen nur die für die Abwicklung zuständigen
> Vorstandsmitglieder. Sie werden nicht weitergegeben und nach Abschluss der Aktion, spätestens am
> 31.01.2027, gelöscht. Du hast das Recht auf Auskunft, Berichtigung und Löschung; wende dich dazu
> an die oben genannte Adresse. Außerdem hast du ein Beschwerderecht bei einer Aufsichtsbehörde,
> z. B. dem Hessischen Beauftragten für Datenschutz und Informationsfreiheit.

## Projektstruktur (Ziel)

```text
/
├── CLAUDE.md
├── README.md
├── DEPLOYMENT.md
├── deploy/
│   ├── deploy.sh
│   ├── backup.sh              (DB-Kopie auf den Mac)
│   ├── traffic.sh             (Zugriffsauswertung, nur lokal, gitignored)
│   └── nginx/kalender.agorion.de.conf
└── site/                      ← wird auf den Server deployt
    ├── index.php              (Formular + Bestätigung)
    ├── datenschutz.php
    ├── impressum.php
    ├── 404.php / 50x.html     (Fehlerseiten)
    ├── img/                   (Banner, vorschau.svg/png für die Link-Vorschau)
    ├── admin/
    │   ├── index.php          (Übersicht, Kennzahlen, bezahlt-Häkchen)
    │   ├── edit.php
    │   ├── delete.php
    │   ├── liste.php          (druckbare Übergabeliste)
    │   └── export.php         (CSV)
    ├── inc/
    │   ├── config.php
    │   ├── db.php
    │   ├── csrf.php
    │   ├── helpers.php
    │   ├── header.php / footer.php
    └── css/style.css
```

## Konventionen

* Sprache der Oberfläche: Deutsch, Anrede „du“ wie in den Vereinsgruppen
* Mobile first: die meisten Mitglieder kommen über einen Link aus Signal
* Beträge als `10,00 €`, Datumsangaben als `22.11.2026`
* Saubere URLs ohne `.php` (nginx-Muster aus ago-sofi-site)
* Keine Abhängigkeiten von CDNs
* Commit-Nachrichten auf Deutsch, kurz und sachlich
