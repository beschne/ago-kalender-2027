# Deployment

Die Seite wird wie ago-sofi-site **direkt vom Mac per rsync** ausgerollt. Auf dem Server
liegt kein Git-Checkout, nur der Inhalt von `site/` unter `/var/www/kalender.agorion.de`.

* Server: derselbe wie sofi.agorion.de, angesprochen über den SSH-Alias `ago-server`
* SSH-User: `cerberus`, nur Login per Key `~/.ssh/ago-sofi-deploy` (Passwort-Login ist aus)
* Webroot: `/var/www/kalender.agorion.de` (entspricht 1:1 `site/`)
* Datenbank: `/var/www/kalender.agorion.de-data/bestellungen.sqlite` (außerhalb des Webroots)

## SSH-Zugang

Alle Skripte und Befehle hier nutzen den Alias `ago-server`. Einmalig in `~/.ssh/config`:

```text
Host ago-server
  HostName kalender.agorion.de
  User cerberus
  IdentityFile ~/.ssh/ago-sofi-deploy
  IdentitiesOnly yes
```

`HostName` kann auch die IP-Adresse des Servers sein (Domain-Factory-Kundenmenü). Test:
`ssh ago-server 'echo ok'`. Ohne Key gibt es keinen Zugang mehr; im Notfall hilft die
Konsole im Kundenmenü.

## Einmalige Einrichtung (Schritte 1–4 erledigt am 27.10.2026, eigener PHP-Pool seit 29.10.2026)

### 1. Benutzer, Verzeichnisse und PHP-Pool

PHP läuft für diese Seite in einem eigenen FPM-Pool als Benutzer `kalender`, nicht als
`www-data`. So können die anderen Seiten auf dem Server (u. a. WordPress) die Bestelldaten
weder lesen noch ändern. Die Pool-Datei liegt im Repo unter
[`deploy/php/kalender.conf`](deploy/php/kalender.conf) und entspricht 1:1
`/etc/php/8.2/fpm/pool.d/kalender.conf`.

```bash
ssh ago-server 'sudo useradd --system --user-group --no-create-home --home-dir /nonexistent --shell /usr/sbin/nologin kalender'
ssh ago-server 'sudo mkdir -p /var/www/kalender.agorion.de && sudo chown cerberus:cerberus /var/www/kalender.agorion.de && sudo chmod 755 /var/www/kalender.agorion.de'
ssh ago-server 'sudo install -d -o kalender -g kalender -m 750 /var/www/kalender.agorion.de-data && sudo install -d -o kalender -g kalender -m 700 /var/lib/php/sessions-kalender'
scp deploy/php/kalender.conf ago-server:/tmp/
ssh ago-server 'sudo install -o root -g root -m 644 /tmp/kalender.conf /etc/php/8.2/fpm/pool.d/kalender.conf && rm /tmp/kalender.conf && sudo php-fpm8.2 -t && sudo systemctl reload php8.2-fpm'
```

Der Webroot gehört `cerberus` (Deploy) und ist für PHP nur lesbar. Das Datenverzeichnis gehört
`kalender`, weil SQLite neben der Datenbank Journal-Dateien anlegt. Die Datenbank und die
Tabelle entstehen beim ersten Seitenaufruf automatisch (`CREATE TABLE IF NOT EXISTS` in
`site/inc/db.php`).

Voraussetzung: `php8.2-sqlite3` ist installiert. Prüfen mit
`ssh ago-server 'php8.2 -m | grep -i sqlite'`, sonst
`sudo apt install php8.2-sqlite3 && sudo systemctl reload php8.2-fpm`.

### 2. Erstes Deploy

```bash
./deploy/deploy.sh
```

### 3. nginx

Die Konfiguration liegt unter [`deploy/nginx/kalender.agorion.de.conf`](deploy/nginx/kalender.agorion.de.conf)
und soll 1:1 der Datei auf dem Server entsprechen.

```bash
scp deploy/nginx/kalender.agorion.de.conf ago-server:/tmp/kalender.agorion.de.conf
ssh ago-server 'sudo mv /tmp/kalender.agorion.de.conf /etc/nginx/sites-available/kalender.agorion.de && sudo chown root:root /etc/nginx/sites-available/kalender.agorion.de && sudo ln -sf /etc/nginx/sites-available/kalender.agorion.de /etc/nginx/sites-enabled/kalender.agorion.de && sudo nginx -t && sudo systemctl reload nginx'
```

Das Access-Log der Seite (`/var/log/nginx/kalender.agorion.de-access.log`) speichert IP-Adressen
nur gekürzt (`map`/`log_format` in der Konfiguration). Beide `server`-Blöcke (HTTPS und der
Redirect auf Port 80) setzen ein eigenes `access_log` und heben damit das globale
`/var/log/nginx/access.log` mit voller IP für diese Seite auf. Das globale
`/var/log/nginx/error.log` enthält bei Fehlern volle IP-Adressen (gewollt, fail2ban liest es);
logrotate löscht es nach 14 Tagen.

### 4. TLS-Zertifikat

```bash
ssh ago-server 'sudo certbot --nginx -d kalender.agorion.de --non-interactive --agree-tos -m benno.schneider@gmail.com --redirect'
```

Certbot ergänzt dabei die TLS- und Redirect-Blöcke in der Server-Konfiguration. Danach die
Datei zurück ins Repo holen, damit Repo und Server übereinstimmen:

```bash
ssh ago-server 'cat /etc/nginx/sites-available/kalender.agorion.de' > deploy/nginx/kalender.agorion.de.conf
```

### 5. Admin-Zugang (HTTP Basic Auth)

Passwort mit bcrypt (`-B`), interaktiv abgefragt, damit es nicht in der Shell-History landet
(`htpasswd` stammt aus `apache2-utils`):

```bash
ssh -t ago-server 'sudo htpasswd -B -C 12 /etc/nginx/.htpasswd-kalender-admin benno && sudo chmod 640 /etc/nginx/.htpasswd-kalender-admin && sudo chown root:www-data /etc/nginx/.htpasswd-kalender-admin'
```

Beim allerersten Anlegen zusätzlich `-c`. Weitere Personen mit demselben Befehl und anderem
Namen eintragen. Die Datei liegt außerhalb des Webroots und ist nicht Teil des Deploys.
Fehlgeschlagene Anmeldungen sperrt fail2ban über das Jail `nginx-http-auth`.

### 6. Upload-Link (optional)

Der Link zur HiDrive-Freigabe für Bilder erscheint nur im Admin-Bereich und wird aus einer
Textdatei neben der Datenbank gelesen, damit er nicht im öffentlichen Repo steht:

```bash
ssh ago-server 'echo "https://my.hidrive.com/share/<id>" | sudo -u kalender tee /var/www/kalender.agorion.de-data/upload-url.txt > /dev/null'
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
`/tmp` kopieren und als `kalender` nach `/var/www/kalender.agorion.de-data/bestellungen.sqlite`
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
