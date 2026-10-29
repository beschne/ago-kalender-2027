<?php
// Öffentliche Bestellseite: Einleitung, Formular, Bestätigung nach dem Absenden
// (Post/Redirect/Get, damit ein Neuladen keine doppelte Bestellung erzeugt).
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/csrf.php';

$offen = bestellung_offen();
$fehler = [];
$eingabe = ['name' => '', 'anzahl' => '1', 'bemerkung' => '', 'datenschutz' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $offen) {
    csrf_verify();

    // Honeypot: für Menschen unsichtbares Feld, Bots füllen es aus. Kein Hinweis an den Absender.
    if (($_POST['website'] ?? '') !== '') {
        header('Location: /');
        exit;
    }

    [$daten, $fehler] = bestellung_validieren($_POST);
    if (empty($_POST['datenschutz'])) {
        $fehler[] = 'Bitte bestätige, dass du den Datenschutzhinweis gelesen hast.';
    }
    if (($_SESSION['bestellungen_anzahl'] ?? 0) >= BESTELLUNGEN_PRO_SESSION) {
        $fehler[] = 'Du hast schon mehrere Bestellungen abgeschickt. Für weitere melde dich bitte direkt bei '
            . KONTAKT_NAME . ' per Signal oder PN.';
    }

    if (!$fehler) {
        $stmt = ago_kalender_db()->prepare(
            "INSERT INTO bestellungen (erstellt_am, name, anzahl, bemerkung, quelle) VALUES (?, ?, ?, ?, 'formular')"
        );
        $stmt->execute([jetzt()->format('c'), $daten['name'], $daten['anzahl'], $daten['bemerkung']]);

        $_SESSION['bestellungen_anzahl'] = ($_SESSION['bestellungen_anzahl'] ?? 0) + 1;
        $_SESSION['bestaetigung'] = ['name' => $daten['name'], 'anzahl' => $daten['anzahl']];
        header('Location: /?bestellt=1', true, 303);
        exit;
    }

    $eingabe = [
        'name' => eingabe_text($_POST['name'] ?? ''),
        'anzahl' => eingabe_text($_POST['anzahl'] ?? ''),
        'bemerkung' => eingabe_text($_POST['bemerkung'] ?? '', true),
        'datenschutz' => !empty($_POST['datenschutz']),
    ];
}

$bestaetigung = null;
if (isset($_GET['bestellt'])) {
    session_starten();
    $bestaetigung = $_SESSION['bestaetigung'] ?? null;
    if ($bestaetigung === null) {
        header('Location: /');
        exit;
    }
} elseif ($offen) {
    csrf_token(); // Session-Cookie muss gesetzt sein, bevor unten HTML-Ausgabe beginnt
}

$bestellt = ZAEHLER_ANZEIGEN ? kalender_bestellt_summe(ago_kalender_db()) : 0;

$title = 'Astrokalender ' . KALENDER_JAHR . ' bestellen – AG Orion';
$activeNav = 'bestellen';
require __DIR__ . '/inc/header.php';
?>
<?php if ($bestaetigung !== null): ?>
    <?php $betrag = euro($bestaetigung['anzahl'] * PREIS_CENT); ?>
            <h1>Danke, <?= h($bestaetigung['name']) ?>!</h1>
            <p class="lead">
                Deine Bestellung über <strong><?= (int) $bestaetigung['anzahl'] ?> Kalender</strong>
                ist eingegangen. Der Gesamtbetrag ist <strong><?= h($betrag) ?></strong>.
            </p>

            <section class="konto" aria-labelledby="konto-titel">
                <h2 id="konto-titel">Bitte überweise <?= h($betrag) ?> an:</h2>
                <dl>
                    <dt>Empfänger</dt>
                    <dd><?= h(KONTO_EMPFAENGER) ?></dd>
                    <dt>IBAN</dt>
                    <dd class="kopierbar"><?= h(KONTO_IBAN) ?></dd>
                    <dt>BIC</dt>
                    <dd class="kopierbar"><?= h(KONTO_BIC) ?></dd>
                    <dt>Bank</dt>
                    <dd><?= h(KONTO_BANK) ?></dd>
                    <dt>Betrag</dt>
                    <dd><?= h($betrag) ?></dd>
                    <dt>Verwendungszweck</dt>
                    <dd class="kopierbar"><?= h(verwendungszweck($bestaetigung['name'])) ?></dd>
                </dl>
            </section>

            <p class="hinweis-box">
                Mach dir am besten einen <strong>Screenshot</strong> dieser Seite. Eine Bestätigung per
                E-Mail gibt es nicht, weil wir keine E-Mail-Adresse speichern.
            </p>
            <p>
                Die Kalender bekommst du <?= h(UEBERGABE) ?>. Möchtest du etwas ändern oder
                stornieren, schreib <?= h(KONTAKT_NAME) ?> einfach per Signal oder PN.
            </p>
    <?php if (ZAEHLER_ANZEIGEN): ?>
            <p class="zaehler"><?= zaehler_satz($bestellt) ?></p>
    <?php endif; ?>
            <p><a href="/">Noch eine Bestellung aufgeben</a></p>

