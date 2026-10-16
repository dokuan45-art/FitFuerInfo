<?php
session_start();
include("config/database.php");

function ist_gueltiges_passwort($passwort) {
    return preg_match('/^(?=.*[a-z])(?=.*\d).{4,}$/', $passwort) === 1;
}

$meldung = "";
$fehler = "";

$aktivierungscode = isset($_GET["aktivierungscode"]) ? trim($_GET["aktivierungscode"]) : "";

//Prüfung

$mitarbeiter = null;

if($aktivierungscode != "") {
    $sql = "SELECT * FROM mitarbeiter WHERE aktivierungscode = :aktivierungscode AND aktiv = 0 LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":aktivierungscode"=> $aktivierungscode
    ]);

    $mitarbeiter = $stmt->fetch();
    }

    if($_SERVER["REQUEST_METHOD"]=="POST"){

        $aktivierungscode = isset($_POST["aktivierungscode"])? trim($_POST["aktivierungscode"]): "";
        $passwort = isset($_POST["passwort"])? $_POST["passwort"]: "";
        $passwort_wiederholen = isset($_POST["passwort_wiederholen"]) ? $_POST["passwort_wiederholen"] : "";

        if($aktivierungscode == "") {
            $fehler = "Ungültigere Aktivierungscode.";
        } else {
            
            $sql = "SELECT * FROM mitarbeiter WHERE aktivierungscode =:aktivierungscode AND aktiv = 0 LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":aktivierungscode" => $aktivierungscode
            ]);

            $mitarbeiter = $stmt->fetch();

            if(!$mitarbeiter) {
                $fehler = "Der Aktivierungscode ist ungültig oder wurde bereits verwendet.";
            } elseif ($passwort == "" || $passwort_wiederholen == "") {
                $fehler = "Bitte beide Passwortfelder ausfüllen";
            } elseif ($passwort !== $passwort_wiederholen) {
                $fehler = "Die Passwörter stimmen nicht überein.";
            } elseif (!ist_gueltiges_passwort($passwort)) {
                $fehler = "Das Passwort muss mindestens 4 Zeichen lang sein, mindestens einen Kleinbuchstaben und eine Zahl enthalten.";
            } else {
                $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
                $sql = "UPDATE mitarbeiter SET passwort = :passwort, aktiv = 1, aktivierungscode = NULL WHERE mitarbeiter_id = :mitarbeiter_id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":passwort" => $passwort_hash,
                ":mitarbeiter_id" => $mitarbeiter["mitarbeiter_id"]
            ]);

            $meldung = "Passwort wurde erfolgreich festgelegt. Du kannst dich jetzt anmelden.";
            $mitarbeiter = null;
            $aktivierungscode = "";
        }
    }
}
include("includes/header.php");
?>

<div class="w3-content w3-padding-32">

<div class="w3-card w3-white w3-padding-32">

    <h2 class="w3-center">Passwort festlegen</h2>

    <?php if ($fehler != "") { ?>

        <div class="w3-panel w3-red">
            <p>
                <?php echo htmlspecialchars($fehler); ?>
            </p>
        </div>

    <?php } ?>

    <?php if ($meldung != "") { ?>

        <div class="w3-panel w3-green">
            <p>
                <?php echo htmlspecialchars($meldung); ?>
            </p>

            <p>
                <a href="login.php" class="w3-button w3-blue">
                    Zum Login
                </a>
            </p>
        </div>

    <?php } ?>

    <?php if ($mitarbeiter && $aktivierungscode != "") { ?>

        <p>
            Hallo
            <b>
                <?php echo htmlspecialchars($mitarbeiter["vorname"]); ?>
                <?php echo htmlspecialchars($mitarbeiter["nachname"]); ?>
            </b>
        </p>

        <p>
            Bitte lege jetzt dein persönliches Passwort fest.
        </p>

        <form method="post" action="passwort_setzen.php">

            <input
                type="hidden"
                name="aktivierungscode"
                value="<?php echo htmlspecialchars($aktivierungscode); ?>"
            >

            <label>
                <b>Neues Passwort</b>
            </label>

            <input
                class="w3-input w3-border w3-margin-bottom"
                type="password"
                name="passwort"
                minlength="4"
                pattern="(?=.*[a-z])(?=.*\d).{4,}"
                title="Mindestens 4 Zeichen, ein Kleinbuchstabe und eine Zahl."
                required
            >

            <label>
                <b>Passwort wiederholen</b>
            </label>

            <input
                class="w3-input w3-border w3-margin-bottom"
                type="password"
                name="passwort_wiederholen"
                minlength="4"
                pattern="(?=.*[a-z])(?=.*\d).{4,}"
                title="Mindestens 4 Zeichen, ein Kleinbuchstabe und eine Zahl."
                required
            >

            <button
                class="w3-button w3-blue w3-block"
                type="submit"
            >
                Passwort festlegen
            </button>

        </form>

    <?php} elseif ($meldung == "") { ?>

        <div class="w3-panel w3-red">
            <p>
                Der Aktivierungslink ist ungültig oder wurde bereits verwendet.
            </p>
        </div>

    <?php } ?>

</div>

</div>

<?php
include("includes/footer.php");
?>
