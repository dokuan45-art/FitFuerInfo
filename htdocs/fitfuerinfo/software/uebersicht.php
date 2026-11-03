<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}
if (!isset($_SESSION["ist_admin"]) || (int)$_SESSION["ist_admin"] !== 1) {
    die("Zugriff verweigert. Nur Administratoren dürfen Software verwalten.");
}

require_once "../config/database.php";

function idsAusFormular($werte) {
    if (!is_array($werte)) {
        return array();
    }
    $ids = array();
    foreach ($werte as $wert) {
        if (ctype_digit((string)$wert) && (int)$wert > 0) {
            $ids[] = (int)$wert;
        }
    }
    return array_values(array_unique($ids));
}

$fehler = "";
$meldung = "";
$bearbeitenId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$bearbeiten = null;
$ausgewaehlteRaumIds = array();
$ausgewaehlteKursIds = array();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aktion = isset($_POST["aktion"]) ? $_POST["aktion"] : "";
    $id = isset($_POST["software_id"]) ? (int)$_POST["software_id"] : 0;

    if ($aktion === "speichern") {
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
        $ausgewaehlteRaumIds = idsAusFormular(isset($_POST["raum_ids"]) ? $_POST["raum_ids"] : array());
        $ausgewaehlteKursIds = idsAusFormular(isset($_POST["kurs_ids"]) ? $_POST["kurs_ids"] : array());

        if ($name === "") {
            $fehler = "Bitte einen Titel für das Softwarepaket angeben.";
        } else {
            $checkStmt = $pdo->prepare("SELECT software_id FROM software WHERE name = :name AND software_id <> :id LIMIT 1");
            $checkStmt->execute(array(":name" => $name, ":id" => $id));

            if ($checkStmt->fetch()) {
                $fehler = "Ein Softwarepaket mit diesem Titel existiert bereits.";
            } else {
                try {
                    $pdo->beginTransaction();
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE software SET name = :name, beschreibung = :beschreibung WHERE software_id = :id");
                        $stmt->execute(array(":name" => $name, ":beschreibung" => $beschreibung, ":id" => $id));
                        $softwareId = $id;
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO software (name, beschreibung) VALUES (:name, :beschreibung)");
                        $stmt->execute(array(":name" => $name, ":beschreibung" => $beschreibung));
                        $softwareId = (int)$pdo->lastInsertId();
                    }

                    $stmt = $pdo->prepare("DELETE FROM raum_software WHERE software_id = :software_id");
                    $stmt->execute(array(":software_id" => $softwareId));
                    $stmt = $pdo->prepare("DELETE FROM kurs_software WHERE software_id = :software_id");
                    $stmt->execute(array(":software_id" => $softwareId));

                    $stmt = $pdo->prepare("INSERT INTO raum_software (raum_id, software_id) VALUES (:raum_id, :software_id)");
                    foreach ($ausgewaehlteRaumIds as $raumId) {
                        $stmt->execute(array(":raum_id" => $raumId, ":software_id" => $softwareId));
                    }
                    $stmt = $pdo->prepare("INSERT INTO kurs_software (kurs_id, software_id) VALUES (:kurs_id, :software_id)");
                    foreach ($ausgewaehlteKursIds as $kursId) {
                        $stmt->execute(array(":kurs_id" => $kursId, ":software_id" => $softwareId));
                    }

                    $pdo->commit();
                    $meldung = "Softwarepaket und Zuordnungen wurden gespeichert.";
                    $bearbeitenId = 0;
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $fehler = "Softwarepaket oder Zuordnungen konnten nicht gespeichert werden.";
                }
            }
        }
    } elseif ($aktion === "loeschen" && $id > 0) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM kurs_software WHERE software_id = :id");
            $stmt->execute(array(":id" => $id));
            $stmt = $pdo->prepare("DELETE FROM raum_software WHERE software_id = :id");
            $stmt->execute(array(":id" => $id));
            $stmt = $pdo->prepare("DELETE FROM software WHERE software_id = :id");
            $stmt->execute(array(":id" => $id));
            $pdo->commit();
            $meldung = "Softwarepaket und seine Zuordnungen wurden gelöscht.";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $fehler = "Softwarepaket konnte nicht gelöscht werden.";
        }
    }
}

