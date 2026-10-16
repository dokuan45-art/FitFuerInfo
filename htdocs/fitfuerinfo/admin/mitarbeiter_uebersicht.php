<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"]) || !isset($_SESSION["ist_admin"]) || $_SESSION["ist_admin"] != 1) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$sql = "
    SELECT mitarbeiter_id, benutzername, vorname, nachname, ist_admin, aktiv, erstellt_am, aktivierungscode
    FROM mitarbeiter
    ORDER BY aktiv ASC, nachname ASC, vorname ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$mitarbeiter = $stmt->fetchAll();

$protokoll = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$basisUrl = $protokoll . "://" . $_SERVER["HTTP_HOST"] . rtrim(dirname(dirname($_SERVER["SCRIPT_NAME"])), "/\\");

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2 class="w3-center">Mitarbeiterübersicht</h2>

        <p>
            <a href="mitarbeiter_erstellen.php" class="w3-button w3-blue">
                Mitarbeiter erstellen
            </a>
        </p>

        <?php if (empty($mitarbeiter)) : ?>
            <div class="w3-panel w3-light-grey">
                <p>Es sind noch keine Mitarbeiter vorhanden.</p>
            </div>
        <?php else : ?>
            <div class="w3-responsive">
                <table class="w3-table-all">
                    <thead>
                        <tr class="w3-blue">
                            <th>Name</th>
                            <th>Benutzername</th>
                            <th>Rolle</th>
                            <th>Status</th>
                            <th>Erstellt am</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mitarbeiter as $person) : ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars(trim($person["vorname"] . " " . $person["nachname"])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($person["benutzername"]); ?></td>
                                <td>
                                    <?php echo ((int)$person["ist_admin"] === 1) ? "Administrator" : "Mitarbeiter"; ?>
                                </td>
                                <td>
                                    <?php if ((int)$person["aktiv"] === 1) : ?>
                                        <span class="w3-tag w3-green">Aktiv</span>
                                    <?php else : ?>
                                        <span class="w3-tag w3-orange">Nicht aktiviert</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($person["erstellt_am"]); ?></td>
                            </tr>
                            <?php if ((int)$person["aktiv"] === 0 && !empty($person["aktivierungscode"])) : ?>
                                <?php
                                $aktivierungslink = $basisUrl . "/passwort_setzen.php?aktivierungscode=" . rawurlencode($person["aktivierungscode"]);
                                $linkId = "aktivierungslink-" . (int)$person["mitarbeiter_id"];
                                ?>
                                <tr class="w3-pale-yellow">
                                    <td colspan="5">
                                        <strong>Aktivierungslink:</strong>
                                        <input
                                            id="<?php echo htmlspecialchars($linkId); ?>"
                                            class="w3-input w3-border w3-margin-top"
                                            type="text"
                                            value="<?php echo htmlspecialchars($aktivierungslink); ?>"
                                            readonly
                                        >
                                        <button
                                            type="button"
                                            class="w3-button w3-blue w3-margin-top"
                                            onclick="navigator.clipboard.writeText(document.getElementById('<?php echo htmlspecialchars($linkId); ?>').value).then(function () { this.textContent = 'Kopiert'; }.bind(this)).catch(function () { alert('Der Link konnte nicht kopiert werden.'); });"
                                        >
                                            Link kopieren
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
