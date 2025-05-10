<p align="right">
  <a href="https://www.paypal.com/donate/?hosted_button_id=A6JNPQ6PZJMTS">
    <img src="https://img.shields.io/badge/💸%20Jetzt%20spenden-PayPal-blue?logo=paypal" alt="Spenden via PayPal">
  </a>
</p>

# 💸 Deine geile Finanz-Webseite

Willkommen in deiner **persönlichen Geldzentrale**!  
Behalte Einnahmen, Ausgaben und Sparziele im Blick – ohne Excel, ohne Chaos.

---

## 📱 Optimiert für alle Geräte

Diese Webseite sieht nicht nur auf dem PC gut aus – sie ist auch **voll responsive**:  
✅ Funktioniert auf Smartphones & Tablets  
✅ Automatische Anpassung für kleine Bildschirme  
✅ Alles bleibt übersichtlich und nutzbar – auch mobil

---

## ⚠️ Sicherheit zuerst!

> Diese App ist für den **lokalen Einsatz** gedacht.  
> Wenn du sie öffentlich nutzen willst, musst du noch selbst:
- 🔐 Passwort-Hashing einbauen
- 🛡️ Login-Absicherung ergänzen
- 🔒 HTTPS verwenden
- 📜 Datenschutzhinweise einfügen

---

## ⚙️ Setup in 3 Minuten

### ✅ Was du brauchst:
- 🐘 PHP 7.4 oder höher
- 🐬 MySQL/MariaDB
- 🌐 Apache oder Nginx

---

### 🗂️ 1. Dateien auf den Server ballern
Lade alles aus dem Repo hoch. Keine halben Sachen!

---

### 🛠️ 2. Datenbank einrichten

Importiere `finanzen.sql` in deine Datenbank:
```bash
mysql -u BENUTZER -p DEINEDB < finanzen.sql
```

---

### 🔌 3. Datenbank verbinden

Passe `db_connect.php` an:
```php
$host = 'localhost';
$user = 'dein_benutzer';
$password = 'dein_passwort';
$database = 'deine_datenbank';
```

---

### 🔐 4. Login? Läuft.

Der Login ist in `login.php`. Aktuell ohne Passwort-Hashing – du kannst das später nachrüsten mit `password_hash()`.

---

## 📦 Was ist alles drin?

```bash
📁 index.php        → Übersicht nach dem Login
📁 login.php        → Login-Formular
📁 logout.php       → Logout-Skript
📁 api.php          → API für Einnahmen/Ausgaben
📁 db_connect.php   → DB-Verbindung konfigurieren
📁 finanzen.sql     → SQL-Datei zum Importieren
📁 styles.css       → Stylisches Design mit Dark Mode & Mobile Support
```

---

## 🖼️ Vorschau

Hier ein paar Screenshots deiner zukünftigen Finanzzentrale:

<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot1.png" width="600" alt="Screenshot 1">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot2.png" width="600" alt="Screenshot 2">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot3.png" width="600" alt="Screenshot 3">
<br>
<img src="https://raw.githubusercontent.com/kevinkiwi14/Finanzverwaltung/main/img/Screenshot4.png" width="600" alt="Screenshot 4">

---

## 🧩 Geplante Features & Updates

Wir ruhen uns nicht aus – folgende Dinge sollen noch kommen:

- 🔐 **Erweiterte Sicherheit** mit Passwort-Hashing, Login-Sperren & Session-Checks
- 🌈 **Farb-Auswahl-System**: Farben & Darkmode nach Wunsch umstellen
- 📊 **Statistiken & Diagramme** für Einnahmen, Ausgaben & Sparziele
- 🧠 **Künstliche Intelligenz-Vorschläge** (z. B. zum Sparen)
- 🛎️ **Benachrichtigungen**, wenn Ausgaben fällig sind
- 👥 **Mehrbenutzer-Unterstützung** (z. B. für Familie oder Team)
- ☁️ **Export-Funktion** für PDF oder CSV
- 🎨 **Design-Editor** für Farben, Icons & Layout

---

## 🤘 Sonst noch was?

- Kein Composer 🎻  
- Kein Framework-Dschungel 🌴  
- Kein Login-Spam ✉️  
- Und läuft auch auf dem Handy! 📱✅

Einfach pures PHP – wie in den guten alten Tagen.

---

## 🙌 Mitmachen?

Fork das Repo, schick nen Pull Request oder bastel dir deine eigene Version.

---

## 📫 Fragen? Wünsche? Feedback?

Du hast eine Idee, brauchst Hilfe oder willst einfach nur Hallo sagen?

👉 **Komm auf meinen Discord-Server** – dort beantworte ich alle Fragen direkt:

[🎮 Zum Discord-Server](https://discord.gg/gagTvTJK3q)

> Alternativ findest du mich auf Discord unter: `kev_1997`

---

<p align="center"><strong>Let’s go – spar dir den Stress und behalt den Überblick!</strong></p>
