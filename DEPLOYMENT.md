# Deployment

Die Seite wird wie ago-sofi-site **direkt vom Mac per rsync** ausgerollt. Auf dem Server
liegt kein Git-Checkout, nur der Inhalt von `site/` unter `/var/www/kalender.agorion.de`.

* Server: `92.205.236.81` (gleicher Server wie sofi.agorion.de)
* SSH-User: `cerberus`, Deploy-Key `~/.ssh/ago-sofi-deploy` (bereits eingerichtet)
* Webroot: `/var/www/kalender.agorion.de` (entspricht 1:1 `site/`)
* Datenbank: `/var/www/kalender.agorion.de-data/bestellungen.sqlite` (außerhalb des Webroots)

## Einmalige Einrichtung (Schritte 1–4 erledigt am 27.10.2026)

### 1. Verzeichnisse

```bash
ssh cerberus@92.205.236.81 'sudo mkdir -p /var/www/kalender.agorion.de && sudo chown cerberus:cerberus /var/www/kalender.agorion.de && sudo chmod 755 /var/www/kalender.agorion.de'
ssh cerberus@92.205.236.81 'sudo mkdir -p /var/www/kalender.agorion.de-data && sudo chown www-data:www-data /var/www/kalender.agorion.de-data && sudo chmod 750 /var/www/kalender.agorion.de-data'
```

Das Datenverzeichnis gehört `www-data`, weil PHP-FPM als `www-data` läuft und SQLite
neben der Datenbank Journal-Dateien anlegt. Die Datenbank und die Tabelle entstehen beim
ersten Seitenaufruf automatisch (`CREATE TABLE IF NOT EXISTS` in `site/inc/db.php`).

Voraussetzung: `php8.2-sqlite3` ist installiert. Prüfen mit
`ssh cerberus@92.205.236.81 'php8.2 -m | grep -i sqlite'`, sonst
`sudo apt install php8.2-sqlite3 && sudo systemctl reload php8.2-fpm`.

### 2. Erstes Deploy

```bash
./deploy/deploy.sh
```

### 3. nginx

Die Konfiguration liegt unter [`deploy/nginx/kalender.agorion.de.conf`](deploy/nginx/kalender.agorion.de.conf)
und soll 1:1 der Datei auf dem Server entsprechen.

```bash
scp -i ~/.ssh/ago-sofi-deploy deploy/nginx/kalender.agorion.de.conf cerberus@92.205.236.81:/tmp/kalender.agorion.de.conf
ssh cerberus@92.205.236.81 'sudo mv /tmp/kalender.agorion.de.conf /etc/nginx/sites-available/kalender.agorion.de && sudo chown root:root /etc/nginx/sites-available/kalender.agorion.de && sudo ln -sf /etc/nginx/sites-available/kalender.agorion.de /etc/nginx/sites-enabled/kalender.agorion.de && sudo nginx -t && sudo systemctl reload nginx'
```

Das Access-Log der Seite (`/var/log/nginx/kalender.agorion.de-access.log`) speichert IP-Adressen
nur gekürzt (`map`/`log_format` in der Konfiguration). Beide `server`-Blöcke (HTTPS und der
Redirect auf Port 80) setzen ein eigenes `access_log` und heben damit das globale
`/var/log/nginx/access.log` mit voller IP für diese Seite auf. Das globale
`/var/log/nginx/error.log` enthält bei Fehlern volle IP-Adressen (gewollt, fail2ban liest es);
logrotate löscht es nach 14 Tagen.

### 4. TLS-Zertifikat

```bash
ssh cerberus@92.205.236.81 'sudo certbot --nginx -d kalender.agorion.de --non-interactive --agree-tos -m benno.schneider@gmail.com --redirect'
```

Certbot ergänzt dabei die TLS- und Redirect-Blöcke in der Server-Konfiguration. Danach die
Datei zurück ins Repo holen, damit Repo und Server übereinstimmen:

```bash
ssh cerberus@92.205.236.81 'cat /etc/nginx/sites-available/kalender.agorion.de' > deploy/nginx/kalender.agorion.de.conf
```

