-- Vollständiges Datenbankschema für FitFuerInfo.
-- In phpMyAdmin oder mit dem mysql-Client ausführen.

CREATE DATABASE IF NOT EXISTS fitfuerinfo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fitfuerinfo;

CREATE TABLE IF NOT EXISTS mitarbeiter (
    mitarbeiter_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    benutzername VARCHAR(50) NOT NULL,
    passwort VARCHAR(255) DEFAULT NULL,
    vorname VARCHAR(100) NOT NULL,
    nachname VARCHAR(100) NOT NULL,
    ist_admin TINYINT(1) NOT NULL DEFAULT 0,
    aktiv TINYINT(1) NOT NULL DEFAULT 0,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aktivierungscode VARCHAR(64) DEFAULT NULL,
    PRIMARY KEY (mitarbeiter_id),
    UNIQUE KEY uq_mitarbeiter_benutzername (benutzername),
    UNIQUE KEY uq_mitarbeiter_aktivierungscode (aktivierungscode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kurse (
    kurs_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    beschreibung TEXT NOT NULL,
    max_teilnehmer INT UNSIGNED NOT NULL,
    mitarbeiter_id INT UNSIGNED NOT NULL,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (kurs_id),
    KEY idx_kurse_mitarbeiter (mitarbeiter_id),
    CONSTRAINT fk_kurse_mitarbeiter
        FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter (mitarbeiter_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS raeume (
    raum_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    beschreibung TEXT NOT NULL,
    arbeitsplaetze INT UNSIGNED NOT NULL,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (raum_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS software (
    software_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    beschreibung TEXT DEFAULT NULL,
    PRIMARY KEY (software_id),
    UNIQUE KEY uq_software_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS buchungen (
    buchungs_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    raum_id INT UNSIGNED NOT NULL,
    kurs_id INT UNSIGNED NOT NULL,
    beginn DATE NOT NULL,
    ende DATE NOT NULL,
    mitarbeiter_id INT UNSIGNED NOT NULL,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (buchungs_id),
    KEY idx_buchungen_raum_zeitraum (raum_id, beginn, ende),
    KEY idx_buchungen_kurs (kurs_id),
    KEY idx_buchungen_mitarbeiter (mitarbeiter_id),
    CONSTRAINT fk_buchungen_raum
        FOREIGN KEY (raum_id) REFERENCES raeume (raum_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_buchungen_kurs
        FOREIGN KEY (kurs_id) REFERENCES kurse (kurs_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_buchungen_mitarbeiter
        FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter (mitarbeiter_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kurs_software (
    kurs_id INT UNSIGNED NOT NULL,
    software_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (kurs_id, software_id),
    KEY idx_kurs_software_software (software_id),
    CONSTRAINT fk_kurs_software_kurs
        FOREIGN KEY (kurs_id) REFERENCES kurse (kurs_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_kurs_software_software
        FOREIGN KEY (software_id) REFERENCES software (software_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS raum_software (
    raum_id INT UNSIGNED NOT NULL,
    software_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (raum_id, software_id),
    KEY idx_raum_software_software (software_id),
    CONSTRAINT fk_raum_software_raum
        FOREIGN KEY (raum_id) REFERENCES raeume (raum_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_raum_software_software
        FOREIGN KEY (software_id) REFERENCES software (software_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kurs_mitarbeiter (
    kurs_id INT UNSIGNED NOT NULL,
    mitarbeiter_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (kurs_id, mitarbeiter_id),
    KEY idx_kurs_mitarbeiter_mitarbeiter (mitarbeiter_id),
    CONSTRAINT fk_kurs_mitarbeiter_kurs
        FOREIGN KEY (kurs_id) REFERENCES kurse (kurs_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_kurs_mitarbeiter_mitarbeiter
        FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter (mitarbeiter_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS raum_mitarbeiter (
    raum_id INT UNSIGNED NOT NULL,
    mitarbeiter_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (raum_id, mitarbeiter_id),
    KEY idx_raum_mitarbeiter_mitarbeiter (mitarbeiter_id),
    CONSTRAINT fk_raum_mitarbeiter_raum
        FOREIGN KEY (raum_id) REFERENCES raeume (raum_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_raum_mitarbeiter_mitarbeiter
        FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter (mitarbeiter_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
