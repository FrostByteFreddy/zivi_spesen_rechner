# Zivi Spesen Rechner 🇨🇭

Ein modernes Spesenmanagement-System für Zivildienstleistende, basierend auf Drupal 10.

## 🚀 Features

- **Dashboard**: Übersicht über alle eingereichten und Entwurfs-Spesenabrechnungen.
- **Intelligentes Formular**: Automatisierte Berechnung von Standard-Spesen (Taschengeld, Verpflegung) und einfache Erfassung von individuellen Ausgaben.
- **PDF-Generierung**: Professionell gestaltete PDF-Abrechnungen inklusive:
  - Automatisch berechneter Gesamtsumme.
  - Buchungsstempel für die interne Verarbeitung.
  - Automatischer Anhang von Belegen (nur für relevante Positionen).
  - Kompaktes, einseitiges Layout.
- **Profil-Management**: Zentrale Verwaltung von Name, Adresse und IBAN für die Abrechnungen.
- **Responsive Design**: Optimiert für Desktop und mobile Nutzung dank Tailwind CSS.

## 🛠 Tech Stack

- **Core**: Drupal 10
- **Frontend**: Tailwind CSS (via CDN & Custom Templates)
- **PDF Engine**: Entity Print (Dompdf)
- **Environment**: DDEV

## 📦 Installation & Setup

1. **Repository klonen**

   ```bash
   git clone <repository-url>
   cd zivi_spesen_rechner
   ```

2. **DDEV starten**

   ```bash
   ddev start
   ```

3. **Abhängigkeiten installieren**

   ```bash
   ddev composer install
   ```

4. **Cache leeren**
   ```bash
   ddev drush cr
   ```

## 📂 Projektstruktur

- `web/modules/custom/zivi_spesen`: Das Hauptmodul mit der Logik für Formulare, Controller und PDF-Templates.
- `web/modules/custom/zivi_spesen/templates`: Twig-Templates für das Dashboard, das Spesenformular und das PDF.
- `web/modules/custom/zivi_spesen/src/Form`: Drupal Form-Klassen für die Spesenerfassung und das Profil.

## 📄 Lizenz

Dieses Projekt ist für den internen Gebrauch bestimmt.
