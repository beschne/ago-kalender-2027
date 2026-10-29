<?php
// Einfacher CSRF-Schutz für alle POST-Formulare (Muster aus ago-sofi-site).
// Das Session-Cookie ist das einzige Cookie der Seite und technisch notwendig.

function session_starten(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    // Keine vom Browser vorgegebenen Session-IDs annehmen (Session Fixation). Das Präfix
    // __Host- verhindert zusätzlich, dass andere agorion.de-Subdomains das Cookie setzen.
    ini_set('session.use_strict_mode', '1');
    session_name($https ? '__Host-kalender' : 'kalender');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string {
    session_starten();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): void {
    session_starten();
    $submitted = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !is_string($submitted) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        die('Ungültiges Formular (Sitzung abgelaufen). Bitte Seite neu laden und erneut versuchen.');
    }
}
