<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idproduct'])) {
    $idproduct = intval($_POST['idproduct']);

    // Verwijder bij product has bestelling
    $stmt0 = $conn->prepare("DELETE FROM product_has_bestelling WHERE idproduct = ?");
    $stmt0->bind_param("i", $idproduct);
    $stmt0->execute();
    $stmt0->close();

    // Verwijder bij voorraad
    $stmt1 = $conn->prepare("DELETE FROM voorraad WHERE idproduct = ?");
    $stmt1->bind_param("i", $idproduct);
    $stmt1->execute();
    $stmt1->close();

    // Verwijder bij product
    $stmt2 = $conn->prepare("DELETE FROM product WHERE idproduct = ?");
    $stmt2->bind_param("i", $idproduct);
    $stmt2->execute();
    $stmt2->close();

    
    header("Location: voorraad.php");
    exit;
}
?>
