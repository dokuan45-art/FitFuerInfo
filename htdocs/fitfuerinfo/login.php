<?php
session_start();
include("config/database.php");

$fehler ="";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $benutzername = isset($_POST["benutzername"]) ? trim($_POST["benutzername"]) : "";
    $passwort = isset($_POST["passwort"]) ? $_POST["passwort"] : "";

    if ($benutzername == "") {

        $fehler = "Bitte Benutzername eingeben";
    } else {
        $sql = "SELECT * FROM mitarbeiter WHERE benutzername = :benutzername AND aktiv = 1 LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(":benutzername" => $benutzername));

        $mitarbeiter = $stmt->fetch();
        $ist_root_login = ($benutzername === $username && $passwort === $password);

        if ($mitarbeiter && !empty($mitarbeiter["passwort"]) && password_verify($passwort, $mitarbeiter["passwort"])) {

            session_regenerate_id(true);

            $_SESSION["mitarbeiter_id"] = $mitarbeiter["mitarbeiter_id"];
            $_SESSION["benutzername"] = $mitarbeiter["benutzername"];
            $_SESSION["vorname"] = $mitarbeiter["vorname"];
            $_SESSION["nachname"] = $mitarbeiter["nachname"];
            $_SESSION["ist_admin"] = $mitarbeiter["ist_admin"];

            header("Location: dashboard.php");
            exit;

        } elseif ($ist_root_login) {

            session_regenerate_id(true);

            $_SESSION["mitarbeiter_id"] = 0;
            $_SESSION["benutzername"] = $benutzername;
            $_SESSION["vorname"] = "Root";
            $_SESSION["nachname"] = "User";
            $_SESSION["ist_admin"] = 1;

            header("Location: dashboard.php");
            exit;

        } else {
            $fehler = "Benutzername oder Passwort ist falsch.";
        }
    }
}

include("includes/header.php");
?>

<?php if ($fehler !== "") : ?>
    <div class="w3-panel w3-pale-red w3-border-red">
        <p><?php echo htmlspecialchars($fehler); ?></p>
    </div>
<?php endif; ?>

<div class="w3-content w3-padding-32">
    <div class="w3-card w3-white w3-padding-32">
        <h2 class="w3-center">Anmeldung</h2>
        <form method="post" action="login.php">

            <label><b>Benutzername</b></label>

            <input
                class="w3-input w3-border w3-margin-bottom"
                type="text"
                name="benutzername"
                required
            >

            <label><b>Passwort</b></label>


            <input
                class="w3-input w3-border w3-margin-bottom"
                type="password"
                name="passwort"
                autocomplete="current-password"
            >

            <button
                class="w3-button w3-blue w3-block"
                type="submit"
            >
                Anmelden
            </button>

        </form>
    </div>
</div>

<?php
include("includes/footer.php");
?>