"""Erzeugt die Banner unter site/img/ aus dem Milchstraßen-Originalfoto (nur lokal, wird nicht deployt).

Aufruf:  python3 dev/banner.py ["/Pfad/zum/Originalfoto.jpg"]
Benötigt Pillow und rsvg-convert (brew install librsvg). Für 2028 nur JAHR anpassen.

Motiv: Milchstraßenbogen über Glacier Point (gleicher Ausschnitt wie das bisherige Vereinsbanner),
darunter "Astrokalender 2027", links am Horizont Teleskop, Smartscope und Systemkamera als
Silhouetten (wir suchen Aufnahmen mit ganz unterschiedlicher Ausrüstung).
- banner-300/768/940.jpg: mit Vereinsname über dem Titel
- banner-mobil-940.jpg:   ohne Vereinsname (auf dem Handy wäre er unlesbar klein), per <picture>
"""
import os
import subprocess
import sys
import tempfile

from PIL import Image

JAHR = 2027
ORIGINAL = os.path.expanduser(
    "~/Documents/AG Orion/2019-01-14 Originaldatei The Milkyway, Glacier Point, Yosemite NP.jpg")
ZIEL = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "site", "img")

SCHRIFT = "Helvetica Neue, Helvetica, Arial, sans-serif"
GELB = "#f4c430"
HELLBLAU = "#c9d4f2"


def hintergrund(original, pfad):
    # Volle Breite, vertikal ab 136/373 der Höhe: derselbe Ausschnitt wie das bisherige Banner.
    Image.MAX_IMAGE_PIXELS = None
    foto = Image.open(original).convert("RGB")
    hoehe = round(foto.height * 1880 / foto.width)
    foto.resize((1880, hoehe), Image.LANCZOS).crop((0, 272, 1880, 272 + 396)).save(pfad, quality=95)


def stativ(x, y_kopf, spreizung, boden=197):
    # Dreibein: zwei äußere Beine und ein kürzer wirkendes hinteres Bein
    return (f'<path d="M{x} {y_kopf} L{x - spreizung} {boden} M{x} {y_kopf} L{x + spreizung} {boden} '
            f'M{x} {y_kopf} L{x + spreizung * 0.25} {boden - 3}" fill="none" stroke-linecap="round"/>')


# Silhouetten am Horizont, schwarz mit feiner heller Kante ("Gegenlicht").
GERAETE = f"""
  <g fill="#020305" stroke="#9fb0d8" stroke-opacity="0.55" stroke-width="0.9" stroke-linejoin="round">
    <!-- Linsenteleskop auf parallaktischer Montierung -->
    <g stroke-width="3" stroke="#020305">{stativ(80, 150, 26)}</g>
    <g stroke-width="1">{stativ(80, 150, 26)}</g>
    <rect x="72" y="140" width="16" height="12" rx="2"/>
    <path d="M80 142 L62 160" stroke-width="2.4" stroke="#020305"/>
    <path d="M80 142 L62 160" fill="none"/>
    <circle cx="60" cy="162" r="5"/>
    <g transform="translate(82 132) rotate(-35)">
      <rect x="-34" y="-5.5" width="70" height="11" rx="2"/>
      <rect x="30" y="-7" width="22" height="14" rx="2"/>
      <rect x="-44" y="-3" width="11" height="6" rx="1"/>
      <rect x="-6" y="-13" width="26" height="5" rx="2"/>
    </g>

    <!-- Smartscope (kompaktes Gerät mit integrierter Optik) auf kurzem Stativ -->
    <g stroke-width="2.6" stroke="#020305">{stativ(162, 176, 13)}</g>
    <g stroke-width="0.9">{stativ(162, 176, 13)}</g>
    <rect x="157" y="170" width="10" height="7" rx="1.5"/>
    <g transform="translate(162 162) rotate(-38)">
      <rect x="-9" y="-12" width="30" height="20" rx="5"/>
      <circle cx="21" cy="-2" r="6.5"/>
      <circle cx="21" cy="-2" r="3" fill="none"/>
    </g>

    <!-- Systemkamera mit Objektiv auf Stativ -->
    <g stroke-width="2.6" stroke="#020305">{stativ(232, 172, 15)}</g>
    <g stroke-width="0.9">{stativ(232, 172, 15)}</g>
    <rect x="228" y="166" width="8" height="7" rx="1"/>
    <g transform="translate(232 160) rotate(-30)">
      <path d="M-13 -7 h7 l2 -5 h9 l2 5 h7 a2 2 0 0 1 2 2 v13 a2 2 0 0 1 -2 2 h-27 a2 2 0 0 1 -2 -2 v-13 a2 2 0 0 1 2 -2 z"/>
      <rect x="15" y="-5" width="13" height="14" rx="2"/>
      <rect x="27" y="-6" width="3" height="16" rx="1"/>
    </g>
  </g>
"""


