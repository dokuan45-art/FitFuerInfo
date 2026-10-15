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

$sql = "SELECT * FROM buchungen WHERE buchungs_id = :buchungs_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":buchungs_id" => $id]);
$buchung = $stmt->fetch();

if (!$buchung) {
    header("Location: uebersicht.php");
    exit;
}

$istAdmin = ($_SESSION["ist_admin"] == 1);

if (!$istAdmin && (int)$buchung["mitarbeiter_id"] !== (int)$_SESSION["mitarbeiter_id"]) {
    die("Du hast keine Berechtigung, diese Buchung zu löschen.");
}

$sql = "DELETE FROM buchungen WHERE buchungs_id = :buchungs_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":buchungs_id" => $id]);

header("Location: uebersicht.php");
exit;
