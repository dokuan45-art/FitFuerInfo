<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$fehler = "";
$selectedKursId = isset($_GET["kurs_id"]) ? (int)$_GET["kurs_id"] : 0;

$sql = "SELECT * FROM kurse ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$kurse = $stmt->fetchAll();

$sql = "SELECT * FROM raeume ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$raeume = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kursId = isset($_POST["kurs_id"]) ? (int)$_POST["kurs_id"] : $selectedKursId;
    $raumId = isset($_POST["raum_id"]) ? (int)$_POST["raum_id"] : 0;
    $beginn = isset($_POST["beginn"]) ? trim($_POST["beginn"]) : "";
    $ende = isset($_POST["ende"]) ? trim($_POST["ende"]) : "";

    if ($kursId <= 0) {
        $fehler = "Bitte einen Kurs auswählen.";
    } elseif ($raumId <= 0) {
        $fehler = "Bitte einen Raum auswählen.";
    } elseif ($beginn === "" || $ende === "") {
        $fehler = "Bitte ein Start- und Enddatum eingeben.";
    } elseif (strtotime($beginn) === false || strtotime($ende) === false) {
        $fehler = "Bitte gültige Datumswerte eingeben.";
    } elseif (strtotime($beginn) < strtotime(date("Y-m-d"))) {
        $fehler = "Das Startdatum darf nicht vor dem heutigen Tag liegen.";
    } elseif (strtotime($ende) < strtotime($beginn)) {
        $fehler = "Das Enddatum darf nicht vor dem Startdatum liegen.";
    } else {
        $kursSql = "SELECT * FROM kurse WHERE kurs_id = :kurs_id LIMIT 1";
        $kursStmt = $pdo->prepare($kursSql);
        $kursStmt->execute([":kurs_id" => $kursId]);
        $kurs = $kursStmt->fetch();

        $raumSql = "SELECT * FROM raeume WHERE raum_id = :raum_id LIMIT 1";
        $raumStmt = $pdo->prepare($raumSql);
        $raumStmt->execute([":raum_id" => $raumId]);
        $raum = $raumStmt->fetch();

        if (!$kurs) {
            $fehler = "Der ausgewählte Kurs wurde nicht gefunden.";
        } elseif (!$raum) {
            $fehler = "Der ausgewählte Raum wurde nicht gefunden.";
        } elseif ((int)$raum["arbeitsplaetze"] < (int)$kurs["max_teilnehmer"]) {
            $fehler = "Der Raum hat zu wenige Arbeitsplätze für diesen Kurs.";
        } else {
            $checkSql = "
                SELECT 1
                FROM buchungen b
                INNER JOIN raeume r ON r.raum_id = b.raum_id
                INNER JOIN kurse k ON k.kurs_id = b.kurs_id
                WHERE b.raum_id = :raum_id
                AND (
                    (b.beginn <= :ende AND b.ende >= :beginn)
                )
                LIMIT 1
            ";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([
                ":raum_id" => $raumId,
                ":beginn" => $beginn,
                ":ende" => $ende
            ]);

            if ($checkStmt->fetch()) {
                $fehler = "Dieser Raum ist für den gewählten Zeitraum bereits belegt.";
            } else {
                $insertSql = "
                    INSERT INTO buchungen (raum_id, kurs_id, beginn, ende, mitarbeiter_id, erstellt_am)
                    VALUES (:raum_id, :kurs_id, :beginn, :ende, :mitarbeiter_id, NOW())
                ";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([
                    ":raum_id" => $raumId,
                    ":kurs_id" => $kursId,
                    ":beginn" => $beginn,
                    ":ende" => $ende,
                    ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
                ]);

                header("Location: uebersicht.php");
                exit;
            }
        }
    }
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Neue Raumbelegung anlegen</h2>

        <?php if ($fehler !== "") : ?>
            <div class="w3-panel w3-red">
                <p><?php echo htmlspecialchars($fehler); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <label><b>Kurs</b></label>
            <select class="w3-select w3-border w3-margin-bottom" name="kurs_id" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($kurse as $kurs): ?>
                    <option value="<?php echo (int)$kurs["kurs_id"]; ?>"
                        <?php
                        $kursSelected = (
                            (isset($_POST["kurs_id"]) && (int)$_POST["kurs_id"] === (int)$kurs["kurs_id"]) ||
                            (!isset($_POST["kurs_id"]) && $selectedKursId === (int)$kurs["kurs_id"])
                        );
                        echo $kursSelected ? "selected" : "";
                        ?>
                    >
                        <?php echo htmlspecialchars($kurs["name"]); ?> (max. <?php echo (int)$kurs["max_teilnehmer"]; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label><b>Raum</b></label>
            <select class="w3-select w3-border w3-margin-bottom" name="raum_id" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($raeume as $raum): ?>
                    <option value="<?php echo (int)$raum["raum_id"]; ?>"
                        <?php echo (isset($_POST["raum_id"]) && (int)$_POST["raum_id"] === (int)$raum["raum_id"]) ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($raum["name"]); ?> (<?php echo (int)$raum["arbeitsplaetze"]; ?> Plätze)
                    </option>
                <?php endforeach; ?>
            </select>

            <label><b>Beginn</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="date" name="beginn" value="<?php echo isset($_POST["beginn"]) ? htmlspecialchars($_POST["beginn"]) : ""; ?>" required>

            <label><b>Ende</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="date" name="ende" value="<?php echo isset($_POST["ende"]) ? htmlspecialchars($_POST["ende"]) : ""; ?>" required>

            <button type="submit" class="w3-button w3-blue">Buchung speichern</button>
            <a href="uebersicht.php" class="w3-button w3-gray">Abbrechen</a>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
