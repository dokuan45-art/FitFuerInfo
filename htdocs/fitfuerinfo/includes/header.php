<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FitFuerInfo</title>

    <link rel="stylesheet" href="/fitfuerinfo/w3.css">
</head>

<body class="w3-light-grey">

<?php
$scriptDir = dirname($_SERVER["SCRIPT_NAME"]);
if ($scriptDir === "/" || $scriptDir === "/fitfuerinfo") {
    $dashboardUrl = "dashboard.php";
} else {
    $dashboardUrl = rtrim($scriptDir, "/") . "/../dashboard.php";
}
?>

<header class="w3-blue w3-center w3-padding-32">
    <h1>
        <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="w3-text-white" style="text-decoration:none;">
            FitFuerInfo
        </a>
    </h1>
    <p>Verwaltung von Kursen, Räumen und Buchungen</p>
</header>