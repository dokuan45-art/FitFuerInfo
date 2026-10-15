<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["ist_admin"]) || $_SESSION["ist_admin"] != 1) {
    die("Zugriff verweigert. Nur Administratoren dürfen neue Räume anlegen.");
}

include("../config/database.php");

$fehler = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $arbeitsplaetze = isset($_POST["arbeitsplaetze"]) ? trim($_POST["arbeitsplaetze"]) : "";

    if ($name === "") {
        $fehler = "Bitte einen Raumnamen eingeben.";
    } elseif ($arbeitsplaetze === "" || !ctype_digit($arbeitsplaetze) || (int)$arbeitsplaetze <= 0) {
        $fehler = "Bitte eine gültige Anzahl an Arbeitsplätzen angeben.";
    } else {
        $sql = "INSERT INTO raeume (name, beschreibung, arbeitsplaetze, erstellt_am)
                VALUES (:name, :beschreibung, :arbeitsplaetze, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $name,
            ":beschreibung" => $beschreibung,
            ":arbeitsplaetze" => (int)$arbeitsplaetze
        ]);

        header("Location: uebersicht.php");
        exit;
    }
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Neuen Raum anlegen</h2>

        <?php if ($fehler !== "") : ?>
            <div class="w3-panel w3-red">
                <p><?php echo htmlspecialchars($fehler); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <label><b>Raumname</b></label>
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

            <label><b>Arbeitsplätze</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="number"
                name="arbeitsplaetze"
                min="1"
                value="<?php echo isset($_POST["arbeitsplaetze"]) ? htmlspecialchars($_POST["arbeitsplaetze"]) : ""; ?>"
                required
            >

            <button type="submit" class="w3-button w3-blue">Raum speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Abbrechen</a>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
