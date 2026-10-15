<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$sql = "
    SELECT b.*, k.name AS kurs_name, r.name AS raum_name, m.vorname, m.nachname
    FROM buchungen b
    JOIN kurse k ON k.kurs_id = b.kurs_id
    JOIN raeume r ON r.raum_id = b.raum_id
    LEFT JOIN mitarbeiter m ON m.mitarbeiter_id = b.mitarbeiter_id
    ORDER BY b.beginn ASC, b.ende ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$buchungen = $stmt->fetchAll();

function darfBuchungLoeschen($pdo, $buchungsId, $mitarbeiterId, $istAdmin) {
    if ($istAdmin == 1) {
        return true;
    }

    $sql = "SELECT 1 FROM buchungen WHERE buchungs_id = :buchungs_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":buchungs_id" => $buchungsId,
        ":mitarbeiter_id" => $mitarbeiterId
    ]);

    return ($stmt->fetch() !== false);
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2>Raumbelegungen</h2>

        <p>
            <a href="neu.php" class="w3-button w3-blue">Neue Buchung anlegen</a>
        </p>

        <?php if (empty($buchungen)) : ?>
            <div class="w3-panel w3-light-grey">
                <p>Es sind noch keine Räume für Kurse gebucht.</p>
            </div>
        <?php else : ?>
            <?php foreach ($buchungen as $buchung): ?>
                <div class="w3-card w3-light-grey w3-margin-bottom w3-padding">
                    <h3><?php echo htmlspecialchars($buchung["kurs_name"]); ?></h3>

                    <p>
                        <strong>Raum:</strong>
                        <?php echo htmlspecialchars($buchung["raum_name"]); ?>
                    </p>

                    <p>
                        <strong>Zeitraum:</strong>
                        <?php echo htmlspecialchars($buchung["beginn"]); ?> bis <?php echo htmlspecialchars($buchung["ende"]); ?>
                    </p>

                    <p>
                        <strong>Erstellt von:</strong>
                        <?php echo htmlspecialchars(trim(($buchung["vorname"] ?? "") . " " . ($buchung["nachname"] ?? ""))); ?>
                    </p>

                    <?php if (darfBuchungLoeschen($pdo, $buchung["buchungs_id"], $_SESSION["mitarbeiter_id"], $_SESSION["ist_admin"])): ?>
                        <a href="loeschen.php?id=<?php echo (int)$buchung["buchungs_id"]; ?>"
                           class="w3-button w3-red"
                           onclick="return confirm('Buchung wirklich löschen?');">
                            Löschen
                        </a>
                    <?php else: ?>
                        <span class="w3-tag w3-gray">Nur ansehen</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
