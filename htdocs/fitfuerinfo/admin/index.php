<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["ist_admin"]) || (int)$_SESSION["ist_admin"] !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

require_once("../config/database.php");

function anzahlDatensaetze(PDO $pdo, string $tabelle): int
{
    // Tabellenname wird ausschließlich aus den fest definierten Aufrufen übergeben.
    return (int)$pdo->query("SELECT COUNT(*) FROM " . $tabelle)->fetchColumn();
}

$kennzahlen = [
    ["Mitarbeiter", anzahlDatensaetze($pdo, "mitarbeiter"), "mitarbeiter_uebersicht.php", "Konten verwalten"],
    ["Aktive Mitarbeiter", (int)$pdo->query("SELECT COUNT(*) FROM mitarbeiter WHERE aktiv = 1")->fetchColumn(), "mitarbeiter_uebersicht.php", "Aktivierte Konten"],
    ["Kurse", anzahlDatensaetze($pdo, "kurse"), "../kurse/uebersicht.php", "Kurse ansehen"],
    ["Räume", anzahlDatensaetze($pdo, "raeume"), "../Räume/uebersicht.php", "Räume ansehen"],
    ["Buchungen", anzahlDatensaetze($pdo, "buchungen"), "../buchungen/uebersicht.php", "Belegungen ansehen"],
    ["Softwarepakete", anzahlDatensaetze($pdo, "software"), "../software/uebersicht.php", "Software verwalten"],
];

include("../includes/header.php");
?>

<main class="w3-content w3-padding-32 admin-wrap">
    <section class="admin-hero w3-card">
        <p class="w3-small" style="margin:0 0 6px;">ADMINISTRATION</p>
        <h2>Admin-Panel</h2>
        <p style="margin:0;">Zentrale Übersicht und Schnellzugriffe für FitFuerInfo.</p>
    </section>

    <section class="admin-grid" aria-label="Systemübersicht">
        <?php foreach ($kennzahlen as $kennzahl) : ?>
            <article class="w3-card w3-white w3-padding admin-card">
                <h3 class="w3-medium" style="margin:0;"><?php echo htmlspecialchars($kennzahl[0], ENT_QUOTES, "UTF-8"); ?></h3>
                <p class="admin-number"><?php echo (int)$kennzahl[1]; ?></p>
                <a href="<?php echo htmlspecialchars($kennzahl[2], ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($kennzahl[3], ENT_QUOTES, "UTF-8"); ?> &rarr;</a>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="w3-card w3-white w3-padding w3-margin-bottom">
        <h3>Verwaltung</h3>
        <div class="admin-actions">
            <a class="w3-button w3-blue" href="mitarbeiter_uebersicht.php">Mitarbeiter verwalten</a>
            <a class="w3-button w3-blue-grey" href="mitarbeiter_erstellen.php">Mitarbeiter erstellen</a>
            <a class="w3-button w3-light-grey" href="../dashboard.php">Zurück zum Dashboard</a>
        </div>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
