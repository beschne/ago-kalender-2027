#!/usr/bin/env bash
# Deployt den Inhalt von site/ per rsync auf den Server.
# Siehe DEPLOYMENT.md fuer den einmaligen Setup (Verzeichnisse, nginx, TLS, htpasswd).
# Die SQLite-Datenbank liegt ausserhalb des Webroots und wird nie uebertragen.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SITE_DIR="$SCRIPT_DIR/../site"

REMOTE_HOST="ago-server"   # SSH-Alias aus ~/.ssh/config (Server, Benutzer, Key), siehe DEPLOYMENT.md
REMOTE_PATH="/var/www/kalender.agorion.de/"

rsync -avz --delete \
    --exclude ".*" \
    --exclude "*.sqlite*" \
    "$SITE_DIR/" "$REMOTE_HOST:$REMOTE_PATH"

echo "Deploy abgeschlossen: https://kalender.agorion.de"
