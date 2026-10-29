<?php
require __DIR__ . '/inc/helpers.php';
$title = 'Datenschutz – AG Orion Astrokalender ' . KALENDER_JAHR;
$activeNav = 'datenschutz';
require __DIR__ . '/inc/header.php';
?>
            <h1>Datenschutzhinweis zur Kalenderbestellung</h1>

            <h2>Verantwortlich</h2>
            <p>
                Astronomische Gesellschaft Orion Bad Homburg e.V., vertreten durch den Vorsitzenden
                <?= h(KONTAKT_NAME) ?>, Elisabethenschneise 2, Peter-Schall-Haus, 61350 Bad Homburg,
                <a href="mailto:<?= h(KONTAKT_EMAIL) ?>"><?= h(KONTAKT_EMAIL) ?></a>.
            </p>

            <h2>Deine Bestellung</h2>
            <p>
                Wir speichern deinen Namen, die bestellte Anzahl und eine eventuelle Bemerkung
                ausschließlich, um deine Kalenderbestellung abzuwickeln, Zahlungseingänge zuzuordnen
                und die Kalender zu übergeben (Art. 6 Abs. 1 lit. b DSGVO). Zusätzlich speichern wir
                den Zeitpunkt der Bestellung und ob die Zahlung eingegangen ist.
            </p>
            <p>
                Die Daten sehen nur die für die Abwicklung zuständigen Vorstandsmitglieder. Sie werden
                nicht weitergegeben und nach Abschluss der Aktion, spätestens am <?= h(LOESCHFRIST) ?>,
                gelöscht. Auf dieser Seite wird höchstens die Gesamtzahl der bestellten Kalender
                angezeigt, nie Namen.
            </p>

            <h2>Cookies, Server-Logs, externe Inhalte</h2>
            <p>
                Die Seite setzt nur ein technisch notwendiges Sitzungs-Cookie, das das Formular gegen
                Missbrauch schützt und beim Schließen des Browsers verfällt. Es gibt kein Tracking,
                keine Analyse-Tools und keine Inhalte von anderen Anbietern; Schriften und Bilder
                kommen von unserem eigenen Server. Der Webserver protokolliert Aufrufe nur mit
                gekürzter, nicht mehr zuordenbarer IP-Adresse. Nur bei Fehlern und abgewehrten
                Angriffsversuchen speichert er die vollständige IP-Adresse im Fehlerprotokoll; es
                wird nach 14 Tagen automatisch gelöscht (berechtigtes Interesse an der Sicherheit
                der Seite, Art. 6 Abs. 1 lit. f DSGVO).
            </p>
<?php if (UPLOAD_URL !== ''): ?>
            <p>
                Ausnahme ist der Menüpunkt „Bilder hochladen“: Er führt zu unserem Speicherplatz bei
                HiDrive, einem Cloud-Speicher der STRATO AG mit Rechenzentren in Deutschland. Dort
                landen nur die Bilder, die du hochlädst; ein Konto brauchst du dafür nicht. Das
                Hochladen ist freiwillig und hat mit deiner Bestellung nichts zu tun.
            </p>
<?php endif; ?>

            <h2>Deine Rechte</h2>
            <p>
                Du hast das Recht auf Auskunft, Berichtigung und Löschung; wende dich dazu an die oben
                genannte Adresse. Außerdem hast du ein Beschwerderecht bei einer Aufsichtsbehörde,
                z. B. dem
                <a href="https://datenschutz.hessen.de">Hessischen Beauftragten für Datenschutz und Informationsfreiheit</a>.
            </p>

            <p class="muted">Stand: Oktober 2026</p>
<?php require __DIR__ . '/inc/footer.php';
