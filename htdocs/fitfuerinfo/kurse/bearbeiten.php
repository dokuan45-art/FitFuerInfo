<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$sql = "SELECT * FROM kurse WHERE kurs_id = :kurs_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":kurs_id" => $id]);
$kurs = $stmt->fetch();

if (!$kurs) {
    die("Kurs nicht gefunden.");
}

$istAdmin = ($_SESSION["ist_admin"] == 1);

$istErsteller = ((int)$kurs["mitarbeiter_id"] === (int)$_SESSION["mitarbeiter_id"]);

$sql = "SELECT 1 FROM kurs_mitarbeiter
        WHERE kurs_id = :kurs_id
        AND mitarbeiter_id = :mitarbeiter_id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":kurs_id" => $id,
    ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
]);
$istZusatzEigentuemer = ($stmt->fetch() !== false);

if (!$istAdmin && !$istErsteller && !$istZusatzEigentuemer) {
    die("Du hast keine Berechtigung, diesen Kurs zu bearbeiten.");
}

$fehler = "";
$meldung = "";

$stmt = $pdo->query("SELECT software_id, name FROM software ORDER BY name ASC");
$softwarePakete = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT software_id FROM kurs_software WHERE kurs_id = :kurs_id");
$stmt->execute(array(":kurs_id" => $id));
$ausgewaehlteSoftwareIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aktion = isset($_POST["aktion"]) ? trim($_POST["aktion"]) : "save_course";

    if ($aktion === "save_course") {
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
        $maxTeilnehmer = isset($_POST["max_teilnehmer"]) ? trim($_POST["max_teilnehmer"]) : "";
        $softwareIds = isset($_POST["software_ids"]) && is_array($_POST["software_ids"]) ? $_POST["software_ids"] : array();
        $gueltigeSoftwareIds = array();

        foreach ($softwareIds as $softwareId) {
            if (ctype_digit((string)$softwareId) && (int)$softwareId > 0) {
                $gueltigeSoftwareIds[] = (int)$softwareId;
            }
        }
        $gueltigeSoftwareIds = array_values(array_unique($gueltigeSoftwareIds));
        $ausgewaehlteSoftwareIds = $gueltigeSoftwareIds;

        if ($name === "") {
            $fehler = "Bitte einen Kursnamen eingeben.";
        } elseif (!ctype_digit($maxTeilnehmer) || (int)$maxTeilnehmer <= 0) {
            $fehler = "Bitte eine gültige Anzahl an Teilnehmern eingeben.";
        } else {
            try {
                $pdo->beginTransaction();
                $sql = "UPDATE kurse SET name = :name, beschreibung = :beschreibung, max_teilnehmer = :max_teilnehmer WHERE kurs_id = :kurs_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":name" => $name, ":beschreibung" => $beschreibung, ":max_teilnehmer" => (int)$maxTeilnehmer, ":kurs_id" => $id));

                $stmt = $pdo->prepare("DELETE FROM kurs_software WHERE kurs_id = :kurs_id");
                $stmt->execute(array(":kurs_id" => $id));

                if (!empty($gueltigeSoftwareIds)) {
                    $stmt = $pdo->prepare("INSERT INTO kurs_software (kurs_id, software_id) VALUES (:kurs_id, :software_id)");
                    foreach ($gueltigeSoftwareIds as $softwareId) {
                        $stmt->execute(array(":kurs_id" => $id, ":software_id" => $softwareId));
                    }
                }

                $pdo->commit();
                header("Location: uebersicht.php");
                exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $fehler = "Kurs oder Softwarezuordnung konnte nicht gespeichert werden.";
            }
        }
    }

    if (($aktion === "add_owner" || $aktion === "remove_owner") && !$istAdmin) {
        $fehler = "Nur Administratoren dürfen Eigentümer verwalten.";
    }

    if ($aktion === "add_owner" && $fehler === "") {
        $neuerEigentuemerId = isset($_POST["neuer_eigentuemer_id"]) ? (int)$_POST["neuer_eigentuemer_id"] : 0;

        if ($neuerEigentuemerId <= 0) {
            $fehler = "Bitte einen Mitarbeiter auswählen.";
        } elseif ($neuerEigentuemerId === (int)$kurs["mitarbeiter_id"]) {
            $fehler = "Der Ersteller ist bereits Eigentümer.";
        } else {
            $sql = "SELECT 1 FROM mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":mitarbeiter_id" => $neuerEigentuemerId]);

            if (!$stmt->fetch()) {
                $fehler = "Ausgewählter Mitarbeiter wurde nicht gefunden.";
            } else {
                $sql = "SELECT 1 FROM kurs_mitarbeiter WHERE kurs_id = :kurs_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ":kurs_id" => $id,
                    ":mitarbeiter_id" => $neuerEigentuemerId
                ]);

                if ($stmt->fetch()) {
                    $fehler = "Mitarbeiter ist bereits als zusätzlicher Eigentümer eingetragen.";
                } else {
                    $sql = "INSERT INTO kurs_mitarbeiter (kurs_id, mitarbeiter_id) VALUES (:kurs_id, :mitarbeiter_id)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ":kurs_id" => $id,
                        ":mitarbeiter_id" => $neuerEigentuemerId
                    ]);

                    $meldung = "Zusätzlicher Eigentümer wurde hinzugefügt.";
                }
            }
        }
    }

    if ($aktion === "remove_owner" && $fehler === "") {
        $eigentuemerId = isset($_POST["eigentuemer_id"]) ? (int)$_POST["eigentuemer_id"] : 0;

        if ($eigentuemerId <= 0) {
            $fehler = "Ungültiger Eigentümer.";
        } else {
            $sql = "DELETE FROM kurs_mitarbeiter
                    WHERE kurs_id = :kurs_id
                    AND mitarbeiter_id = :mitarbeiter_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":kurs_id" => $id,
                ":mitarbeiter_id" => $eigentuemerId
            ]);

            if ($stmt->rowCount() > 0) {
                $meldung = "Zusätzlicher Eigentümer wurde entfernt.";
            } else {
                $fehler = "Eigentümer konnte nicht entfernt werden.";
            }
        }
    }
}