if ($bearbeitenId > 0) {
    $stmt = $pdo->prepare("SELECT software_id, name, beschreibung FROM software WHERE software_id = :id");
    $stmt->execute(array(":id" => $bearbeitenId));
    $bearbeiten = $stmt->fetch();
    if ($bearbeiten && $_SERVER["REQUEST_METHOD"] !== "POST") {
        $stmt = $pdo->prepare("SELECT raum_id FROM raum_software WHERE software_id = :software_id");
        $stmt->execute(array(":software_id" => $bearbeitenId));
        $ausgewaehlteRaumIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare("SELECT kurs_id FROM kurs_software WHERE software_id = :software_id");
        $stmt->execute(array(":software_id" => $bearbeitenId));
        $ausgewaehlteKursIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

if (!$bearbeiten && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $raumId = isset($_GET["raum_id"]) ? (int)$_GET["raum_id"] : 0;
    $kursId = isset($_GET["kurs_id"]) ? (int)$_GET["kurs_id"] : 0;
    if ($raumId > 0) { $ausgewaehlteRaumIds[] = $raumId; }
    if ($kursId > 0) { $ausgewaehlteKursIds[] = $kursId; }
}

$raeume = $pdo->query("SELECT raum_id, name FROM raeume ORDER BY name ASC")->fetchAll();
$kurse = $pdo->query("SELECT kurs_id, name FROM kurse ORDER BY name ASC")->fetchAll();
$softwarePakete = $pdo->query("SELECT s.software_id, s.name, s.beschreibung,
    (SELECT GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ')
     FROM raum_software rs
     JOIN raeume r ON r.raum_id = rs.raum_id
     WHERE rs.software_id = s.software_id) AS raeume
    FROM software s
    ORDER BY s.name ASC")->fetchAll();

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Softwareverwaltung</h2>
        <p>Ein Paket wird nur den ausgewählten Räumen und Kursen zugeordnet.</p>

        <?php if ($fehler !== ""): ?><div class="w3-panel w3-red"><p><?php echo htmlspecialchars($fehler); ?></p></div><?php endif; ?>
        <?php if ($meldung !== ""): ?><div class="w3-panel w3-green"><p><?php echo htmlspecialchars($meldung); ?></p></div><?php endif; ?>

        <h3><?php echo $bearbeiten ? "Softwarepaket bearbeiten" : "Softwarepaket hinzufügen"; ?></h3>
        <form method="post">
            <input type="hidden" name="aktion" value="speichern">
            <input type="hidden" name="software_id" value="<?php echo $bearbeiten ? (int)$bearbeiten["software_id"] : 0; ?>">

            <label><b>Titel</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" name="name" maxlength="120" required value="<?php echo htmlspecialchars($bearbeiten ? $bearbeiten["name"] : (isset($_POST["name"]) ? $_POST["name"] : "")); ?>">
            <label><b>Beschreibung</b></label>
            <textarea class="w3-input w3-border w3-margin-bottom" name="beschreibung" rows="4"><?php echo htmlspecialchars($bearbeiten ? $bearbeiten["beschreibung"] : (isset($_POST["beschreibung"]) ? $_POST["beschreibung"] : "")); ?></textarea>

            <div class="w3-row-padding">
                <div class="w3-half">
                    <label><b>In diesen Räumen installiert</b></label>
                    <div class="w3-border w3-padding w3-margin-bottom" style="max-height:220px; overflow:auto;">
                        <?php if (empty($raeume)): ?><p>Keine Räume vorhanden.</p><?php endif; ?>
                        <?php foreach ($raeume as $raum): ?>
                            <label class="w3-block"><input type="checkbox" name="raum_ids[]" value="<?php echo (int)$raum["raum_id"]; ?>" <?php echo in_array((string)$raum["raum_id"], array_map("strval", $ausgewaehlteRaumIds), true) ? "checked" : ""; ?>> <?php echo htmlspecialchars($raum["name"]); ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="w3-half">
                    <label><b>Für diese Kurse benötigt</b></label>
                    <div class="w3-border w3-padding w3-margin-bottom" style="max-height:220px; overflow:auto;">
                        <?php if (empty($kurse)): ?><p>Keine Kurse vorhanden.</p><?php endif; ?>
                        <?php foreach ($kurse as $kurs): ?>
                            <label class="w3-block"><input type="checkbox" name="kurs_ids[]" value="<?php echo (int)$kurs["kurs_id"]; ?>" <?php echo in_array((string)$kurs["kurs_id"], array_map("strval", $ausgewaehlteKursIds), true) ? "checked" : ""; ?>> <?php echo htmlspecialchars($kurs["name"]); ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button class="w3-button w3-blue" type="submit">Speichern</button>
            <?php if ($bearbeiten): ?><a class="w3-button w3-gray" href="uebersicht.php">Abbrechen</a><?php endif; ?>
        </form>

        <hr>
        <h3>Vorhandene Softwarepakete</h3>
        <?php if (empty($softwarePakete)): ?><p>Noch keine Softwarepakete eingetragen.</p><?php endif; ?>
        <?php foreach ($softwarePakete as $paket): ?>
            <div class="w3-card w3-light-grey w3-padding w3-margin-bottom">
                <h4><?php echo htmlspecialchars($paket["name"]); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($paket["beschreibung"])); ?></p>
                <p><strong>Installiert in Räumen:</strong> <?php echo empty($paket["raeume"]) ? "Noch keinem Raum zugeordnet" : htmlspecialchars($paket["raeume"]); ?></p>
                <a class="w3-button w3-green" href="uebersicht.php?id=<?php echo (int)$paket["software_id"]; ?>">Bearbeiten und zuordnen</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Softwarepaket und seine Zuordnungen löschen?');">
                    <input type="hidden" name="aktion" value="loeschen">
                    <input type="hidden" name="software_id" value="<?php echo (int)$paket["software_id"]; ?>">
                    <button class="w3-button w3-red" type="submit">Löschen</button>
                </form>
            </div>
        <?php endforeach; ?>
        <p><a href="../dashboard.php" class="w3-button w3-gray">Zum Dashboard</a></p>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
