<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$sql = "SELECT * FROM raeume ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$raeume = $stmt->fetchAll();

function darfRaumBearbeiten($pdo, $raumId, $mitarbeiterId, $istAdmin) {
    if ($istAdmin == 1) {
        return true;
    }

    $sql = "SELECT 1 FROM raum_mitarbeiter
            WHERE raum_id = :raum_id
            AND mitarbeiter_id = :mitarbeiter_id
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":raum_id" => $raumId,
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
        <h2>Raumübersicht</h2>

        <p>
            <a href="neu.php" class="w3-button w3-blue">Neuen Raum anlegen</a>
            <?php if ((int)$_SESSION["ist_admin"] === 1): ?>
                <a href="../software/uebersicht.php" class="w3-button w3-indigo">Software verwalten</a>
            <?php endif; ?>
        </p>

        <?php foreach ($raeume as $raum): ?>
            <div class="w3-card w3-light-grey w3-margin-bottom w3-padding">
                <h3><?php echo htmlspecialchars($raum["name"]); ?></h3>

                <p><?php echo nl2br(htmlspecialchars($raum["beschreibung"])); ?></p>

                <p>
                    <strong>Arbeitsplätze:</strong>
                    <?php echo htmlspecialchars($raum["arbeitsplaetze"]); ?>
                </p>

                <?php
                $softwareStmt = $pdo->prepare("SELECT s.name FROM raum_software rs JOIN software s ON s.software_id = rs.software_id WHERE rs.raum_id = :raum_id ORDER BY s.name ASC");
                $softwareStmt->execute(array(":raum_id" => $raum["raum_id"]));
                $raumSoftware = $softwareStmt->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <p><strong>Installierte Software:</strong> <?php echo empty($raumSoftware) ? "Keine angegeben" : htmlspecialchars(implode(", ", $raumSoftware)); ?></p>

                <?php
                $darfBearbeiten = darfRaumBearbeiten(
                    $pdo,
                    $raum["raum_id"],
                    $_SESSION["mitarbeiter_id"],
                    $_SESSION["ist_admin"]
                );
                ?>

                <?php if ($darfBearbeiten): ?>
                    <a href="bearbeiten.php?id=<?php echo (int)$raum["raum_id"]; ?>" class="w3-button w3-green">
                        Bearbeiten
                    </a>

                    <?php if ((int)$_SESSION["ist_admin"] === 1): ?>
                        <a href="../software/uebersicht.php?raum_id=<?php echo (int)$raum["raum_id"]; ?>" class="w3-button w3-indigo">
                            Software zuordnen
                        </a>
                    <?php endif; ?>

                    <a href="loeschen.php?id=<?php echo (int)$raum["raum_id"]; ?>" class="w3-button w3-red" onclick="return confirm('Raum wirklich löschen?');">
                        Löschen
                    </a>
                <?php else: ?>
                    <span class="w3-tag w3-gray">Nur ansehen</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
