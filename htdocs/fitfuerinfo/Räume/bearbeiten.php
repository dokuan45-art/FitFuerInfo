<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$sql = "SELECT * FROM raeume WHERE raum_id = :raum_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":raum_id" => $id]);
$raum = $stmt->fetch();

if (!$raum) {
    die("Raum nicht gefunden.");
}

$istAdmin = ($_SESSION["ist_admin"] == 1);

$sql = "SELECT 1 FROM raum_mitarbeiter WHERE raum_id = :raum_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":raum_id" => $id,
    ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
]);
$istErsteller = ($stmt->fetch() !== false);

if (!$istAdmin && !$istErsteller) {
    die("Du hast keine Berechtigung, diesen Raum zu bearbeiten.");
}

$fehler = "";
$meldung = "";
$softwareStmt = $pdo->query("SELECT software_id, name FROM software ORDER BY name ASC");
$softwarePakete = $softwareStmt->fetchAll();
$softwareIdsAuswahl = array();
$softwareStmt = $pdo->prepare("SELECT software_id FROM raum_software WHERE raum_id = :raum_id");
$softwareStmt->execute(array(":raum_id" => $id));
foreach ($softwareStmt->fetchAll() as $softwareZeile) { $softwareIdsAuswahl[] = (string)$softwareZeile["software_id"]; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aktion = isset($_POST["aktion"]) ? trim($_POST["aktion"]) : "save_raum";

    if ($aktion === "save_raum") {
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
        $arbeitsplaetze = isset($_POST["arbeitsplaetze"]) ? trim($_POST["arbeitsplaetze"]) : "";
        $softwareIds = isset($_POST["software_ids"]) && is_array($_POST["software_ids"]) ? $_POST["software_ids"] : array();
        $softwareIds = array_values(array_unique(array_filter(array_map("intval", $softwareIds), function ($softwareId) { return $softwareId > 0; })));
        $softwareIdsAuswahl = array_map("strval", $softwareIds);

        if ($name === "") {
            $fehler = "Bitte einen Raumnamen eingeben.";
        } elseif ($arbeitsplaetze === "" || !ctype_digit($arbeitsplaetze) || (int)$arbeitsplaetze <= 0) {
            $fehler = "Bitte eine gültige Anzahl an Arbeitsplätzen angeben.";
        } else {
            try {
                $pdo->beginTransaction();
                $sql = "UPDATE raeume SET name = :name, beschreibung = :beschreibung, arbeitsplaetze = :arbeitsplaetze WHERE raum_id = :raum_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(":name" => $name, ":beschreibung" => $beschreibung, ":arbeitsplaetze" => (int)$arbeitsplaetze, ":raum_id" => $id));
                $stmt = $pdo->prepare("DELETE FROM raum_software WHERE raum_id = :raum_id");
                $stmt->execute(array(":raum_id" => $id));
                if (!empty($softwareIds)) {
                    $stmt = $pdo->prepare("INSERT INTO raum_software (raum_id, software_id) VALUES (:raum_id, :software_id)");
                    foreach ($softwareIds as $softwareId) { $stmt->execute(array(":raum_id" => $id, ":software_id" => $softwareId)); }
                }
                $pdo->commit();
                header("Location: uebersicht.php"); exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $fehler = "Raum oder Softwarezuordnung konnte nicht gespeichert werden.";
            }
        }
    }

    if (($aktion === "add_berechtigung" || $aktion === "remove_berechtigung") && !$istAdmin) {
        $fehler = "Nur Administratoren dürfen Raum-Berechtigungen verwalten.";
    }

    if ($aktion === "add_berechtigung" && $fehler === "") {
        $neuerMitarbeiterId = isset($_POST["neuer_mitarbeiter_id"]) ? (int)$_POST["neuer_mitarbeiter_id"] : 0;

        if ($neuerMitarbeiterId <= 0) {
            $fehler = "Bitte einen Mitarbeiter auswählen.";
        } else {
            $sql = "SELECT 1 FROM mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":mitarbeiter_id" => $neuerMitarbeiterId]);

            if (!$stmt->fetch()) {
                $fehler = "Ausgewählter Mitarbeiter wurde nicht gefunden.";
            } else {
                $sql = "SELECT 1 FROM raum_mitarbeiter WHERE raum_id = :raum_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ":raum_id" => $id,
                    ":mitarbeiter_id" => $neuerMitarbeiterId
                ]);

                if ($stmt->fetch()) {
                    $fehler = "Mitarbeiter hat bereits Bearbeitungsrechte für diesen Raum.";
                } else {
                    $sql = "INSERT INTO raum_mitarbeiter (raum_id, mitarbeiter_id) VALUES (:raum_id, :mitarbeiter_id)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ":raum_id" => $id,
                        ":mitarbeiter_id" => $neuerMitarbeiterId
                    ]);

                    $meldung = "Raum-Berechtigung wurde hinzugefügt.";
                }
            }
        }
    }

    if ($aktion === "remove_berechtigung" && $fehler === "") {
        $mitarbeiterId = isset($_POST["mitarbeiter_id_recht"]) ? (int)$_POST["mitarbeiter_id_recht"] : 0;

        if ($mitarbeiterId <= 0) {
            $fehler = "Ungültiger Mitarbeiter.";
        } else {
            $sql = "DELETE FROM raum_mitarbeiter
                    WHERE raum_id = :raum_id
                    AND mitarbeiter_id = :mitarbeiter_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":raum_id" => $id,
                ":mitarbeiter_id" => $mitarbeiterId
            ]);

            if ($stmt->rowCount() > 0) {
                $meldung = "Raum-Berechtigung wurde entfernt.";
            } else {
                $fehler = "Raum-Berechtigung konnte nicht entfernt werden.";
            }
        }
    }
}

