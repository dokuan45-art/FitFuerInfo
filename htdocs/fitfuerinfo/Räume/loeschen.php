<?php
session_start();

if (!isset($_SESSION["mitarbeiter_id"])) {
    header("Location: ../login.php");
    exit;
}

include("../config/database.php");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: uebersicht.php");
    exit;
}

$sql = "SELECT * FROM raeume WHERE raum_id = :raum_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":raum_id" => $id]);
$raum = $stmt->fetch();

if (!$raum) {
    header("Location: uebersicht.php");
    exit;
}

$istAdmin = ($_SESSION["ist_admin"] == 1);

$sql = "SELECT 1 FROM raum_mitarbeiter WHERE raum_id = :raum_id AND mitarbeiter_id = :mitarbeiter_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":raum_id" => $id,
    ":mitarbeiter_id" => $_SESSION["mitarbeiter_id"]
]);
$istErsteller = ($stmt->fetch() !== false);

if (!$istAdmin && !$istErsteller) {
    die("Du hast keine Berechtigung, diesen Raum zu löschen.");
}

$sql = "DELETE FROM raeume WHERE raum_id = :raum_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":raum_id" => $id]);

header("Location: uebersicht.php");
exit;
