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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $beschreibung = isset($_POST["beschreibung"]) ? trim($_POST["beschreibung"]) : "";
    $maxTeilnehmer = isset($_POST["max_teilnehmer"]) ? trim($_POST["max_teilnehmer"]) : "";

    if ($name === "") {
        $fehler = "Bitte einen Kursnamen eingeben.";
    } elseif (!ctype_digit($maxTeilnehmer) || (int)$maxTeilnehmer <= 0) {
        $fehler = "Bitte eine gültige Anzahl an Teilnehmern eingeben.";
    } else {
        $sql = "UPDATE kurse
                SET name = :name,
                    beschreibung = :beschreibung,
                    max_teilnehmer = :max_teilnehmer
                WHERE kurs_id = :kurs_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":name" => $name,
            ":beschreibung" => $beschreibung,
            ":max_teilnehmer" => (int)$maxTeilnehmer,
            ":kurs_id" => $id
        ]);

        header("Location: uebersicht.php");
        exit;
    }
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

        <form method="post">
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

            <button type="submit" class="w3-button w3-blue">Änderungen speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Zurück</a>
        </form>
    </div>
</div>

<?php
include("../includes/footer.php");
?>