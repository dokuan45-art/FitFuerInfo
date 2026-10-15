<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"]) || !isset($_SESSION["ist_admin"]) || $_SESSION["ist_admin"] != 1) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$meldung = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vorname = trim($_POST["vorname"]);
    $nachname = trim ($_POST["nachname"]);
    $benutzername = trim($_POST["benutzername"]);
    $aktivierungscode = bin2hex(openssl_random_pseudo_bytes(32));

    if ($vorname == "" || $nachname == "" || $benutzername == "") {
        $meldung = "Bitte alle Felder ausfüllen.";
    } else {
        $sql = "INSERT INTO mitarbeiter (benutzername, vorname, nachname, ist_admin, aktiv, erstellt_am, aktivierungscode) VALUES (:benutzername, :vorname, :nachname, 0, 0, NOW(), :aktivierungscode)";        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ":benutzername" => $benutzername,
            ":vorname" => $vorname,
            ":nachname" => $nachname,
            ":aktivierungscode" => $aktivierungscode
        ));
        $meldung = "Mitarbeiter wurde erfolgreich erstellt.";
    }
}

include("../includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-cardd w3-white w3-padding">
        <h2 class="w3-center">Mitarbeiter erstellen</h2>

        <?php if ($meldung != "") { ?>
            <div class="w3-panel w3-green">
                <p><?php echo htmlspecialchars($meldung); ?></p>
            </div>

        <?php } ?>

        <form method="post" action="mitarbeiter_erstellen.php">
            <label><b>Vorname</b></label>

            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="vorname"
                required
            >

            <label><b>Nachname</b></label>
            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="nachname"
                required
            >

            <label><b>Benutzername</b></label>

            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="benutzername"
                required
            >

            <button
                class="w3-button w3-blue w3-block"
                type="submit"
            >
                Mitarbeiter erstellen
            </button>
        </form>
    </div>
</div>

<?php 
include("../includes/footer.php");
?>