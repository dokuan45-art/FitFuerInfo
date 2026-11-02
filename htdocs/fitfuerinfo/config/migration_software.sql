CREATE TABLE IF NOT EXISTS software (
    software_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kurs_software (
    kurs_id INT NOT NULL,
    software_id INT NOT NULL,
    PRIMARY KEY (kurs_id, software_id),
    CONSTRAINT fk_kurs_software_kurs
        FOREIGN KEY (kurs_id) REFERENCES kurse(kurs_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_kurs_software_software
        FOREIGN KEY (software_id) REFERENCES software(software_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS raum_software (
    raum_id INT NOT NULL,
    software_id INT NOT NULL,
    PRIMARY KEY (raum_id, software_id),
    CONSTRAINT fk_raum_software_raum
        FOREIGN KEY (raum_id) REFERENCES raeume(raum_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_raum_software_software
        FOREIGN KEY (software_id) REFERENCES software(software_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
