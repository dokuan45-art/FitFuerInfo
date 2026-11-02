<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"]) || !isset($_SESSION["ist_admin"]) || $_SESSION["ist_admin"] != 1) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$meldung = "";
$fehler = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aktion = isset($_POST["aktion"]) ? trim($_POST["aktion"]) : "";
    $zielId = isset($_POST["mitarbeiter_id"]) ? (int)$_POST["mitarbeiter_id"] : 0;

    if (($aktion !== "toggle_aktiv" && $aktion !== "toggle_admin" && $aktion !== "loeschen") || $zielId <= 0) {
        $fehler = "Ungültige Aktion.";
    } else {
        $sql = "SELECT aktiv, ist_admin FROM mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":mitarbeiter_id" => $zielId]);
        $zielMitarbeiter = $stmt->fetch();

        if (!$zielMitarbeiter) {
            $fehler = "Mitarbeiter wurde nicht gefunden.";
        } elseif ($zielId === (int)$_SESSION["mitarbeiter_id"]) {
            $fehler = "Der eigene Account kann hier nicht geändert werden.";
        } else {
            if ($aktion === "toggle_aktiv") {
                $neuerStatus = ((int)$zielMitarbeiter["aktiv"] === 1) ? 0 : 1;
                $sql = "UPDATE mitarbeiter SET aktiv = :aktiv WHERE mitarbeiter_id = :mitarbeiter_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ":aktiv" => $neuerStatus,
                    ":mitarbeiter_id" => $zielId
                ]);

                $meldung = ($neuerStatus === 1)
                    ? "Mitarbeiter wurde aktiviert."
                    : "Mitarbeiter wurde deaktiviert.";
            }

            if ($aktion === "toggle_admin") {
                $neuerAdminStatus = ((int)$zielMitarbeiter["ist_admin"] === 1) ? 0 : 1;

                if ($neuerAdminStatus === 0) {
                    $sql = "SELECT COUNT(*) AS anzahl FROM mitarbeiter WHERE ist_admin = 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $anzahlAdmins = (int)$stmt->fetch()["anzahl"];

                    if ($anzahlAdmins <= 1) {
                        $fehler = "Mindestens ein Administrator muss im System bleiben.";
                    }
                }

                if ($fehler === "") {
                    $sql = "UPDATE mitarbeiter SET ist_admin = :ist_admin WHERE mitarbeiter_id = :mitarbeiter_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ":ist_admin" => $neuerAdminStatus,
                        ":mitarbeiter_id" => $zielId
                    ]);

                    $meldung = ($neuerAdminStatus === 1)
                        ? "Admin-Rechte wurden vergeben."
                        : "Admin-Rechte wurden entzogen.";
                }
            }

            if ($aktion === "loeschen") {
                if ((int)$zielMitarbeiter["ist_admin"] === 1) {
                    $sql = "SELECT COUNT(*) AS anzahl FROM mitarbeiter WHERE ist_admin = 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $anzahlAdmins = (int)$stmt->fetch()["anzahl"];

                    if ($anzahlAdmins <= 1) {
                        $fehler = "Der letzte Administrator kann nicht gelöscht werden.";
                    }
                }

                if ($fehler === "") {
                    $sql = "SELECT COUNT(*) AS anzahl FROM kurse WHERE mitarbeiter_id = :mitarbeiter_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([":mitarbeiter_id" => $zielId]);
                    $anzahlKurse = (int)$stmt->fetch()["anzahl"];

                    $sql = "SELECT COUNT(*) AS anzahl FROM buchungen WHERE mitarbeiter_id = :mitarbeiter_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([":mitarbeiter_id" => $zielId]);
                    $anzahlBuchungen = (int)$stmt->fetch()["anzahl"];

                    if ($anzahlKurse > 0 || $anzahlBuchungen > 0) {
                        $fehler = "Mitarbeiter kann nicht gelöscht werden. Offene Zuordnungen: "
                            . $anzahlKurse . " Kurs(e), "
                            . $anzahlBuchungen . " Buchung(en).";
                    }
                }

                if ($fehler === "") {
                    try {
                        $pdo->beginTransaction();

                        $sql = "DELETE FROM kurs_mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([":mitarbeiter_id" => $zielId]);

                        $sql = "DELETE FROM raum_mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([":mitarbeiter_id" => $zielId]);

                        $sql = "DELETE FROM mitarbeiter WHERE mitarbeiter_id = :mitarbeiter_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([":mitarbeiter_id" => $zielId]);

                        $pdo->commit();
                        $meldung = "Mitarbeiter wurde gelöscht.";
                    } catch (PDOException $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        $fehler = "Mitarbeiter konnte nicht gelöscht werden.";
                    }
                }
            }
        }
    }
}

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

        <?php if ($meldung !== "") : ?>
            <div class="w3-panel w3-green">
                <p><?php echo htmlspecialchars($meldung); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($fehler !== "") : ?>
            <div class="w3-panel w3-red">
                <p><?php echo htmlspecialchars($fehler); ?></p>
            </div>
        <?php endif; ?>

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
                            <th>Aktion</th>
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
                                <td>
                                    <?php if ((int)$person["mitarbeiter_id"] === (int)$_SESSION["mitarbeiter_id"]) : ?>
                                        <span class="w3-tag w3-gray">Eigenes Konto</span>
                                    <?php else : ?>
                                        <form method="post" action="mitarbeiter_uebersicht.php" style="margin:0;">
                                            <input type="hidden" name="aktion" value="toggle_aktiv">
                                            <input type="hidden" name="mitarbeiter_id" value="<?php echo (int)$person["mitarbeiter_id"]; ?>">
                                            <?php if ((int)$person["aktiv"] === 1) : ?>
                                                <button type="submit" class="w3-button w3-orange w3-small" onclick="return confirm('Mitarbeiter wirklich deaktivieren?');">
                                                    Deaktivieren
                                                </button>
                                            <?php else : ?>
                                                <button type="submit" class="w3-button w3-green w3-small">
                                                    Aktivieren
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form method="post" action="mitarbeiter_uebersicht.php" class="w3-margin-top" style="margin-bottom:0;">
                                            <input type="hidden" name="aktion" value="toggle_admin">
                                            <input type="hidden" name="mitarbeiter_id" value="<?php echo (int)$person["mitarbeiter_id"]; ?>">
                                            <?php if ((int)$person["ist_admin"] === 1) : ?>
                                                <button type="submit" class="w3-button w3-red w3-small" onclick="return confirm('Admin-Rechte wirklich entziehen?');">
                                                    Admin entziehen
                                                </button>
                                            <?php else : ?>
                                                <button type="submit" class="w3-button w3-indigo w3-small">
                                                    Admin geben
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form method="post" action="mitarbeiter_uebersicht.php" class="w3-margin-top" style="margin-bottom:0;">
                                            <input type="hidden" name="aktion" value="loeschen">
                                            <input type="hidden" name="mitarbeiter_id" value="<?php echo (int)$person["mitarbeiter_id"]; ?>">
                                            <button type="submit" class="w3-button w3-black w3-small" onclick="return confirm('Mitarbeiter wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.');">
                                                Löschen
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ((int)$person["aktiv"] === 0 && !empty($person["aktivierungscode"])) : ?>
                                <?php
                                $aktivierungslink = $basisUrl . "/passwort_setzen.php?aktivierungscode=" . rawurlencode($person["aktivierungscode"]);
                                $linkId = "aktivierungslink-" . (int)$person["mitarbeiter_id"];
                                ?>
                                <tr class="w3-pale-yellow">
                                    <td colspan="6">
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
