<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: login.php");
    exit;
}

include("includes/header.php");
?>

<main class="w3-content w3-padding-32 dashboard-wrap">
    <section class="dashboard-hero w3-card">
        <p class="w3-small" style="margin:0 0 6px;">FITFUERINFO</p>
        <h2>Willkommen, <?php echo htmlspecialchars(trim($_SESSION["vorname"] . " " . $_SESSION["nachname"]), ENT_QUOTES, "UTF-8"); ?>!</h2>
        <p style="margin:0;">Verwaltung von Kursen, Räumen und Raumbelegungen.</p>
    </section>

    <section class="dashboard-grid" aria-label="Dashboard-Bereiche">
        <article class="w3-card w3-white w3-padding dashboard-card">
            <h3 class="w3-medium" style="margin:0;">Start</h3>
            <a class="dashboard-link" href="index.php">Startseite &rarr;</a>
            <a class="dashboard-link" href="buchungen/uebersicht.php">Buchungen &rarr;</a>
            <a class="dashboard-link" href="logout.php">Logout &rarr;</a>
        </article>

        <article class="w3-card w3-white w3-padding dashboard-card">
            <h3 class="w3-medium" style="margin:0;">Verwaltung</h3>
            <a class="dashboard-link" href="kurse/uebersicht.php">Kurse &rarr;</a>
            <a class="dashboard-link" href="Räume/uebersicht.php">Räume &rarr;</a>
            <a class="dashboard-link" href="software/uebersicht.php">Softwareverwaltung &rarr;</a>
        </article>

        <?php if (isset($_SESSION["ist_admin"]) && (int)$_SESSION["ist_admin"] === 1) : ?>
            <article class="w3-card w3-white w3-padding dashboard-card">
                <h3 class="w3-medium" style="margin:0;">Administration</h3>
                <a class="dashboard-link" href="admin/index.php">Admin-Panel &rarr;</a>
                <a class="dashboard-link" href="admin/mitarbeiter_uebersicht.php">Mitarbeiterübersicht &rarr;</a>
                <a class="dashboard-link" href="admin/mitarbeiter_erstellen.php">Mitarbeiter erstellen &rarr;</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php include("includes/footer.php"); ?>
