# Planungsdokumentation – FitFuerInfo

**Ausbildungsberuf:** Fachinformatiker/in für Systemintegration  
**Lernfeld:** LF 12 – Kundenspezifische Systemintegration durchführen  
**Projekt:** Raumbuchung über Webseite – Verwaltung von Kursen und Räumen  
**Teammitglieder:** [Namen aller Teammitglieder eintragen]  
**Auftraggeber:** FitFuerInfo

## 1. Ausgangssituation

FitFuerInfo bietet Schulungskurse zu verschiedenen IT-Themen an. Ein Teil der Schulungen findet in den Kursräumen in Aachen statt. Für die Kurse werden je nach Thema eine bestimmte Anzahl an Rechnerarbeitsplätzen und passende Software benötigt. Bei Übungen zu Sicherheitsthemen kann zusätzlich eine vom Internet getrennte Umgebung erforderlich sein.

In unserem Projekt planen und entwickeln wir eine lokal auf einem Rechner laufende Webanwendung. Mitarbeitende sollen Kursprofile verwalten und passende Räume buchen können. Mitarbeiterkonten, Kursprofile, Räume, Software und Buchungen werden in einer SQL-Datenbank gespeichert. Die Benutzeroberfläche setzen wir mit PHP und HTML um.

## 2. Projektziel

Unser Ziel ist eine übersichtliche Webseite, über die Mitarbeitende ohne direkte Datenbankkenntnisse Kurse und Raumbelegungen verwalten können. Vor dem Speichern einer Buchung soll das System prüfen, ob der Raum zum Kurs passt. Dazu gehören die Arbeitsplatzanzahl, die benötigte Software und der ausgewählte Zeitraum.

Die Verwaltung von Mitarbeiterzugängen und Räumen soll nur einem berechtigten Systemverwalter möglich sein. Mitarbeitende sollen ihre Passwörter selbst festlegen. Der Systemverwalter darf das Klartextpasswort nicht sehen können.

## 3. Anforderungen aus dem Auftrag

| ID | Anforderung | Bedeutung für unser Projekt |
|---|---|---|
| A1 | Mitarbeiterkonten müssen kontrolliert angelegt werden. | Nur der Systemverwalter darf neue Konten freischalten und Zugänge entziehen. |
| A2 | Mitarbeitende legen ihr Passwort selbst fest. | Ein Aktivierungsverfahren darf das Passwort nicht an den Verwalter weitergeben. In der Datenbank wird nur ein Passwort-Hash gespeichert. |
| A3 | Das Passwort erfüllt die vorgegebene Mindestregel. | Mindestens vier Zeichen, darunter ein Kleinbuchstabe und eine Zahl. |
| A4 | Jeder Mitarbeiter kann Kursprofile anlegen. | Ein Kursprofil enthält mindestens die maximale Teilnehmerzahl und die benötigte Software. |
| A5 | Kursprofile anderer Mitarbeitender dürfen gelesen werden. | Die Kursübersicht ist für angemeldete Mitarbeitende sichtbar. |
| A6 | Kursprofile dürfen nur von ihren Eigentümern bearbeitet oder gelöscht werden. | Der Ersteller ist Eigentümer. Der Systemverwalter kann bei Bedarf weitere Eigentümer zuordnen. |
| A7 | Alle Mitarbeitenden können die Raumbelegung einsehen und Räume buchen. | Buchungen sind einem konkreten Kurs und Raum zugeordnet. |
| A8 | Eine Buchung darf nur ihr Ersteller oder der Systemverwalter löschen. | Die Berechtigung wird vor dem Löschen geprüft. |
| A9 | Neue Räume legt der Systemverwalter an. | Der Systemverwalter kann einzelne Mitarbeitende zur Bearbeitung eines Raums berechtigen. |
| A10 | Zu jedem Raum gehören Arbeitsplatzanzahl und installierte Software. | Diese Angaben werden bei der Buchung mit den Kursanforderungen verglichen. |
| A11 | Das System muss prüfen, ob ein Raum zu einem Kursprofil passt. | Kapazität, Software und zeitliche Verfügbarkeit werden vor dem Speichern kontrolliert. |
| A12 | Das System soll lokal auf einem Rechner mit PHP und SQL-Datenbank laufen. | Als Zielumgebung ist der Schulrechner mit XAMPP 5.6.36 und phpMyAdmin genannt. |

## 4. Geplante Bedienung und Aussehen der Webseite

Wir planen eine einfache Oberfläche mit einer Startseite, einer Anmeldung und einem Dashboard. Nach der Anmeldung sollen Mitarbeitende die wichtigsten Funktionen direkt erreichen können. Administratorfunktionen werden nur Systemverwaltern angezeigt und zusätzlich serverseitig geschützt.

