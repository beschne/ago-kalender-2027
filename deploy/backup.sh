#!/usr/bin/env bash
# Manuelles Backup der Bestelldatenbank auf den Mac (Muster: ago-sofi-site).
# Erzeugt auf dem Server eine konsistente Kopie per "VACUUM INTO" (auch während
# gerade bestellt wird) und lädt sie herunter. Der Code liegt in GitHub.
# Aufruf: ./deploy/backup.sh
#
# Datenschutz: Die Kopie enthält Namen. Alle Backups spätestens zur Löschfrist
# (31.01.2027) ebenfalls löschen, siehe DEPLOYMENT.md.
set -euo pipefail
umask 077   # Backup ist von Anfang an nur für den eigenen Benutzer lesbar

REMOTE_HOST="cerberus@92.205.236.81"
SSH_KEY="$HOME/.ssh/ago-sofi-deploy"
REMOTE_DB="/var/www/kalender.agorion.de-data/bestellungen.sqlite"
ZIEL_DIR="$(cd "$(dirname "$0")/.." && pwd)/backups"

ZEITSTEMPEL="$(date +%Y-%m-%d_%H%M)"
ZIEL_DATEI="$ZIEL_DIR/$ZEITSTEMPEL-ago-kalender-backup.sqlite"
mkdir -p "$ZIEL_DIR"

echo "Datenbank sichern..."
ssh -i "$SSH_KEY" -o IdentitiesOnly=yes "$REMOTE_HOST" "
    set -e
    KOPIE=\$(sudo -u www-data mktemp /tmp/ago-kalender-backup.XXXXXX)
    sudo -u www-data rm -f \"\$KOPIE\"
    sudo -u www-data php -r '(new PDO(\"sqlite:$REMOTE_DB\"))->exec(\"VACUUM INTO \" . var_export(\$argv[1], true));' \"\$KOPIE\"
    sudo cat \"\$KOPIE\"
    sudo rm -f \"\$KOPIE\"
" > "$ZIEL_DATEI"
chmod 600 "$ZIEL_DATEI"

# Kurze Plausibilitätsprüfung mit dem lokalen sqlite3.
ANZAHL="$(sqlite3 "$ZIEL_DATEI" 'SELECT COUNT(*) || " Bestellungen, " || COALESCE(SUM(anzahl), 0) || " Kalender" FROM bestellungen')"
echo "Fertig: $ZIEL_DATEI ($ANZAHL)"
echo "Hinweis: Backup spätestens am 31.01.2027 löschen."
