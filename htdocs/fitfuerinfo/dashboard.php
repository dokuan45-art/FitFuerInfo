<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: login.php");
    exit;
}

include("includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding">
        <h2 class="w3-center">
            Willkommen bei FitFürInfo, <?php echo htmlspecialchars($_SESSION["vorname"]); ?>!
        </h2>

        <p class="w3-center">
            Verwaltung von Kursen, Räumen und Raumbelegungen
        </p>

        <div class="w3-row-padding">
            <div class="w3-half w3-margin-bottom">
                <div class="w3-card w3-light-grey w3-padding">
                    <h3>Start</h3>
                    <ul class="w3-ul">
                        <li><a href="index.php">Startseite</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li>
                            <a href="logout.php" class="w3-button w3-red">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="w3-half w3-margin-bottom">
                <div class="w3-card w3-light-grey w3-padding">
                    <h3>Verwaltung</h3>
                    <ul class="w3-ul">
                        <?php if (isset($_SESSION["ist_admin"]) && $_SESSION["ist_admin"] == 1) : ?>
                            <li><a href="admin/mitarbeiter_uebersicht.php">Mitarbeiterübersicht</a></li>
                            <li><a href="admin/mitarbeiter_erstellen.php">Mitarbeiter erstellen</a></li>
                        <?php endif; ?>
                        <li><a href="kurse/uebersicht.php">Kursübersicht</a></li>
                        <li><a href="Räume/uebersicht.php">Raumübersicht</a></li>
                        <li><a href="buchungen/uebersicht.php">Raumbelegungen</a></li>
                        <li><a href="buchungen/neu.php">Raum buchen</a></li>
                        <li><a href="Räume/neu.php">Raum erstellen</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include("includes/footer.php");
?>