### 5. Admin-Zugang (HTTP Basic Auth)

Passwort mit bcrypt (`-B`), interaktiv abgefragt, damit es nicht in der Shell-History landet
(`htpasswd` stammt aus `apache2-utils`):

```bash
ssh -t cerberus@92.205.236.81 'sudo htpasswd -B -C 12 /etc/nginx/.htpasswd-kalender-admin benno && sudo chmod 640 /etc/nginx/.htpasswd-kalender-admin && sudo chown root:www-data /etc/nginx/.htpasswd-kalender-admin'
```

Beim allerersten Anlegen zusätzlich `-c`. Weitere Personen mit demselben Befehl und anderem
Namen eintragen. Die Datei liegt außerhalb des Webroots und ist nicht Teil des Deploys.
Fehlgeschlagene Anmeldungen sperrt fail2ban über das Jail `nginx-http-auth`.

### 6. Upload-Link (optional)

Der Link zur HiDrive-Freigabe für Bilder erscheint nur im Admin-Bereich und wird aus einer
Textdatei neben der Datenbank gelesen, damit er nicht im öffentlichen Repo steht:

```bash
ssh -i ~/.ssh/ago-sofi-deploy cerberus@92.205.236.81 'echo "https://my.hidrive.com/share/<id>" | sudo -u www-data tee /var/www/kalender.agorion.de-data/upload-url.txt > /dev/null'
```

Leeren oder löschen blendet den Link aus. Lokal: `dev-data/upload-url.txt`.

## Deploy-Ablauf (nach jeder Änderung)

```bash
./deploy/deploy.sh
```

`rsync --delete` gleicht `site/` mit dem Webroot ab. Die Datenbank liegt in einem anderen
Verzeichnis und wird dabei nie angefasst.

## Backup der Bestellungen (manuell)

```bash
./deploy/backup.sh
```

Erzeugt auf dem Server per `VACUUM INTO` eine konsistente Kopie der Datenbank und legt sie als
`./backups/<Zeitstempel>-ago-kalender-backup.sqlite` (Modus 600) ab. Der Ordner `backups/`
steht in `.gitignore` und wird nicht mit deployt. Empfehlenswert vor
Bestellschluss und vor der Weihnachtsfeier. Die Kopien enthalten Namen und müssen zur
Löschfrist ebenfalls gelöscht werden (siehe unten). Wiederherstellen: Datei per `scp` nach
`/tmp` kopieren und als `www-data` nach `/var/www/kalender.agorion.de-data/bestellungen.sqlite`
verschieben.

## Fehlerseiten

* 404: `site/404.php`
* 500/502/503/504: `site/50x.html` (statisch). nginx zeigt sie, wenn PHP-FPM nicht antwortet;
  unbehandelte PHP-Fehler (z. B. Datenbank nicht erreichbar) zeigt `inc/helpers.php` mit
  derselben Datei an und schreibt den Fehler ins PHP-Log.

## Lokale Entwicklung

```bash
mkdir -p dev-data
AGO_KALENDER_DB="$PWD/dev-data/bestellungen.sqlite" php -S localhost:8000 -t site dev/router.php
```

Dann http://localhost:8000 (Formular) und http://localhost:8000/admin/ (lokal ohne Passwort).
`dev-data/` ist in `.gitignore`.

Zustand nach Bestellschluss testen:

```bash
AGO_KALENDER_JETZT="2026-11-23 08:00" AGO_KALENDER_DB="$PWD/dev-data/bestellungen.sqlite" php -S localhost:8000 -t site dev/router.php
```

## Nach der Aktion

1. Im Admin-Bereich CSV exportieren und, falls der Kassenwart eine Aufstellung braucht, an ihn geben.
2. „Alle Bestellungen löschen“ (spätestens 31.01.2027). Die Datenbank wird dabei per `VACUUM`
   neu geschrieben, gelöschte Daten bleiben nicht in der Datei zurück.
3. Lokale CSV-Exporte und Backups (`backups/*-ago-kalender-backup.sqlite`) auf dem Mac
   ebenfalls löschen.
