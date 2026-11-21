# Bestellinfo für Mitglieder

Kurz vor Bestellschluss (November 2026) haben sich Anfragen gehäuft, in denen Mitglieder wissen
wollten, wie viele Kalender sie bestellt haben. Mehrere haben in Teilbestellungen nachbestellt
und den Überblick verloren. Die Seite zeigt das bisher bewusst nicht an. Hier ist festgehalten,
welche DSGVO-konformen Möglichkeiten es gibt, wofür wir uns entschieden haben und warum.

## Ausgangslage

* Gespeichert werden nur Name, Anzahl, optionale Bemerkung, Zeitpunkt und Zahlungsstatus,
  ohne E-Mail, Telefonnummer oder Login (siehe [DATENSCHUTZ.md](DATENSCHUTZ.md)).
* Keine Bestellnummer, kein Self-Service. Änderungen und Stornos laufen per PN über Benno.
* Öffentlich sichtbar ist nur die Gesamtsumme („Bisher wurden X Kalender bestellt“).

## Rechtlicher Rahmen

Nach **Art. 15 DSGVO** hat jedes Mitglied das Recht zu erfahren, welche Daten über es gespeichert
sind. Ob jemand seine eigene Bestellung sehen darf, ist also keine Frage. Die Frage ist, wie
sichergestellt wird, dass **nur diese Person** sie sieht. Ohne Login und ohne Kontaktdaten gibt
es auf der Seite keine Möglichkeit, das zu prüfen. Daraus ergibt sich, welche Lösungen in
Frage kommen.

## Möglichkeiten

### Ausgeschlossen: Abfrage über den Namen

„Name eingeben, Anzahl wird angezeigt.“ Damit könnte jeder die Bestellung jedes anderen
Mitglieds abfragen, weil die Namen im Verein bekannt sind. Das verstößt gegen die
Vertraulichkeit (Art. 5 Abs. 1 lit. f, Art. 32 DSGVO), daran ändert auch eine Rate-Begrenzung
nichts.

### Variante 1: Vorbereitete Antwort aus dem Admin-Bereich (umgesetzt)

In der Admin-Übersicht hat jede Zeile einen Knopf **„Nachricht“**. Er kopiert einen fertigen
Text in die Zwischenablage, den Benno in eine Signal-DM oder PN einfügt. Der Text ist
kurz gehalten, hat keine Leerzeilen und je Thema eine Zeile:

* alle Bestellungen mit demselben Namen zusammengefasst (Groß-/Kleinschreibung und Umlaute
  egal), Gesamtanzahl und Gesamtbetrag, bei mehreren mit Anzahl und Datum je Teilbestellung
* eingegangene Beträge mit dem Datum des Eingangs
* bei offenem Betrag Empfänger, IBAN und Verwendungszweck. BIC und Bank fehlen, weil die IBAN
  reicht. Den Empfänger lassen wir ausgeschrieben, weil die Bank ihn beim Überweisen mit der
  IBAN abgleicht.
* Termin der Übergabe

Beispiel (teilweise bezahlt):

```text
Hallo Max, du hast 3 Kalender (30,00 €) für 2027 bestellt: 1 am 18.11.2026 und 2 am 21.11.2026.
Eingegangen: 10,00 € am 19.11.2026.
Bitte überweise die restlichen 20,00 € an Astronomische Gesellschaft Orion Bad Homburg e.V., IBAN DE30 5125 0000 0001 0574 80, Verwendungszweck: Astrokalender 2027 - Max Mustermann
Übergabe bei der Weihnachtsfeier am 18.12.2026. Viele Grüße, Benno
```

Bewertung:

* **Datenschutz:** unverändert. Benno weiß, wem er antwortet, es gibt keine neue öffentliche
  Funktion und keine neuen Daten.
* **Aufwand:** klein (`site/admin/index.php`, `admin.js`, `admin.css`).
* **Nachteil:** Die Anfragen laufen weiter über Benno, kosten ihn aber nur noch Sekunden.
* **Achtung:** Haben zwei verschiedene Mitglieder exakt denselben Namen, werden ihre
  Bestellungen zusammengefasst. Vor dem Abschicken kurz prüfen (der Tooltip des Knopfs zeigt,
  wie viele Bestellungen zusammengefasst wurden).