### 4.1 Anmeldung und Mitarbeiterkonto

Auf der Startseite führt eine Schaltfläche zur Anmeldung. Nach erfolgreicher Anmeldung erscheint das Dashboard. Neue Mitarbeitende erhalten keinen vom Verwalter vergebenen Klartextzugang. Stattdessen legt der Verwalter ein Konto an und übermittelt einen Aktivierungslink. Über diesen Link setzt der Mitarbeiter sein eigenes Passwort.

### 4.2 Kursprofile

In der Kursübersicht sehen Mitarbeitende alle Kursprofile mit Namen, Beschreibung, maximaler Teilnehmerzahl und benötigten Softwarepaketen. Ein Mitarbeiter kann ein neues Kursprofil anlegen. Bei eigenen oder zusätzlich zugewiesenen Kursen erscheinen Funktionen zum Bearbeiten und Löschen. Andere Kursprofile können nur angesehen werden.

### 4.3 Räume und Raumbelegung

In der Raumübersicht sehen Mitarbeitende die Räume mit ihrer Arbeitsplatzanzahl und installierten Software. Je nach Berechtigung können sie einen Raum bearbeiten. Für eine Buchung wählen sie einen Kurs, einen Raum sowie den Beginn und das Ende des Zeitraums aus. Das System zeigt verständliche Fehlermeldungen, falls der Raum nicht geeignet oder bereits belegt ist.

In der Belegungsübersicht sehen Mitarbeitende die aktuellen Buchungen. Nur der Ersteller einer Buchung und der Systemverwalter erhalten eine Löschmöglichkeit.

### 4.4 Verwaltungsbereich

Der Systemverwalter erhält einen eigenen Verwaltungsbereich. Dort kann er Mitarbeiterkonten erstellen, aktivieren oder deaktivieren, Administratorrechte verwalten, Räume anlegen und Softwarepakete den Kursen und Räumen zuordnen.

## 5. Geplante Arbeitsschritte

Wir planen die Arbeit in folgender Reihenfolge:

| Schritt | Arbeit | Ergebnis |
|---|---|---|
| 1. Auftrag klären | Anforderungen durchgehen und offene Fragen an FitFuerInfo sammeln. | Abgestimmte Anforderungen und geklärte Rahmenbedingungen. |
| 2. Umgebung erkunden | XAMPP, PHP-Version, MySQL/MariaDB und phpMyAdmin auf dem Schulrechner prüfen. | Entscheidung, welche Funktionen mit der vorhandenen Zielumgebung kompatibel sind. |
| 3. Bedienung planen | Seiten, Navigation, Formulare und typische Abläufe skizzieren. | Einfacher Entwurf der Webseite aus Sicht eines Mitarbeiters und eines Verwalters. |
| 4. Datenmodell entwerfen | Tabellen, Schlüssel und Beziehungen für Mitarbeiter, Kurse, Räume, Software und Buchungen festlegen. | SQL-Schema mit nachvollziehbaren Beziehungen. |
| 5. Prototyp erstellen | Verbindung zwischen PHP und SQL-Datenbank sowie gemeinsame Seitenstruktur aufbauen. | Lokal erreichbare Grundanwendung. |
| 6. Anmeldung umsetzen | Sitzungen, Rollenprüfung, Aktivierung und sichere Passwortspeicherung einrichten. | Geschützte Mitarbeiter- und Administratorbereiche. |
| 7. Kursverwaltung umsetzen | Kursprofile erstellen, anzeigen, bearbeiten und löschen; Eigentümerrechte ergänzen. | Kursverwaltung entsprechend den Rollen. |
| 8. Raumverwaltung umsetzen | Räume und Arbeitsplatzanzahl verwalten; Mitarbeiterberechtigungen ergänzen. | Raumübersicht und eingeschränkte Bearbeitungsrechte. |
| 9. Softwareverwaltung umsetzen | Softwarekatalog anlegen und Software Räumen und Kursen zuordnen. | Grundlage für die Kompatibilitätsprüfung. |
| 10. Buchung umsetzen | Buchungsformular, Belegungsübersicht und Prüfungen implementieren. | Buchungen nur für passende und freie Räume. |
| 11. Integrieren und testen | Funktionen gemeinsam mit positiven und negativen Testfällen prüfen. | Fehlerprotokoll und korrigierte Anwendung. |
| 12. Zielumgebung abnehmen | Anwendung auf dem Schulrechner mit XAMPP 5.6.36 starten und Bedienung prüfen. | Nachweis, ob die geforderte Zielumgebung unterstützt wird. |
| 13. Dokumentieren | Installation, Bedienung, Datenbank und bekannte Grenzen dokumentieren. | Übergabefähige Projektdokumentation. |