def svg(hintergrund_datei, mit_vereinsname):
    vereinsname = (f'<text x="470" y="70" text-anchor="middle" font-family="{SCHRIFT}" font-size="12" '
                   f'font-weight="500" letter-spacing="2.5" fill="{HELLBLAU}" filter="url(#schatten)">'
                   f'ASTRONOMISCHE GESELLSCHAFT ORION BAD HOMBURG E.V.</text>') if mit_vereinsname else ""
    titel_y = 122 if mit_vereinsname else 116
    return f"""<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
     viewBox="0 0 940 198" width="940" height="198">
  <defs>
    <filter id="schatten" x="-10%" y="-30%" width="120%" height="160%">
      <feDropShadow dx="0" dy="1.5" stdDeviation="2.5" flood-color="#000" flood-opacity="0.85"/>
    </filter>
    <radialGradient id="mitte" cx="0.5" cy="0.5" r="0.5">
      <stop offset="0%" stop-color="#000" stop-opacity="0.6"/>
      <stop offset="100%" stop-color="#000" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <image x="0" y="0" width="940" height="198" xlink:href="{hintergrund_datei}" preserveAspectRatio="none"/>
  <ellipse cx="470" cy="104" rx="330" ry="68" fill="url(#mitte)"/>
  {vereinsname}
  <text x="470" y="{titel_y}" text-anchor="middle" font-family="{SCHRIFT}" font-size="50" font-weight="700"
        fill="#fff" filter="url(#schatten)">Astrokalender <tspan fill="{GELB}">{JAHR}</tspan></text>
  {GERAETE}
</svg>
"""


def main():
    original = sys.argv[1] if len(sys.argv) > 1 else ORIGINAL
    with tempfile.TemporaryDirectory() as tmp:
        hintergrund(original, os.path.join(tmp, "hintergrund.jpg"))
        varianten = {"banner": True, "banner-mobil": False}
        for name, mit_vereinsname in varianten.items():
            svg_datei = os.path.join(tmp, name + ".svg")
            png_datei = os.path.join(tmp, name + ".png")
            with open(svg_datei, "w", encoding="utf-8") as f:
                f.write(svg("hintergrund.jpg", mit_vereinsname))
            subprocess.run(["rsvg-convert", "-w", "1880", "-h", "396", svg_datei, "-o", png_datei], check=True)
            gross = Image.open(png_datei).convert("RGB")
            breiten = (300, 768, 940) if mit_vereinsname else (940,)
            for breite in breiten:
                ziel = os.path.join(ZIEL, f"{name}-{breite}.jpg")
                gross.resize((breite, round(198 * breite / 940)), Image.LANCZOS).save(
                    ziel, quality=82, optimize=True, progressive=True)
                print(f"{os.path.relpath(ziel)}  {os.path.getsize(ziel) // 1024} KB")


if __name__ == "__main__":
    main()