$sql = "SELECT m.mitarbeiter_id, m.vorname, m.nachname, m.benutzername
        FROM kurs_mitarbeiter km
        JOIN mitarbeiter m ON m.mitarbeiter_id = km.mitarbeiter_id
        WHERE km.kurs_id = :kurs_id
        ORDER BY m.nachname ASC, m.vorname ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([":kurs_id" => $id]);
$zusaetzlicheEigentuemer = $stmt->fetchAll();

$sql = "SELECT mitarbeiter_id, vorname, nachname, benutzername
        FROM mitarbeiter
        WHERE mitarbeiter_id = :mitarbeiter_id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":mitarbeiter_id" => (int)$kurs["mitarbeiter_id"]]);
$erstellerDaten = $stmt->fetch();

$verfuegbareEigentuemer = [];
if ($istAdmin) {
    $sql = "SELECT m.mitarbeiter_id, m.vorname, m.nachname, m.benutzername
            FROM mitarbeiter m
            LEFT JOIN kurs_mitarbeiter km
                ON km.mitarbeiter_id = m.mitarbeiter_id
                AND km.kurs_id = :kurs_id
            WHERE m.mitarbeiter_id <> :ersteller_id
            AND km.mitarbeiter_id IS NULL
            ORDER BY m.nachname ASC, m.vorname ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":kurs_id" => $id,
        ":ersteller_id" => (int)$kurs["mitarbeiter_id"]
    ]);
    $verfuegbareEigentuemer = $stmt->fetchAll();
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Kurs bearbeiten</h2>

        <?php if ($fehler !== "") : ?>
            <div class="w3-panel w3-red">
                <p><?php echo htmlspecialchars($fehler); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($meldung !== "") : ?>
            <div class="w3-panel w3-green">
                <p><?php echo htmlspecialchars($meldung); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="aktion" value="save_course">
            <label><b>Kursname</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($kurs["name"]); ?>"
                required
            >

            <label><b>Beschreibung</b></label>
            <textarea
                class="w3-input w3-border w3-margin-bottom"
                name="beschreibung"
                rows="5"
            ><?php echo htmlspecialchars($kurs["beschreibung"]); ?></textarea>

            <label><b>Max. Teilnehmer</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="number"
                name="max_teilnehmer"
                min="1"
                value="<?php echo htmlspecialchars($kurs["max_teilnehmer"]); ?>"
                required
            >

            <label><b>Benötigte Softwarepakete</b></label>
            <div class="w3-border w3-padding w3-margin-bottom">
                <?php foreach ($softwarePakete as $paket): ?>
                    <label class="w3-block">
                        <input type="checkbox" name="software_ids[]" value="<?php echo (int)$paket["software_id"]; ?>" <?php echo in_array((string)$paket["software_id"], array_map("strval", $ausgewaehlteSoftwareIds), true) ? "checked" : ""; ?>>
                        <?php echo htmlspecialchars($paket["name"]); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p><a href="../software/uebersicht.php" class="w3-button w3-indigo">Softwareverwaltung öffnen</a></p>

            <button type="submit" class="w3-button w3-blue">Änderungen speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Zurück</a>
        </form>

        <?php if ($istAdmin): ?>
            <hr>
            <h3>Eigentümer verwalten (Admin)</h3>

            <div class="w3-panel w3-light-grey">
                <p><strong>Ersteller:</strong>
                    <?php if ($erstellerDaten): ?>
                        <?php echo htmlspecialchars(trim($erstellerDaten["vorname"] . " " . $erstellerDaten["nachname"])); ?>
                        (<?php echo htmlspecialchars($erstellerDaten["benutzername"]); ?>)
                    <?php else: ?>
                        Unbekannt
                    <?php endif; ?>
                </p>

                <p><strong>Zusätzliche Eigentümer:</strong></p>
                <?php if (empty($zusaetzlicheEigentuemer)): ?>
                    <p>Keine zusätzlichen Eigentümer vorhanden.</p>
                <?php else: ?>
                    <?php foreach ($zusaetzlicheEigentuemer as $eigentuemer): ?>
                        <div class="w3-bar w3-margin-bottom">
                            <span class="w3-bar-item">
                                <?php echo htmlspecialchars(trim($eigentuemer["vorname"] . " " . $eigentuemer["nachname"])); ?>
                                (<?php echo htmlspecialchars($eigentuemer["benutzername"]); ?>)
                            </span>
                            <form method="post" class="w3-bar-item" style="padding:0;">
                                <input type="hidden" name="aktion" value="remove_owner">
                                <input type="hidden" name="eigentuemer_id" value="<?php echo (int)$eigentuemer["mitarbeiter_id"]; ?>">
                                <button type="submit" class="w3-button w3-red w3-small" onclick="return confirm('Zusätzlichen Eigentümer wirklich entfernen?');">
                                    Entfernen
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post" class="w3-margin-top">
                <input type="hidden" name="aktion" value="add_owner">
                <label><b>Zusätzlichen Eigentümer hinzufügen</b></label>
                <select class="w3-select w3-border w3-margin-bottom" name="neuer_eigentuemer_id">
                    <option value="">Bitte wählen</option>
                    <?php foreach ($verfuegbareEigentuemer as $mitarbeiter): ?>
                        <option value="<?php echo (int)$mitarbeiter["mitarbeiter_id"]; ?>">
                            <?php echo htmlspecialchars(trim($mitarbeiter["vorname"] . " " . $mitarbeiter["nachname"])); ?>
                            (<?php echo htmlspecialchars($mitarbeiter["benutzername"]); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="w3-button w3-indigo">Eigentümer hinzufügen</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
include("../includes/footer.php");
?>