## 6. Kenntnisse und geplante Erkundungen

Im Projekt wollen wir insbesondere folgende Kenntnisse vertiefen und praktisch ausprobieren:

- PHP-Sitzungen und serverseitige Zugriffskontrollen
- Passwort-Hashing mit `password_hash()` und Prüfung mit `password_verify()`
- PDO und vorbereitete SQL-Abfragen
- Relationale Datenbankmodellierung mit Primär- und Fremdschlüsseln
- n:m-Beziehungen, beispielsweise zwischen Kursen und Softwarepaketen
- SQL-Abfragen zur Prüfung überschneidender Buchungen
- Formularvalidierung und verständliche Rückmeldungen bei Fehlern
- Einrichtung und Fehlerbehebung von Apache, PHP und MySQL in XAMPP
- Sicherung und Wiederherstellung der Datenbank über phpMyAdmin
- Prüfung der Kompatibilität mit der auf dem Schulrechner installierten PHP-Version

Besonders die letzte Erkundung ist wichtig: Der Auftrag nennt XAMPP 5.6.36. Die aktuelle Fassung unseres Projekts verwendet an einzelnen Stellen PHP-Syntax, die erst ab PHP 7 verfügbar ist. Deshalb muss die Anwendung vor einer Abnahme auf PHP 5.6 angepasst oder die Zielumgebung mit dem Auftraggeber abgestimmt werden. Mit der aktuellen Fassung können wir die Kompatibilität zu XAMPP 5.6.36 noch nicht zusichern.

## 7. Fragen an den Auftraggeber

Diese Fragen können die Planung und die spätere Umsetzung beeinflussen:

1. Soll eine Raumbelegung für ganze Tage oder für genaue Uhrzeiten geplant werden?
2. Sind mehrtägige Buchungen möglich? Zählt eine Buchung am Enddatum den Raum ebenfalls als belegt?
3. Reicht die Prüfung von Teilnehmerzahl und installierter Software aus, oder müssen weitere Raumeigenschaften berücksichtigt werden?
4. Wie wird sichergestellt, dass ein Raum für Sicherheitsschulungen tatsächlich vom Internet getrennt ist? Soll die Webseite diese Information nur anzeigen oder muss sie die Netzwerkumgebung berücksichtigen?
5. Gibt es einen bevorzugten Weg, den Aktivierungslink an neue Mitarbeitende zu übermitteln, zum Beispiel E-Mail oder persönliche Übergabe?
6. Wer richtet das erste Administratorkonto ein, bevor ein Systemverwalter über die Webseite existiert?
7. Darf es mehrere Systemverwalter geben, oder soll genau ein Konto diese Rolle besitzen?
8. Was soll mit bestehenden Buchungen geschehen, wenn ein Kurs oder Raum geändert oder gelöscht werden soll?
9. Muss die Anwendung zwingend auf dem vorhandenen XAMPP 5.6.36 laufen, oder kann auf dem Schulrechner eine neuere PHP-Version eingesetzt werden?
10. Welche Sicherung der Datenbank wird erwartet und wer soll sie durchführen?

## 8. Datenbankentwurf

Für die Anwendung haben wir die folgenden Tabellen vorgesehen. Die Zuordnungstabellen bilden die Beziehungen zwischen mehreren Datensätzen ab.

| Tabelle | Inhalt |
|---|---|
| `mitarbeiter` | Benutzername, Passwort-Hash, Name, Rolle, Aktivierungsstatus und Aktivierungscode |
| `kurse` | Kursname, Beschreibung, Teilnehmermaximum und verantwortlicher Mitarbeiter |
| `raeume` | Raumname, Beschreibung und Anzahl der Arbeitsplätze |
| `buchungen` | Gebuchter Raum, Kurs, Zeitraum und erstellender Mitarbeiter |
| `software` | Verfügbare Softwarepakete |
| `kurs_software` | Benötigte Software eines Kurses |
| `raum_software` | Im Raum installierte Software |
| `kurs_mitarbeiter` | Zusätzliche Eigentümer eines Kursprofils |
| `raum_mitarbeiter` | Mitarbeitende mit Bearbeitungsrecht für einen Raum |

Das Schema liegt in `config/schema.sql`. Es erstellt die Datenbank `fitfuerinfo` und die Tabellen für eine Neuinstallation. Es enthält keine produktiven Mitarbeiterdaten und erstellt keinen Standardzugang.

## 9. Sicherheitsüberlegungen

