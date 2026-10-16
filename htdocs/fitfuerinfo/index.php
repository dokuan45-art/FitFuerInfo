<?php
session_start();
include("includes/header.php");
?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding w3-center">
        <h2> Willkommen bei FitFürInfo</h2>
        
        <p>
            Verwaltung von Kursen,Räumen und Raumbelegungen
        </p>

        <p>
            Über dieses System können Mitarbeiter Kurse verwalten, 
            Räume einsehen und Raumbelegungen planen.
        </p>

        <?php if (isset($_SESSION["mitarbeiter_id"])) : ?>
            <a href="dashboard.php" class="w3-button w3-blue w3-round">
                Zum Dashboard
            </a>
        <?php else : ?>
            <a href="login.php" class="w3-button w3-blue w3-round">
                Zum Login
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
include("includes/footer.php");
?>
