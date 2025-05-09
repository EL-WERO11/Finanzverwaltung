# 💸 Deine geile Finanz-Webseite

Willkommen in deiner persönlichen Geldzentrale! Hier verwaltest du Einnahmen, Ausgaben, Sparziele und siehst endlich schwarz auf weiß, wo die Moneten bleiben. Kein Blabla – einfach rein, eintragen, fertig.

## ⚠️ Sicherheit zuerst!
Diese Seite ist aktuell **nicht für das Internet gemacht**. Du solltest sie **nur lokal oder auf einem privaten Server laufen lassen**, wo **niemand sonst Zugriff hat**.  
Wenn du das Ding öffentlich ins Netz stellst, dann **musst du später noch ordentlich nachbessern**:
- Passwort-Hashing einbauen
- Login absichern
- evtl. HTTPS + Login-Sperren
- und ein bisschen Datenschutz-Zauber ✨

Kommen wir aber erstmal zum Setup …

---

## ⚙️ Setup in 3 Minuten

### ✅ Was du brauchst:
- PHP (ab 7.4 oder höher – alles andere ist Steinzeit)
- MySQL/MariaDB (für die Kohle-Daten)
- Einen Webserver (Apache oder Nginx – hauptsache läuft)

---

### 1. Dateien auf den Server ballern
Einfach alles aus dem Repo auf deinen Webspace kopieren. Keine halben Sachen!

---

### 2. Datenbank einrichten
Die Datei `finanzen.sql` ist dein Freund. Rein damit in deine Datenbank:

```bash
mysql -u BENUTZER -p DEINEDB < finanzen.sql
```

Du kannst das auch per phpMyAdmin importieren – geht beides klar.

---

### 3. Datenbankzugang einstellen
Öffne `db_connect.php` und trag da deine MySQL-Zugangsdaten ein – ohne das läuft nix:

```php
$host = 'localhost';
$user = 'dein_benutzer';
$password = 'dein_passwort';
$database = 'deine_datenbank';
```

---

### 4. Login? Easy.
Der Login ist in `login.php` drin – dort kannst du den Benutzername und Passwort-Check anpassen. Momentan wird nur geprüft, ob die Eingabe zur DB passt.

> 🔐 **Sicherheitstipp**: Wenn du das Ding langfristig benutzt, baue `password_hash()` und `password_verify()` ein. Sonst wird’s schnell unschön – besonders online.

---

## 🗂️ Was ist alles drin?

```bash
📁 index.php        → Deine Hauptseite nach dem Login
📁 login.php        → Der gute alte Login-Screen
📁 logout.php       → Und tschüss!
📁 api.php          → Backend-Magie für alles Finanzzeug
📁 db_connect.php   → Hier stellst du die Verbindung zur DB her
📁 finanzen.sql     → Die ganze Datenbankstruktur zum Importieren
📁 styles.css       → Schickes modernes Design – auch Dark Mode
```

---

## 🤘 Sonst noch was?

- Kein Framework-Salat – pures PHP.
- Keine Registrierung – nur du hast den Schlüssel zur Geldmaschine.
- Kein Composer – einfach hochladen und läuft.

---

## 🙌 Mitmachen?
Klar, mach nen Fork, bau dein Feature ein oder baller direkt ’nen Pull Request raus. Du weißt, wie’s läuft.

---

**Let’s go, spar dir den Stress – behalte den Überblick.**

## ☕ Unterstütze das Projekt

Wenn dir dieses Projekt gefällt und du es unterstützen möchtest:

[![Jetzt spenden](https://img.shields.io/badge/PayPal-Spende-blau.svg?logo=paypal)](https://www.paypal.com/donate/?hosted_button_id=A6JNPQ6PZJMTS)

## 🖼️ Vorschau

Hier siehst du einen kleinen Einblick in die Finanzübersicht:

<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot1.png" width="600" alt="Screenshot 1">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot2.png" width="600" alt="Screenshot 2">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot3.png" width="600" alt="Screenshot 3">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot4.png" width="600" alt="Screenshot 4">
