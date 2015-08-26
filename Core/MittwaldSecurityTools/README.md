# shopware-security-tools
Security Tools for Shopware 5

Machen Sie Shopware noch sicherer! Wir haben in dieses Plugin einige Mechanismen und Tools integriert, die Ihren Shop noch besser vor Viren und Hackern schützen.

Die folgenden Funktionen sind im Plugin enthalten und können unabhängig voneinander aktiviert und deaktiviert werden:

* Zwei-Faktor Authentifizierung über YubiKey (Hardware-Token)
* Automatische E-Mail-Benachrichtigung zu sicherheitsrelevanten Vorfällen (fehlgeschlagene Login-Versuche und modifizierte Core-Dateien)
* Allgemeine Sicherheitshinweise
* Google reCaptcha für Kundenregistrierung
* Anzeige zur Passwortsicherheit bei der Kundenregistrierung


Die Frontend-Features sind für das Shopware Responsive Theme optimiert und nutzen den systeminternen LESS- und JS- Precompiler.

## Systemvoraussetzungen

* Shopware Version > 5.0.0

## Installation

* Dateien nach engine/Shopware/Plugins/Local/Core/MittwaldSecurityTools/ hochladen
* Plugin über Plugin Manager aktivieren und gewünschte Features aktivieren
* Cache leeren (+ Theme neu kompilieren)