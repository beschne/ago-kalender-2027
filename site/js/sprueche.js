// Zufällige Sprüche zur Motivation vor dem Bestellschluss. Zeigt beim Laden einen zufälligen
// Spruch und wechselt dann alle paar Sekunden zu einem anderen (nie zweimal derselbe hintereinander).
(function () {
    'use strict';

    const SPRUECHE = [
        'Zwölf Monate Sternenhimmel, fotografiert von unseren eigenen Mitgliedern.',
        'Für 10 € holst du dir den Himmel über dem Taunus an die Wand, und ein Überschuss geht an den Verein.',
        'Hinter jedem Bild stecken Stunden, oft mehrere Nächte unter dem Sternenhimmel. Bei dir hängt es einen ganzen Monat.',
        'Unterstütze unsere Astrofotografen und die AG Orion gleich mit.',
        'Das schönste Geschenk für alle, die gern nach oben schauen.',
        'Zeig deinen Gästen, was unsere Mitglieder am Himmel entdeckt haben.',
        'Bestellschluss ist am 22. November. Der Himmel wartet, unser Kalender nicht.',
        'Endlich ein Grund, sich auf jeden Monatswechsel zu freuen.',
        'Damit du bei jedem Termin an die nächste klare Nacht denkst.',
        'Hier erlebst du Galaxien ohne Wolken, ohne Taupunkt und ohne kalte Füße.',
        'Kein Seeing, kein Mondlicht, keine Nachführfehler. Nur das fertige Bild.',
        'Ein Kalender, bei dem selbst der Januar klare Nächte hat.',
        'Klarer Himmel an 365 Tagen, garantiert.',
        '13 Blätter, null Prozent Lichtverschmutzung.',
        'So viel Belichtungszeit hattest du noch nie an der Wand.',
        'Manches Licht in diesem Kalender war Jahrtausende zu uns unterwegs. Dein Kalender braucht nur bis Weihnachten.',
    ];
    const INTERVALL_MS = 10000;
    const UEBERGANG_MS = 400; // muss zur transition von .spruch in style.css passen

    const el = document.getElementById('spruch');
    if (!el) {
        return;
    }

    let aktuell = -1;
    function naechster() {
        let i;
        do {
            i = Math.floor(Math.random() * SPRUECHE.length);
        } while (i === aktuell && SPRUECHE.length > 1);
        aktuell = i;
        return SPRUECHE[i];
    }

    el.textContent = naechster();
    el.hidden = false;

    setInterval(function () {
        if (document.hidden) {
            return;
        }
        el.classList.add('spruch-aus');
        setTimeout(function () {
            el.textContent = naechster();
            el.classList.remove('spruch-aus');
        }, UEBERGANG_MS);
    }, INTERVALL_MS);
})();
