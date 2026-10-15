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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $arbeitsplaetze = isset($_POST["arbeitsplaetze"]) ? trim($_POST["arbeitsplaetze"]) : "";

    if ($name === "") {
        $fehler = "Bitte einen Raumnamen eingeben.";
    } elseif ($arbeitsplaetze === "" || !ctype_digit($arbeitsplaetze) || (int)$arbeitsplaetze <= 0) {
        $fehler = "Bitte eine gültige Anzahl an Arbeitsplätzen angeben.";
    } else {
        $sql = "UPDATE raeume
                SET name = :name,
                    beschreibung = :beschreibung,
                    arbeitsplaetze = :arbeitsplaetze
                WHERE raum_id = :raum_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $name,
            ":beschreibung" => $beschreibung,
            ":arbeitsplaetze" => (int)$arbeitsplaetze,
            ":raum_id" => $id
        ]);

        header("Location: uebersicht.php");
        exit;
    }
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

        <form method="post">
            <label><b>Raumname</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($raum["name"]); ?>"
                required
            >

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
    </div>
</div>

<?php include("../includes/footer.php"); ?>