$berechtigteMitarbeiter = [];
$verfuegbareMitarbeiter = [];
if ($istAdmin) {
    $sql = "SELECT m.mitarbeiter_id, m.vorname, m.nachname, m.benutzername
            FROM raum_mitarbeiter rm
            JOIN mitarbeiter m ON m.mitarbeiter_id = rm.mitarbeiter_id
            WHERE rm.raum_id = :raum_id
            ORDER BY m.nachname ASC, m.vorname ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":raum_id" => $id]);
    $berechtigteMitarbeiter = $stmt->fetchAll();

    $sql = "SELECT m.mitarbeiter_id, m.vorname, m.nachname, m.benutzername
            FROM mitarbeiter m
            LEFT JOIN raum_mitarbeiter rm
                ON rm.mitarbeiter_id = m.mitarbeiter_id
                AND rm.raum_id = :raum_id
            WHERE rm.mitarbeiter_id IS NULL
            ORDER BY m.nachname ASC, m.vorname ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":raum_id" => $id]);
    $verfuegbareMitarbeiter = $stmt->fetchAll();
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Raum bearbeiten</h2>

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
            <input type="hidden" name="aktion" value="save_raum">
            <label><b>Raumname</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($raum["name"]); ?>"
                required
            >

            <label><b>Installierte Softwarepakete</b></label>
            <select class="w3-select w3-border w3-margin-bottom" name="software_ids[]" multiple size="6">
                <?php foreach ($softwarePakete as $paket): ?>
                    <option value="<?php echo (int)$paket["software_id"]; ?>" <?php echo in_array((string)$paket["software_id"], $softwareIdsAuswahl, true) ? "selected" : ""; ?>><?php echo htmlspecialchars($paket["name"]); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="w3-small">Mehrfachauswahl mit Strg/Command.</p>

            <label><b>Beschreibung</b></label>
            <textarea
                class="w3-input w3-border w3-margin-bottom"
                name="beschreibung"
                rows="5"
            ><?php echo htmlspecialchars($raum["beschreibung"]); ?></textarea>

            <label><b>Arbeitsplätze</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="number"
                name="arbeitsplaetze"
                min="1"
                value="<?php echo htmlspecialchars($raum["arbeitsplaetze"]); ?>"
                required
            >

            <button type="submit" class="w3-button w3-blue">Änderungen speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Zurück</a>
        </form>

        <?php if ($istAdmin): ?>
            <hr>
            <h3>Raum-Berechtigungen verwalten (Admin)</h3>

            <div class="w3-panel w3-light-grey">
                <p><strong>Aktuell berechtigte Mitarbeiter:</strong></p>
                <?php if (empty($berechtigteMitarbeiter)): ?>
                    <p>Keine zusätzlichen Berechtigungen vorhanden.</p>
                <?php else: ?>
                    <?php foreach ($berechtigteMitarbeiter as $mitarbeiter): ?>
                        <div class="w3-bar w3-margin-bottom">
                            <span class="w3-bar-item">
                                <?php echo htmlspecialchars(trim($mitarbeiter["vorname"] . " " . $mitarbeiter["nachname"])); ?>
                                (<?php echo htmlspecialchars($mitarbeiter["benutzername"]); ?>)
                            </span>
                            <form method="post" class="w3-bar-item" style="padding:0;">
                                <input type="hidden" name="aktion" value="remove_berechtigung">
                                <input type="hidden" name="mitarbeiter_id_recht" value="<?php echo (int)$mitarbeiter["mitarbeiter_id"]; ?>">
                                <button type="submit" class="w3-button w3-red w3-small" onclick="return confirm('Berechtigung wirklich entziehen?');">
                                    Entziehen
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post" class="w3-margin-top">
                <input type="hidden" name="aktion" value="add_berechtigung">
                <label><b>Mitarbeiter berechtigen</b></label>
                <select class="w3-select w3-border w3-margin-bottom" name="neuer_mitarbeiter_id">
                    <option value="">Bitte wählen</option>
                    <?php foreach ($verfuegbareMitarbeiter as $mitarbeiter): ?>
                        <option value="<?php echo (int)$mitarbeiter["mitarbeiter_id"]; ?>">
                            <?php echo htmlspecialchars(trim($mitarbeiter["vorname"] . " " . $mitarbeiter["nachname"])); ?>
                            (<?php echo htmlspecialchars($mitarbeiter["benutzername"]); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="w3-button w3-indigo">Berechtigung hinzufügen</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
