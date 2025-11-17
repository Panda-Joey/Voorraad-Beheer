<?php
session_start(); 

include 'database.php';


if (!isset($_SESSION['iduser'])) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idproduct = $_POST['idproduct'];
    $inkoop = $_POST['inkoop'];
    $verkoop = $_POST['verkoopprijs'];

    // productprijzen updaten
    $stmt = $conn->prepare("UPDATE product SET inkoopwaarde = ?, verkoopwaarde = ? WHERE idproduct = ?");
    $stmt->bind_param("ddi", $inkoop, $verkoop, $idproduct);
    $stmt->execute();

    // waarde voorraad updaten
    $stmt2 = $conn->prepare("UPDATE voorraad SET waarde_voorraad = aantal * ? WHERE idproduct = ?");
    $stmt2->bind_param("di", $inkoop, $idproduct);
    $stmt2->execute();

    header("Location: voorraad.php");
    exit;
}
?>

