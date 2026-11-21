// Sicherheitsabfrage für Formulare mit data-confirm (Löschen). Als eigene Datei statt
// Inline-Handler, damit die Content-Security-Policy kein 'unsafe-inline' braucht.
document.addEventListener('submit', function (event) {
    var nachricht = event.target.getAttribute('data-confirm');
    if (nachricht && !window.confirm(nachricht)) {
        event.preventDefault();
    }
});

// Druck-Knopf der Übergabeliste.
document.addEventListener('click', function (event) {
    if (event.target.closest('[data-drucken]')) {
        window.print();
    }
});

// Knopf „Nachricht“: Antworttext für Signal/PN in die Zwischenablage kopieren.
// Ersatzweg über ein verstecktes Textfeld, falls die Clipboard-API fehlt (z. B. ohne HTTPS).
document.addEventListener('click', function (event) {
    var knopf = event.target.closest('[data-nachricht]');
    if (!knopf) {
        return;
    }
    var text = knopf.getAttribute('data-nachricht');
    var bestaetigen = function () {
        var alt = knopf.textContent;
        knopf.textContent = '✓ kopiert';
        setTimeout(function () { knopf.textContent = alt; }, 1500);
    };
    var ersatz = function () {
        var feld = document.createElement('textarea');
        feld.value = text;
        feld.setAttribute('readonly', '');
        feld.style.position = 'fixed';
        feld.style.opacity = '0';
        document.body.appendChild(feld);
        feld.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(feld);
        if (ok) {
            bestaetigen();
        } else {
            window.alert(text);
        }
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(bestaetigen, ersatz);
    } else {
        ersatz();
    }
});
