# Datenschutz – Kalenderbestellung AG Orion

Erörterung der datenschutzrechtlichen Rahmenbedingungen für die Bestellseite
des AG Orion Astrokalenders. Stand: 27.10.2026. Keine Rechtsberatung, sondern
die Grundlage für die Umsetzung in diesem Repository.

## Ausgangslage

Mitglieder bestellen den Astrokalender über ein Formular und geben dabei ihren
Namen an. Ohne Namen lassen sich Zahlungseingänge nicht zuordnen und die
Kalender nicht übergeben. Es werden also personenbezogene Daten verarbeitet,
und die DSGVO ist anwendbar.

Verantwortlicher ist die Astronomische Gesellschaft Orion Bad Homburg e.V.,
vertreten durch den Vorsitzenden.

## Rechtsgrundlage

**Art. 6 Abs. 1 lit. b DSGVO** – Verarbeitung zur Erfüllung eines Vertrags bzw.
zur Durchführung vorvertraglicher Maßnahmen. Die Bestellung eines Kalenders ist
ein solcher Vertrag zwischen Mitglied und Verein.

Eine gesonderte Einwilligung ist daher **nicht erforderlich**. Die Checkbox am
Formular dient nur der Kenntnisnahme des Datenschutzhinweises, nicht als
Einwilligung im Sinne von Art. 6 Abs. 1 lit. a.

## Datensparsamkeit (Art. 5 Abs. 1 lit. c)

Erhoben werden nur:

| Feld | Zweck | Pflicht |
|---|---|---|
| Name | Zuordnung von Zahlung und Übergabe | ja |
| Anzahl | Bestellung, Druckmenge | ja |
| Bemerkung | Sonderwünsche, z. B. Abholung durch Dritte | nein |
| Zeitpunkt | Nachvollziehbarkeit, Bestellschluss | automatisch |
| Bezahlt-Status, interne Notiz | Zahlungsabgleich | nur Admin |

**Bewusst nicht erhoben:** E-Mail-Adresse, Anschrift, Telefonnummer,
Kontoverbindung des Mitglieds. Rückfragen laufen über die bestehenden
Vereinskanäle (Signal), Zahlungen über Überweisung mit Namen im
Verwendungszweck.

## Transparenz (Art. 13)

Ein kurzer Datenschutzhinweis auf der Seite (`/datenschutz`, verlinkt am
Formular und im Footer) nennt:

* Verantwortlichen mit Kontakt
* Zweck und Rechtsgrundlage
* Empfänger (nur die zuständigen Vorstandsmitglieder, keine Weitergabe)
* Speicherdauer und Löschzeitpunkt
* Betroffenenrechte (Auskunft, Berichtigung, Löschung, Beschwerde bei der
  Aufsichtsbehörde, in Hessen der HBDI)

Der Textvorschlag steht in `CLAUDE.md`.

## Zugriff und Sichtbarkeit

* **Keine öffentliche Namensliste.** Die öffentliche Seite zeigt höchstens die
  Gesamtzahl der bestellten Kalender.
* Der Admin-Bereich ist per nginx Basic Auth geschützt. Zugang haben nur die
  für die Abwicklung zuständigen Personen.
* Keine Weitergabe an Dritte. Saal Digital erhält nur die Gesamtmenge, keine
  Namen.

## Speicherdauer und Löschung (Art. 5 Abs. 1 lit. e)

* Löschung aller Bestelldaten nach Abwicklung (Übergabe und Zahlungsabgleich
  abgeschlossen), **spätestens am 31.01.2027**.
* Der Admin-Bereich bietet dafür einen Button „Alle Bestellungen löschen“ mit
  Sicherheitsabfrage.
* Für die Vereinsbuchhaltung genügen die Kontoauszüge und die Rechnung von
  Saal Digital. Die Bestellliste selbst muss nicht aufbewahrt werden. Falls
  der Kassenwart eine Aufstellung braucht, vor dem Löschen einen CSV-Export
  an ihn geben; dieser unterliegt dann den Aufbewahrungsregeln der
  Vereinsbuchhaltung.

## Technische und organisatorische Maßnahmen (Art. 32)

* TLS-Verschlüsselung (Let's Encrypt), HTTP leitet auf HTTPS um
* SQLite-Datenbank außerhalb des Webroots, nicht im Repository, nicht im Deploy
* CSRF-Schutz, Honeypot, serverseitige Validierung, Prepared Statements,
  Ausgabe-Escaping
* Kein Tracking, keine Analyse-Tools, keine externen Ressourcen (Schriften,
  Skripte, CDNs), nur ein technisch notwendiges Session-Cookie
* nginx-Access-Log der Seite (HTTPS und HTTP) mit anonymisierten IP-Adressen; das
  Error-Log enthält volle IP-Adressen (Fehler, Angriffsversuche, fail2ban), Löschung nach
  14 Tagen per logrotate
* Rate-Limits in nginx für Bestellungen und Admin-Bereich
* `X-Robots-Tag: noindex`, damit die Seite nicht in Suchmaschinen erscheint

## Hosting und Auftragsverarbeitung (Art. 28)

Der Server läuft bei einem externen Hoster (Reverse-DNS `secureserver.net`,
also GoDaddy). Offene Prüfpunkte, die für alle Seiten auf diesem Server gelten,
nicht nur für diese:

* [ ] Standort des Rechenzentrums (EU oder Drittland)
* [ ] Vertrag zur Auftragsverarbeitung (AVV/DPA) mit dem Hoster abgeschlossen
* [ ] Bei Drittland: Rechtsgrundlage für die Übermittlung (z. B.
  Angemessenheitsbeschluss / EU-US Data Privacy Framework, Standardvertragsklauseln)

## Vergleich zum Vorjahr

2025 wurde der DFN-Terminplaner genutzt. Dort lagen Namen und Mengen bei einem
Dritten, die Nutzung war formal auf DFN-Einrichtungen beschränkt, und es gab
keinen Datenschutzhinweis des Vereins. Die eigene Seite ist datenschutzrechtlich
mindestens gleichwertig, eher besser: eigene Kontrolle über die Daten,
festgelegte Löschfrist, transparenter Hinweis.

## Fazit

Die Angabe des Namens ist für die Bestellung erforderlich und durch
Art. 6 Abs. 1 lit. b DSGVO gedeckt. Mit Datensparsamkeit, Datenschutzhinweis,
geschütztem Admin-Bereich und fester Löschfrist ist die Verarbeitung für eine
Vereinsaktion dieser Größe angemessen. Offen ist nur die Frage des
Auftragsverarbeitungsvertrags mit dem Hoster.