### Variante 2: Persönlicher Abruf-Link (Vorschlag für 2028)

Jede Bestellung bekommt einen zufälligen, nicht erratbaren Schlüssel (`random_bytes(16)`,
128 Bit, hex- oder base64url-kodiert). Die Bestätigungsseite zeigt dann „Speichere diesen Link,
um deine Bestellung später anzusehen“, z. B. `https://kalender.agorion.de/bestellung?k=…`.
Wer den Link hat, gilt als berechtigt.

Umsetzung:

* neue Spalte `zugang TEXT UNIQUE`, in `db.php` per `ALTER TABLE` nachziehen (wie `uebergeben`)
* neue öffentliche Seite, die genau eine Bestellung anzeigt (Anzahl, Betrag, Zahlungsstatus,
  Kontoverbindung). Unbekannter Schlüssel → dieselbe neutrale Meldung, kein Hinweis, ob es ihn gibt.
* Schlüssel mit `hash_equals` bzw. per Prepared Statement auf die eindeutige Spalte vergleichen
* im Admin-Bereich: Link pro Bestellung anzeigen, für Altbestellungen erzeugen
* `Referrer-Policy: no-referrer` und `noindex` gelten schon für die ganze Seite
* der Schlüssel steht in der URL und damit im nginx-Access-Log. Für diese Location das Log
  abschalten oder den Query-String nicht loggen.
* Datenschutzhinweis: ein Satz, dass es einen persönlichen Link gibt und wer ihn hat, die
  Bestellung sehen kann. Es werden keine neuen personenbezogenen Daten erhoben.

Bewertung:

* **Datenschutz:** gut. Es kommt nur ein Zufallswert hinzu, der Zugriff ist an den Besitz des
  Links gebunden.
* **Aufwand:** mittel.
* **Nachteile:** Wer den Link verliert, fragt doch wieder nach. Wird ein Link in eine Gruppe
  weitergeleitet, sehen ihn alle. Signal ruft für Link-Vorschauen die Seite ab, das ist
  unkritisch, weil die Seite nichts ändert (nur GET, keine Aktionen über den Link).
* Für die laufende Aktion 2027 lohnt sich der Umbau nicht mehr: Er wäre nur bis zum
  Bestellschluss nützlich, und die bestehenden Bestellungen hätten noch keinen Link.

### Variante 3: Selbst gewählte PIN beim Bestellen (verworfen)

Das Formular bekommt ein Feld „PIN (4–6 Ziffern)“, abgefragt wird mit Name plus PIN.

* Kurze PINs lassen sich ohne harte Rate-Begrenzung über IP oder Konto erraten. Die
  Session-Begrenzung reicht dafür nicht.
* Mitglieder vergessen die PIN genauso wie einen Link.
* Bestehende Bestellungen haben keine PIN.
* Gegenüber Variante 2 kein Vorteil, aber mehr Angriffsfläche.

### Weitere Überlegungen

* **Anzeige nur in der eigenen Browser-Session** („Deine Bestellungen in dieser Sitzung“):
  datenschutzrechtlich unproblematisch, hilft aber kaum, weil der Session-Cookie nach dem
  Schließen des Browsers weg ist und viele vom Handy über Signal bestellen.
* **Dauerhafter Cookie mit dem Abruf-Schlüssel:** wäre nach § 25 Abs. 2 TDDDG als „unbedingt
  erforderlich“ vertretbar, wenn das Mitglied die Funktion ausdrücklich nutzt. Das macht aber den
  Datenschutzhinweis länger, und auf fremden Geräten ist der Cookie gar nicht vorhanden. Für den
  Bedarf ist das zu viel.

## Entscheidung

* **2027:** Variante 1. Anfragen beantwortet Benno mit dem Knopf „Nachricht“ im Admin-Bereich.
* **2028:** Variante 2 von Anfang an einplanen, dann gibt es diese Anfragen gar nicht erst.
