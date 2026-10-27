<?php
// Router für den PHP-Built-in-Server, nur für die lokale Entwicklung (wird nicht deployt).
// Bildet die nginx-Regeln nach: saubere URLs ohne .php, /inc/ gesperrt, eigene 404-Seite.
// Basic Auth für /admin/ gibt es lokal nicht.

$root = $_SERVER['DOCUMENT_ROOT'];
$pfad = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

if (str_starts_with($pfad, '/inc/') || str_contains($pfad, '..')) {
    http_response_code(403);
    exit('Verboten');
}

if (is_file($root . $pfad)) {
    return false; // statische Datei oder .php direkt
}

if (is_dir($root . $pfad) && is_file($root . rtrim($pfad, '/') . '/index.php')) {
    require $root . rtrim($pfad, '/') . '/index.php';
    return true;
}

if (is_file($root . $pfad . '.php')) {
    require $root . $pfad . '.php';
    return true;
}

http_response_code(404);
require $root . '/404.php';
