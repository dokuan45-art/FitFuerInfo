<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$fehler = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $max_teilnehmer = isset($_POST["max_teilnehmer"]) ? trim($_POST["max_teilnehmer"]) : "";

    if ($name === "") {
        $fehler = "Bitte einen Kursnamen eingeben.";
    } elseif ($max_teilnehmer === "" || !ctype_digit($max_teilnehmer) || (int)$max_teilnehmer <= 0) {
        $fehler = "Bitte eine gültige Anzahl an Teilnehmern eingeben.";
    } else {
        $sql = "INSERT INTO kurse (name, beschreibung, max_teilnehmer, mitarbeiter_id, erstellt_am)
                VALUES (:name, :beschreibung, :max_teilnehmer, :mitarbeiter_id, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $name,
            ":beschreibung" => $beschreibung,
            ":max_teilnehmer" => (int)$max_teilnehmer,
            ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
        ]);

        header("Location: uebersicht.php");
        exit;
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

            <button type="submit" class="w3-button w3-blue">Kurs speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Abbrechen</a>
        </form>
    </div>
</div>

<?php
include("../includes/footer.php");
?>
