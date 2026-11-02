<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$fehler = "";

$sql = "SELECT software_id, name FROM software ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$softwarePakete = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $max_teilnehmer = isset($_POST["max_teilnehmer"]) ? trim($_POST["max_teilnehmer"]) : "";
    $softwareIds = isset($_POST["software_ids"]) && is_array($_POST["software_ids"]) ? $_POST["software_ids"] : [];

    $gueltigeSoftwareIds = [];
    foreach ($softwareIds as $softwareId) {
        if (ctype_digit((string)$softwareId) && (int)$softwareId > 0) {
            $gueltigeSoftwareIds[] = (int)$softwareId;
        }
    }
    $gueltigeSoftwareIds = array_values(array_unique($gueltigeSoftwareIds));

    if ($name === "") {
        $fehler = "Bitte einen Kursnamen eingeben.";
    } elseif ($max_teilnehmer === "" || !ctype_digit($max_teilnehmer) || (int)$max_teilnehmer <= 0) {
        $fehler = "Bitte eine gültige Anzahl an Teilnehmern eingeben.";
    } else {
        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO kurse (name, beschreibung, max_teilnehmer, mitarbeiter_id, erstellt_am)
                    VALUES (:name, :beschreibung, :max_teilnehmer, :mitarbeiter_id, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":name" => $name,
                ":beschreibung" => $beschreibung,
                ":max_teilnehmer" => (int)$max_teilnehmer,
                ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
            ]);

            $kursId = (int)$pdo->lastInsertId();

            if (!empty($gueltigeSoftwareIds)) {
                $sql = "INSERT INTO kurs_software (kurs_id, software_id) VALUES (:kurs_id, :software_id)";
                $stmt = $pdo->prepare($sql);

                foreach ($gueltigeSoftwareIds as $softwareId) {
                    $stmt->execute([
                        ":kurs_id" => $kursId,
                        ":software_id" => $softwareId
                    ]);
                }
            }

            $pdo->commit();
            header("Location: uebersicht.php");
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $fehler = "Kurs konnte nicht gespeichert werden.";
        }
    }
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Neuen Kurs anlegen</h2>

        <?php if ($fehler !== "") : ?>
            <div class="w3-panel w3-red">
                <p><?php echo htmlspecialchars($fehler); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <label><b>Kursname</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="name"
                value="<?php echo isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""; ?>"
                required
            >

            <label><b>Beschreibung</b></label>
            <textarea
                class="w3-input w3-border w3-margin-bottom"
                name="beschreibung"
                rows="5"
            ><?php echo isset($_POST["beschreibung"]) ? htmlspecialchars($_POST["beschreibung"]) : ""; ?></textarea>

            <label><b>Max. Teilnehmer</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="number"
                name="max_teilnehmer"
                min="1"
                value="<?php echo isset($_POST["max_teilnehmer"]) ? htmlspecialchars($_POST["max_teilnehmer"]) : ""; ?>"
                required
            >

            <label><b>Benötigte Softwarepakete</b></label>
            <select class="w3-select w3-border w3-margin-bottom" name="software_ids[]" multiple size="6">
                <?php foreach ($softwarePakete as $paket): ?>
                    <?php
                    $selected = isset($_POST["software_ids"]) && is_array($_POST["software_ids"]) && in_array((string)$paket["software_id"], $_POST["software_ids"], true);
                    ?>
                    <option value="<?php echo (int)$paket["software_id"]; ?>" <?php echo $selected ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($paket["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="w3-small">Mehrfachauswahl mit Strg/Command.</p>

            <button type="submit" class="w3-button w3-blue">Kurs speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Abbrechen</a>
        </form>
    </div>
</div>

<?php
include("../includes/footer.php");
?>