- Passwörter werden nicht im Klartext gespeichert. In der Datenbank liegt ein Hash.
- Der Mitarbeiter setzt sein Passwort selbst über den Aktivierungslink.
- Der Aktivierungscode wird nach erfolgreicher Passwortfestlegung gelöscht.
- Geschützte Seiten prüfen Anmeldung und Rolle serverseitig.
- Datenbankwerte werden über PDO-Abfragen verarbeitet.
- Datenbankzugangsdaten müssen auf dem jeweiligen Rechner passend gesetzt werden und dürfen nicht öffentlich veröffentlicht werden.
- Der Auftrag beschreibt abgeschottete Umgebungen für bestimmte Sicherheitsschulungen. Die Webseite verwaltet Raum- und Softwareinformationen, richtet aber selbst keine Netzwerkisolation ein. Das muss auf Netzwerk- und Betriebssystemebene geplant werden.

## 10. Test- und Abnahmeplanung

Wir wollen mindestens folgende Fälle testen. Die Tabelle ist ein Testplan und kein bereits ausgefülltes Testprotokoll.

| Testfall | Erwartetes Ergebnis |
|---|---|
| Anmeldung mit gültigem Zugang | Das Dashboard wird geöffnet. |
| Anmeldung mit falschem Passwort | Der Zugang wird abgelehnt. |
| Passwort mit weniger als vier Zeichen oder ohne Kleinbuchstaben/Zahl | Das Passwort wird gemäß Vorgabe abgelehnt. |
| Mitarbeiter öffnet eine Administrationsseite | Der Zugriff wird verweigert. |
| Mitarbeiter bearbeitet ein fremdes, nicht zugewiesenes Kursprofil | Die Bearbeitung wird verweigert. |
| Zugewiesener Kurseigentümer bearbeitet ein Kursprofil | Die Bearbeitung ist möglich. |
| Mitarbeiter versucht, einen nicht zugewiesenen Raum zu bearbeiten | Die Bearbeitung wird verweigert. |
| Mitarbeiter erstellt eine Buchung in einem zu kleinen Raum | Die Buchung wird abgelehnt. |
| Kurs benötigt Software, die im Raum fehlt | Die Buchung wird abgelehnt und die fehlende Software benannt. |
| Raum ist im gewünschten Zeitraum bereits gebucht | Eine überschneidende Buchung wird abgelehnt. |
| Mitarbeiter löscht die eigene Buchung | Die Buchung wird gelöscht. |
| Mitarbeiter löscht die Buchung einer anderen Person | Die Aktion wird verweigert. |
| Systemverwalter löscht eine Buchung | Die Buchung wird gelöscht. |
| Anwendung wird auf Schul-XAMPP 5.6.36 gestartet | Die Seiten laden ohne Syntaxfehler und die Kernfunktionen sind nutzbar. |

## 11. Aktueller Stand unseres Projekts

Die aktuelle Anwendung enthält bereits eine Anmeldung, ein Dashboard, die Kurs-, Raum-, Software- und Buchungsverwaltung sowie einen Administratorbereich. Die zentrale Buchungslogik berücksichtigt Kapazität, Softwareausstattung und Überschneidungen. Auch Mitarbeiter- und Eigentümerzuordnungen sind vorhanden.

Vor der Abgabe müssen wir insbesondere noch die ursprüngliche Zielumgebung XAMPP 5.6.36 praktisch prüfen. Die aktuelle PHP-Fassung enthält PHP-7-Syntax und ist deshalb nicht ohne Anpassung mit PHP 5.6.36 kompatibel. Außerdem sollte der geplante Testablauf als ausgefülltes Testprotokoll ergänzt werden. Die offenen Fragen aus Abschnitt 7 müssen mit dem Auftraggeber geklärt oder als getroffene Annahmen dokumentiert werden.

## 12. Fazit

In unserem Projekt verbinden wir die Anforderungen an Kursprofile, Räume, Software und Buchungen mit einer SQL-Datenbank und einem PHP-Webfrontend. Bei der Planung achten wir besonders auf die unterschiedlichen Rechte von Mitarbeitenden und Systemverwaltern sowie auf die automatische Prüfung, ob ein Raum für einen Kurs geeignet und verfügbar ist.

Als wichtigste offene technische Aufgabe sehen wir die Kompatibilität zum vorgegebenen XAMPP 5.6.36. Als fachliche Klärung benötigen wir vor allem genaue Angaben zu Buchungszeiten und zur abgeschotteten Umgebung für Sicherheitsschulungen. Erst nach diesen Prüfungen und einem dokumentierten Testlauf können wir die Anwendung als vollständig abgenommen betrachten.
