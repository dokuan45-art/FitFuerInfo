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

$sql = "SELECT * FROM kurse WHERE kurs_id = :kurs_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":kurs_id" => $id]);
$kurs = $stmt->fetch();

if (!$kurs) {
    header("Location: uebersicht.php");
    exit;
}

$istAdmin = ($_SESSION["ist_admin"] == 1);
$istErsteller = ((int)$kurs["mitarbeiter_id"] === (int)$_SESSION["mitarbeiter_id"]);

if (!$istAdmin && !$istErsteller) {
    die("Du hast keine Berechtigung, diesen Kurs zu löschen.");
}

try {
    $pdo->beginTransaction();

    $sql = "DELETE FROM buchungen WHERE kurs_id = :kurs_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":kurs_id" => $id]);

    $sql = "DELETE FROM kurs_mitarbeiter WHERE kurs_id = :kurs_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":kurs_id" => $id]);

    $sql = "DELETE FROM kurse WHERE kurs_id = :kurs_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":kurs_id" => $id]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Der Kurs konnte nicht gelöscht werden.");
}

header("Location: uebersicht.php");
exit;
