<?php
/**
 * Gemeinsamer Seitenkopf. Erwartet vor dem include gesetzt:
 * $title (string), $activeNav ("bestellen"|"datenschutz"|"impressum"|"").
 */
require_once __DIR__ . '/helpers.php';

function ago_kalender_nav_class(string $item, string $activeNav): string {
    return $item === $activeNav ? ' class="current" aria-current="page"' : '';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= h($title) ?></title>
    <?php
    $vorschauText = bestellung_offen()
        ? 'Jetzt den Astrokalender ' . KALENDER_JAHR . ' der AG Orion bestellen: '
            . euro(PREIS_CENT) . ' pro Kalender, Bestellschluss ' . bestellschluss()->format('d.m.Y') . '.'
        : 'Die Bestellung für den Astrokalender ' . KALENDER_JAHR . ' der AG Orion ist geschlossen.';
    ?>
    <meta name="description" content="<?= h($vorschauText) ?>">
    <!-- Link-Vorschau in WhatsApp, Signal & Co. -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
    <meta property="og:site_name" content="AG Orion">
    <meta property="og:title" content="Astrokalender <?= KALENDER_JAHR ?> bestellen – AG Orion">
    <meta property="og:description" content="<?= h($vorschauText) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>/">
    <meta property="og:image" content="<?= SITE_URL ?>/img/vorschau.png?v=<?= @filemtime(__DIR__ . '/../img/vorschau.png') ?: '1' ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Astrokalender <?= KALENDER_JAHR ?> der AG Orion">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="/css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?: '1' ?>">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
</head>
<body>
    <div id="page">
        <header id="branding">
            <p id="site-title"><a href="/">AG Orion – Astrokalender <?= KALENDER_JAHR ?></a></p>
            <p id="site-description">Bestellung für Mitglieder der Astronomischen Gesellschaft Orion Bad Homburg</p>
            <a href="/" class="branding-image">
                <?php $bannerV = @filemtime(__DIR__ . '/../img/banner-940.jpg') ?: '1'; ?>
                <img src="/img/banner-940.jpg?v=<?= $bannerV ?>"
                     srcset="/img/banner-300.jpg?v=<?= $bannerV ?> 300w,
                             /img/banner-768.jpg?v=<?= $bannerV ?> 768w,
                             /img/banner-940.jpg?v=<?= $bannerV ?> 940w"
                     sizes="(max-width: 940px) 100vw, 940px"
                     width="940" height="198" alt="Astronomische Gesellschaft Orion">
            </a>
            <nav id="access">
                <div>
                    <ul>
                        <li><a href="/"<?= ago_kalender_nav_class('bestellen', $activeNav) ?>>Kalender bestellen</a></li>
                        <li><a href="/datenschutz"<?= ago_kalender_nav_class('datenschutz', $activeNav) ?>>Datenschutz</a></li>
                        <li><a href="/impressum"<?= ago_kalender_nav_class('impressum', $activeNav) ?>>Impressum</a></li>
                    </ul>
                </div>
            </nav>
        </header>

        <main>
