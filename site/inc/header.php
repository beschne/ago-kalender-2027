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
                <picture>
                    <!-- Handy: ohne Vereinsname über dem Titel, der wäre dort unlesbar klein (dev/banner.py) -->
                    <source media="(max-width: 600px)" srcset="/img/banner-mobil-940.jpg?v=<?= $bannerV ?>">
                    <img src="/img/banner-940.jpg?v=<?= $bannerV ?>"
                         srcset="/img/banner-300.jpg?v=<?= $bannerV ?> 300w,
                                 /img/banner-768.jpg?v=<?= $bannerV ?> 768w,
                                 /img/banner-940.jpg?v=<?= $bannerV ?> 940w"
                         sizes="(max-width: 940px) 100vw, 940px"
                         width="940" height="198"
                         alt="Astrokalender <?= KALENDER_JAHR ?>: Milchstraße über dem Horizont, davor Teleskop, Smartscope und Kamera">
                </picture>
            </a>
            <nav id="access">
                <div>
                    <ul>
                        <li><a href="/"<?= ago_kalender_nav_class('bestellen', $activeNav) ?>>Kalender bestellen</a></li>
                        <?php if (UPLOAD_URL !== ''): ?>
                        <li><a href="<?= h(UPLOAD_URL) ?>" target="_blank" rel="noopener noreferrer">Bilder hochladen<svg class="extern" viewBox="0 0 12 12" width="11" height="11" aria-hidden="true" focusable="false"><path d="M7 1h4v4M11 1 5.5 6.5M9.5 7.5V11H1V2.5h3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="visually-hidden"> (öffnet in neuem Fenster)</span></a></li>
                        <?php endif; ?>
                        <li><a href="/datenschutz"<?= ago_kalender_nav_class('datenschutz', $activeNav) ?>>Datenschutz</a></li>
                        <li><a href="/impressum"<?= ago_kalender_nav_class('impressum', $activeNav) ?>>Impressum</a></li>
                    </ul>
                </div>
            </nav>
        </header>

        <main>
