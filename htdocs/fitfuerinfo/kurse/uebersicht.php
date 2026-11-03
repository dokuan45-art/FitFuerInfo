<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$sql = "
    SELECT k.*
    FROM kurse k
    ORDER BY k.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$kurse = $stmt->fetchAll();

function darfKursBearbeiten($pdo, $kursId, $mitarbeiterId, $istAdmin) {
    if ($istAdmin == 1) {
        return true;
    }

    $sql = "SELECT 1 FROM kurse WHERE kurs_id = :kurs_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":kurs_id" => $kursId,
        ":mitarbeiter_id" => $mitarbeiterId
    ]);

    if ($stmt->fetch()) {
        return true;
    }

    $sql = "SELECT 1 FROM kurs_mitarbeiter
            WHERE kurs_id = :kurs_id
            AND mitarbeiter_id = :mitarbeiter_id
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":kurs_id" => $kursId,
        ":mitarbeiter_id" => $mitarbeiterId
    ]);

    if ($stmt->fetch()) {
        return true;
    }

    return false;
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Kursübersicht</h2>

        <p>
            <a href="neu.php" class="w3-button w3-blue">Neuen Kurs anlegen</a>
        </p>

        <?php foreach ($kurse as $kurs): ?>
            <div class="w3-card w3-light-grey w3-margin-bottom w3-padding">
                <h3><?php echo htmlspecialchars($kurs["name"]); ?></h3>

                <p><?php echo nl2br(htmlspecialchars($kurs["beschreibung"])); ?></p>

                <p>
                    <strong>Max. Teilnehmer:</strong>
                    <?php echo htmlspecialchars($kurs["max_teilnehmer"]); ?>
                </p>

                <?php
                $softwareStmt = $pdo->prepare("SELECT s.name FROM kurs_software ks JOIN software s ON s.software_id = ks.software_id WHERE ks.kurs_id = :kurs_id ORDER BY s.name ASC");
                $softwareStmt->execute(array(":kurs_id" => $kurs["kurs_id"]));
                $kursSoftware = $softwareStmt->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <p><strong>Benötigte Software:</strong> <?php echo empty($kursSoftware) ? "Keine angegeben" : htmlspecialchars(implode(", ", $kursSoftware)); ?></p>

                <?php
                $buchungsSql = "
                    SELECT b.*, r.name AS raum_name
                    FROM buchungen b
                    JOIN raeume r ON r.raum_id = b.raum_id
                    WHERE b.kurs_id = :kurs_id
                    ORDER BY b.beginn ASC
                ";
                $buchungsStmt = $pdo->prepare($buchungsSql);
                $buchungsStmt->execute([":kurs_id" => $kurs["kurs_id"]]);
                $buchungen = $buchungsStmt->fetchAll();
                ?>

                <p>
                    <strong>Gebuchte Räume:</strong>
                    <?php if (empty($buchungen)): ?>
                       <span>noch keine Raumbelegung</span>
                    <?php else: ?>
                       <?php foreach ($buchungen as $index => $buchung): ?>
                           <?php if ($index > 0): ?>
                               <span>, </span>
                           <?php endif; ?>
                           <span><?php echo htmlspecialchars($buchung["raum_name"]); ?> (<?php echo htmlspecialchars($buchung["beginn"]); ?> - <?php echo htmlspecialchars($buchung["ende"]); ?>)</span>
                       <?php endforeach; ?>
                    <?php endif; ?>
                </p>

                <p>
                    <a href="../buchungen/neu.php?kurs_id=<?php echo (int)$kurs["kurs_id"]; ?>" class="w3-button w3-cyan">Raum buchen</a>
                </p>

                <?php
                $darfBearbeiten = darfKursBearbeiten(
                    $pdo,
                    $kurs["kurs_id"],
                    $_SESSION["mitarbeiter_id"],
                    $_SESSION["ist_admin"]
                );
                ?>

                <?php if ($darfBearbeiten): ?>
                    <a href="bearbeiten.php?id=<?php echo (int)$kurs["kurs_id"]; ?>"
                       class="w3-button w3-green">
                       Bearbeiten
                    </a>

                    <a href="loeschen.php?id=<?php echo (int)$kurs["kurs_id"]; ?>"
                       class="w3-button w3-red"
                       onclick="return confirm('Kurs wirklich löschen?');">
                       Löschen
                    </a>
                <?php else: ?>
                    <span class="w3-tag w3-gray">Nur ansehen</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
include("../includes/footer.php");
?>
