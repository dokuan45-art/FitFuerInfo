<?php

session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

// Admin Only
if (!isset($_SESSION["ist_admin"]) || $_SESSION["ist_admin"] != 1) {
    die("Zugriff verweigert. Nur Administratoren dürfen Räume erstellen.");
}

require_once "../config/database.php";

$fehler = array();
$erfolg = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $arbeitsplaetze = isset($_POST["arbeitsplaetze"]) ? trim($_POST["arbeitsplaetze"]) : "";

    if ($name === "") {
        $fehler[] = "Bitte einen Namen für den Raum angeben.";
    }

    if ($arbeitsplaetze === "" || !ctype_digit($arbeitsplaetze)) {
        $fehler[] = "Bitte eine gültige Anzahl an Arbeitsplätzen angeben.";
    }

    if (count($fehler) === 0) {
        $sql = "INSERT INTO raeume (name, beschreibung, arbeitsplaetze, erstellt_am) VALUES (:name, :beschreibung, :arbeitsplaetze, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ":name" => $name,
            ":beschreibung" => $beschreibung,
            ":arbeitsplaetze" => (int)$arbeitsplaetze
        ));
        $erfolg = true;
    }
}

require_once "../includes/header.php";
?>

<div class="w3-container">
    <h2>Raum erstellen</h2>

    <?php if ($erfolg): ?>
        <div class="w3-panel w3-green">
            <p>Raum wurde erfolgreich angelegt.</p>
    </div>
    <?php endif; ?>

    <?php if (count($fehler) > 0): ?>
        <div class ="w3-panel w3-red">
            <?php foreach ($fehler as $f): ?>
                <p><?php echo htmlspecialchars($f); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="w3-container" method="post" action="">
        <label>Name</label>
        <input class="w3-input w3-border w3-margin-bottom" type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ""; ?>">
        
        <label>Beschreibung</label>
        <textarea class="w3-input w3-border w3-margin-bottom" name="beschreibung"><?php echo isset($beschreibung) ? htmlspecialchars($beschreibung) : "";?></textarea>
        
        <label>Arbeitsplätze</label>
        <input class="w3-input w3-border w3-margin-bottom" type="text" name="arbeitsplaetze" value="<?php echo isset($arbeitsplaetze) ? htmlspecialchars($arbeitsplaetze) : ""; ?>">

        <button class="w3-button w3-blue" type="submit">Raum anlegen</button>
    </form>

    <p><a href="../index.php">Zurück zur Raumübersicht</a></p>
</div>

<?php require_once "../includes/footer.php"; ?>