<?php else: ?>
            <h1>Astrokalender <?= KALENDER_JAHR ?> bestellen</h1>
            <p class="lead">
                Auch für <?= KALENDER_JAHR ?> gibt es wieder den Astrokalender der AG Orion.
                Hier kannst du deine Exemplare bestellen.
            </p>

            <dl class="fakten">
                <dt>Format</dt>
                <dd><?= h(KALENDER_FORMAT) ?></dd>
                <dt>Preis</dt>
                <dd><?= h(euro(PREIS_CENT)) ?> pro Kalender</dd>
                <dt>Bestellschluss</dt>
                <dd><?= h(datum_lang(bestellschluss())) ?></dd>
                <dt>Bezahlung</dt>
                <dd>per Überweisung, die Kontodaten siehst du nach dem Absenden</dd>
                <dt>Übergabe</dt>
                <dd><?= h(UEBERGABE) ?></dd>
            </dl>

            <p class="muted">
                Ein eventueller Überschuss geht als Spende an den Verein.
            </p>

    <?php if (ZAEHLER_ANZEIGEN): ?>
            <p class="zaehler"><?= zaehler_satz($bestellt) ?></p>
    <?php endif; ?>

    <?php if (!$offen): ?>
            <div class="hinweis-box">
                <p><strong>Die Bestellung ist geschlossen.</strong></p>
                <p>
                    Bestellschluss war am <?= h(datum_lang(bestellschluss())) ?>. Wenn du noch einen
                    Kalender möchtest, schreib <?= h(KONTAKT_NAME) ?> bitte per Signal oder PN.
                </p>
            </div>
    <?php else: ?>
            <h2>Bestellformular</h2>

        <?php if ($fehler): ?>
            <div class="fehler" role="alert">
                <ul>
                <?php foreach ($fehler as $meldung): ?>
                    <li><?= h($meldung) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

            <form method="post" action="/" class="bestellformular">
                <?= csrf_field() ?>

                <label for="name">Name <span class="pflicht">*</span></label>
                <input type="text" id="name" name="name" required maxlength="<?= NAME_MAX ?>"
                       autocomplete="name" value="<?= h($eingabe['name']) ?>">
                <p class="feld-hinweis">Vor- und Nachname, damit wir deine Überweisung zuordnen können.</p>

                <label for="anzahl">Anzahl Kalender <span class="pflicht">*</span></label>
                <input type="number" id="anzahl" name="anzahl" required min="1" max="<?= ANZAHL_MAX ?>"
                       step="1" inputmode="numeric" value="<?= h($eingabe['anzahl']) ?>">

                <label for="bemerkung">Bemerkung <span class="optional">(optional)</span></label>
                <textarea id="bemerkung" name="bemerkung" maxlength="<?= BEMERKUNG_MAX ?>" rows="3"
                          placeholder="z. B. „Kalender holt Maria für mich ab“"><?= h($eingabe['bemerkung']) ?></textarea>

                <div class="honeypot" aria-hidden="true">
                    <label for="website">Bitte leer lassen</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <label class="checkbox">
                    <input type="checkbox" name="datenschutz" value="1" required<?= $eingabe['datenschutz'] ? ' checked' : '' ?>>
                    <span>Ich habe den <a href="/datenschutz" target="_blank">Datenschutzhinweis</a> gelesen. <span class="pflicht">*</span></span>
                </label>

                <button type="submit" class="btn">Verbindlich bestellen</button>
                <p class="feld-hinweis">
                    Preis: <?= h(euro(PREIS_CENT)) ?> pro Kalender. Änderungen und Stornos bitte per
                    Signal oder PN an <?= h(KONTAKT_NAME) ?>.
                </p>
            </form>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php';
