# FitFuerInfo

FitFuerInfo ist eine PHP-Webanwendung zur Verwaltung von Kursen, Schulungsräumen, Softwareausstattung und Raumbelegungen. Mitarbeitende melden sich mit einem persönlichen Konto an. Administratoren verwalten zusätzlich Mitarbeitende und die Softwarekataloge.

## Inhaltsverzeichnis

- [Funktionen](#funktionen)
- [Technische Voraussetzungen](#technische-voraussetzungen)
- [Installation unter XAMPP](#installation-unter-xampp)
- [Ersten Administrator anlegen](#ersten-administrator-anlegen)
- [Anmeldung und Mitarbeiterkonten](#anmeldung-und-mitarbeiterkonten)
- [Berechtigungen](#berechtigungen)
- [Datenbank](#datenbank)
- [Projektstruktur](#projektstruktur)
- [Konfiguration](#konfiguration)
- [Sicherung und Wiederherstellung](#sicherung-und-wiederherstellung)
- [Bekannte Einschränkungen](#bekannte-einschränkungen)

## Funktionen

- Anmeldung und Abmeldung von Mitarbeitenden
- Aktivierung neu erstellter Mitarbeiterkonten über einen einmaligen Aktivierungslink
- Kurse mit Beschreibung, maximaler Teilnehmerzahl und benötigten Softwarepaketen
- Räume mit Beschreibung, Anzahl der Arbeitsplätze und installierter Software
- Zuordnung von Mitarbeitenden zu Kursen und Räumen
- Raumbelegungen für einen Kurs über einen Datumszeitraum
- Prüfung, ob ein Raum im gewählten Zeitraum bereits belegt ist
- Prüfung, ob ein Raum genügend Arbeitsplätze und die benötigte Software besitzt
- Admin-Panel mit Kennzahlen und Schnellzugriffen
- Mitarbeiterverwaltung: Konten aktivieren/deaktivieren, Administratorrechte verwalten und Konten löschen
- Verwaltung des Softwarekatalogs und der Zuordnungen zu Kursen und Räumen

## Technische Voraussetzungen

- Apache-Webserver
- PHP 7 oder neuer mit PDO und PDO-MySQL
- MySQL oder MariaDB mit InnoDB und `utf8mb4`
- Ein moderner Browser mit JavaScript für das Kopieren des Aktivierungslinks

Es werden keine Composer- oder npm-Pakete benötigt. Das verwendete W3.CSS liegt lokal in `w3.css`.

## Installation unter XAMPP

1. Lege den Ordner `fitfuerinfo` in das XAMPP-Verzeichnis `htdocs`, zum Beispiel `C:\xampp\htdocs\fitfuerinfo`.
2. Starte Apache und MySQL im XAMPP Control Panel.
3. Öffne phpMyAdmin unter `http://localhost/phpmyadmin` oder importiere die SQL-Datei mit dem MySQL-Client.
4. Führe `config/schema.sql` aus. Das Skript erstellt die Datenbank `fitfuerinfo` und alle benötigten Tabellen. Es legt **kein** Benutzerkonto an.
5. Prüfe die Zugangsdaten in `config/database.php`. Die Voreinstellung ist für eine lokale XAMPP-Entwicklung gedacht. Passe Host, Datenbankname, Benutzername und Passwort an deine Installation an.
6. Lege den ersten Administrator an (siehe [Ersten Administrator anlegen](#ersten-administrator-anlegen)).
7. Öffne `http://localhost/fitfuerinfo/` im Browser und melde dich an.

### Schema mit dem MySQL-Client importieren

In einer Windows-Eingabeaufforderung im XAMPP-Ordner:

```bat
mysql\bin\mysql.exe --user=root --password= < htdocs\fitfuerinfo\config\schema.sql
```

Bei der XAMPP-Standardinstallation ist das Root-Passwort häufig leer. In diesem Fall kann `--password=` verwendet werden. Für andere Installationen trage das richtige Passwort ein.

## Ersten Administrator anlegen

Das Schema erzeugt aus Sicherheitsgründen keinen Standard-Administrator und kein Standardpasswort. Erzeuge einen Passwort-Hash mit PHP. Ersetze `DEIN_STARKES_PASSWORT` durch ein eigenes Passwort:

```bat
php\php.exe -r "echo password_hash('DEIN_STARKES_PASSWORT', PASSWORD_DEFAULT), PHP_EOL;"
```

Kopiere den ausgegebenen Hash und führe in phpMyAdmin die folgende Abfrage aus. Ersetze die Beispielwerte einschließlich des Hash-Platzhalters:

```sql
USE fitfuerinfo;

INSERT INTO mitarbeiter
    (benutzername, passwort, vorname, nachname, ist_admin, aktiv)
VALUES
    ('admin', 'HIER_DEN_PASSWORTHASH_EINSETZEN', 'Vorname', 'Nachname', 1, 1);
```

Melde dich danach mit dem gewählten Benutzernamen und Passwort an. Das Passwort wird nur als Hash in der Datenbank gespeichert.

## Anmeldung und Mitarbeiterkonten

Administratoren erstellen Mitarbeiterkonten im Admin-Bereich. Nach dem Erstellen zeigt die Anwendung einmalig einen Aktivierungslink an. Kopiere diesen Link und sende ihn der betreffenden Person über einen sicheren Kanal. Der Mitarbeiter legt darüber sein Passwort fest; danach wird das Konto aktiviert und der Aktivierungscode gelöscht.

Der Aktivierungslink wird nicht automatisch per E-Mail verschickt. Er wird nach dem Verlassen der Erstellungsseite nicht erneut angezeigt. Wird er nicht rechtzeitig kopiert, muss ein neuer Mitarbeiterdatensatz beziehungsweise Aktivierungscode eingerichtet werden.

Für Aktivierungspasswörter verlangt die Anwendung aktuell mindestens vier Zeichen, mindestens einen Kleinbuchstaben und mindestens eine Zahl. Für einen echten Einsatz sollte diese Mindestanforderung erhöht werden.

## Berechtigungen

| Funktion | Mitarbeitende | Administratoren |
|---|---:|---:|
| Anmelden und Dashboard verwenden | Ja | Ja |
| Kurse ansehen und neue Kurse anlegen | Ja | Ja |
| Kurs bearbeiten oder löschen | Eigene oder zugewiesene Kurse | Alle Kurse |
| Räume ansehen | Ja | Ja |
| Raum anlegen | Nein | Ja |
| Raum bearbeiten/löschen | Zugewiesene Räume | Alle Räume |
| Raumbelegung anlegen | Ja | Ja |
| Raumbelegung löschen | Eigene Buchungen | Alle Buchungen |
| Mitarbeiterkonten verwalten | Nein | Ja |
| Softwarekatalog verwalten | Nein | Ja |

Die Anwendung prüft die Anmeldung und Rolle serverseitig. Links im Dashboard sind keine Ersatzprüfung für diese Zugriffskontrollen.

## Datenbank

Das vollständige Einrichtungsschema befindet sich in `config/schema.sql`. Es erstellt die Datenbank `fitfuerinfo` und diese neun Tabellen:

| Tabelle | Zweck |
|---|---|
| `mitarbeiter` | Konten, Passwort-Hashes, Rollen und Aktivierungsstatus |
| `kurse` | Kursdaten und verantwortlicher Mitarbeiter |
| `raeume` | Raumdaten und Arbeitsplatzanzahl |
| `buchungen` | Kurs, Raum, Zeitraum und erstellender Mitarbeiter |
| `software` | Softwarepaket-Katalog |
| `kurs_software` | Benötigte Software pro Kurs |
| `raum_software` | Installierte Software pro Raum |
| `kurs_mitarbeiter` | Zusätzliche Mitarbeiterzuordnungen zu Kursen |
| `raum_mitarbeiter` | Mitarbeiterberechtigungen für Räume |

`config/database.php` stellt die PDO-Verbindung her. SQL-Abfragen der Anwendung verwenden PDO; Werte werden größtenteils über vorbereitete Anweisungen gebunden.

## Projektstruktur

```text
fitfuerinfo/
├── admin/                 Admin-Panel und Mitarbeiterverwaltung
├── buchungen/             Raumbelegungen anzeigen, anlegen und löschen
├── config/
│   ├── database.php       Datenbankverbindung
│   └── schema.sql         Vollständiges Datenbankschema für Neuinstallationen
├── includes/
│   ├── header.php         Gemeinsamer Seitenkopf und CSS-Einbindung
│   └── footer.php         Gemeinsame Fußzeile
├── kurse/                 Kurse anzeigen, anlegen, bearbeiten und löschen
├── Räume/                 Räume anzeigen, anlegen, bearbeiten und löschen
├── software/              Softwarekatalog und Zuordnungen (Admin)
├── app.css                Ergänzende Anwendungsstile
├── w3.css                 Lokal verwendetes W3.CSS
├── dashboard.php          Angemeldeter Bereich
├── index.php              Öffentliche Startseite
├── login.php              Anmeldung
├── logout.php             Abmeldung
└── passwort_setzen.php    Kontoaktivierung und Passwortfestlegung
```

## Konfiguration

Bearbeite `config/database.php`, wenn deine Datenbank nicht mit den lokalen Standardwerten erreichbar ist:

```php
$host = "localhost";
$dbname = "fitfuerinfo";
$username = "root";
$password = "";
```

Für einen Server außerhalb der lokalen Entwicklung sollte ein eigener Datenbankbenutzer mit den nötigen Rechten verwendet werden. Zugangsdaten gehören nicht in ein öffentliches Repository. Vor dem Veröffentlichen muss `config/database.php` daher geprüft und gegebenenfalls in eine lokale, nicht versionierte Konfiguration ausgelagert werden.

## Sicherung und Wiederherstellung

Sichere vor Änderungen an einer bestehenden Datenbank zuerst deren Daten. In phpMyAdmin kannst du die Datenbank `fitfuerinfo` exportieren. Die Datei `config/schema.sql` enthält die Tabellenstruktur, aber keine Mitarbeiter-, Kurs-, Raum- oder Buchungsdaten.

Ein erneuter Import der Schema-Datei löscht bestehende Tabellen nicht, weil `CREATE TABLE IF NOT EXISTS` verwendet wird. Er aktualisiert vorhandene Tabellen jedoch auch nicht. Änderungen an einer bestehenden Datenbank müssen als eigene Migration geplant und gesichert werden.

## Bekannte Einschränkungen

- Buchungen speichern ganze Tage, keine Uhrzeiten. Es sind daher keine mehreren getrennten Zeitfenster am selben Tag modelliert.
- Aktivierungslinks werden nur auf der Erstellungsseite angezeigt und nicht per E-Mail verschickt.
- Die Anwendung hat derzeit keine automatisierte Passwort-Zurücksetzen-Funktion.
- Für schreibende Formulare ist noch kein CSRF-Schutz dokumentiert beziehungsweise implementiert.
- `config/schema.sql` ist für eine neue Datenbank gedacht. Es wurde nicht als Update-Skript für eine bestehende Datenbank ausgelegt.
- Die voreingestellten Datenbank-Zugangsdaten in `config/database.php` sind nur für lokale Entwicklung geeignet.
