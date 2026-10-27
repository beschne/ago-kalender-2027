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
