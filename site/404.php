<?php
require __DIR__ . '/inc/helpers.php';
$title = 'Seite nicht gefunden – AG Orion Astrokalender ' . KALENDER_JAHR;
$activeNav = '';

require __DIR__ . '/inc/header.php';
?>
            <div class="error-404">
                <h1>Diese Seite gibt es nicht.</h1>
                <p>Fehler 404 – vielleicht ein Tippfehler im Link?</p>
                <p><a href="/">Zur Kalenderbestellung</a></p>
            </div>
<?php
require __DIR__ . '/inc/footer.php';
