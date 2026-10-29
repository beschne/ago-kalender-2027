<?php
// Zweite Absicherung für den Admin-Bereich. Den Zugang regelt nginx per auth_basic; fehlt der
// angemeldete Benutzer, ist die nginx-Konfiguration falsch und es wird nichts ausgeliefert.
// Der PHP-Built-in-Server der lokalen Entwicklung kennt kein Basic Auth.
if (PHP_SAPI !== 'cli-server' && empty($_SERVER['REMOTE_USER'])) {
    http_response_code(403);
    exit('Kein Zugriff.');
}

// Bestelldaten nicht im Browser-Cache ablegen.
header('Cache-Control: no-store');
