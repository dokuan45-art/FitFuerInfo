<?php
session_start();
if (!isset($_SESSION["mitarbeiter_id"])) { header("Location: ../login.php"); exit; }
if (!isset($_SESSION["ist_admin"]) || (int)$_SESSION["ist_admin"] !== 1) { die("Zugriff verweigert."); }
require_once "../config/database.php";

$fehler = "";
$stmt = $pdo->query("SELECT software_id, name FROM software ORDER BY name ASC");
$softwarePakete = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $arbeitsplaetze = isset($_POST["arbeitsplaetze"]) ? trim($_POST["arbeitsplaetze"]) : "";
    $softwareIds = isset($_POST["software_ids"]) && is_array($_POST["software_ids"]) ? $_POST["software_ids"] : array();
    $softwareIds = array_values(array_unique(array_filter(array_map("intval", $softwareIds), function ($id) { return $id > 0; })));

    if ($name === "") { $fehler = "Bitte einen Raumnamen eingeben."; }
    elseif ($arbeitsplaetze === "" || !ctype_digit($arbeitsplaetze) || (int)$arbeitsplaetze <= 0) { $fehler = "Bitte eine gültige Anzahl an Arbeitsplätzen eingeben."; }
    else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO raeume (name, beschreibung, arbeitsplaetze, erstellt_am) VALUES (:name, :beschreibung, :arbeitsplaetze, NOW())");
            $stmt->execute(array(":name" => $name, ":beschreibung" => $beschreibung, ":arbeitsplaetze" => (int)$arbeitsplaetze));
            $raumId = (int)$pdo->lastInsertId();
            if (!empty($softwareIds)) {
                $stmt = $pdo->prepare("INSERT INTO raum_software (raum_id, software_id) VALUES (:raum_id, :software_id)");
                foreach ($softwareIds as $softwareId) { $stmt->execute(array(":raum_id" => $raumId, ":software_id" => $softwareId)); }
            }
            $pdo->commit();
            header("Location: uebersicht.php"); exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $fehler = "Raum konnte nicht gespeichert werden. Bitte prüfen Sie die Softwareauswahl.";
        }
    }
}
include("../includes/header.php");
?>
<div class="w3-content w3-padding-32"><div class="w3-card w3-white w3-padding">
    <h2>Neuen Raum anlegen</h2>
    <?php if ($fehler !== ""): ?><div class="w3-panel w3-red"><p><?php echo htmlspecialchars($fehler); ?></p></div><?php endif; ?>
    <form method="post">
        <label><b>Raumname</b></label><input class="w3-input w3-border w3-margin-bottom" type="text" name="name" value="<?php echo isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""; ?>" required>
        <label><b>Beschreibung</b></label><textarea class="w3-input w3-border w3-margin-bottom" name="beschreibung" rows="5"><?php echo isset($_POST["beschreibung"]) ? htmlspecialchars($_POST["beschreibung"]) : ""; ?></textarea>
        <label><b>Arbeitsplätze</b></label><input class="w3-input w3-border w3-margin-bottom" type="number" name="arbeitsplaetze" min="1" value="<?php echo isset($_POST["arbeitsplaetze"]) ? htmlspecialchars($_POST["arbeitsplaetze"]) : ""; ?>" required>
        <label><b>Installierte Softwarepakete</b></label>
        <select class="w3-select w3-border w3-margin-bottom" name="software_ids[]" multiple size="6">
            <?php foreach ($softwarePakete as $paket): ?><option value="<?php echo (int)$paket["software_id"]; ?>" <?php echo (isset($_POST["software_ids"]) && in_array((string)$paket["software_id"], $_POST["software_ids"], true)) ? "selected" : ""; ?>><?php echo htmlspecialchars($paket["name"]); ?></option><?php endforeach; ?>
        </select><p class="w3-small">Mehrfachauswahl mit Strg/Command.</p>
        <button type="submit" class="w3-button w3-blue">Raum speichern</button> <a href="uebersicht.php" class="w3-button w3-gray">Abbrechen</a>
    </form>
</div></div>
<?php include("../includes/footer.php"); ?>
