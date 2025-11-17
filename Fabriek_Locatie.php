<?php
session_start(); 

include 'database.php';


if (!isset($_SESSION['iduser'])) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Fabriek toevoegen
    if (!empty($_POST['fabriek'])) {
        $fabriek = $_POST['fabriek'];

        
        $check = $conn->prepare("SELECT idfabriek FROM fabriek WHERE naam = ?");
        $check->bind_param("s", $fabriek);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<p style='color:red;'>Fabriek '$fabriek' bestaat al!</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO fabriek (naam) VALUES(?)");
            $stmt->bind_param("s", $fabriek);
            $stmt->execute();
            echo "<p style='color:green;'>Fabriek '$fabriek' succesvol toegevoegd!</p>";
        }
    }

    // Locatie toevoegen
    if (!empty($_POST['locatie'])) {
        $locatie = $_POST['locatie'];

        
        $check = $conn->prepare("SELECT idlocatie FROM locatie WHERE regio = ?");
        $check->bind_param("s", $locatie);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "'$locatie' bestaat al!";
        } else {
            $stmt = $conn->prepare("INSERT INTO locatie (regio) VALUES(?)");
            $stmt->bind_param("s", $locatie);
            $stmt->execute();
            echo " '$locatie'toegevoegd";
        }
    }
}

?>


<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="tool.css">
  <title>Fabriek/Locatie toevoegen</title>
</head>

<body>
<nav class="navbar">
        <div class="nav-title">⚒️ ToolsForEver</div>
        <ul>
            <li><a href="Bijvullen.php">🛒 Bestelling</a></li>
            <li><a href="besteloverzicht.php" > Besteloverzicht</a></li>
            <li><a href="voorraad.php">📦 voorraad</a></li>
            <li><a href="toevoeg.php">➕ Product toevoegen</a></li>
            <li>
            <form action="login.php" method="post">
            <button type="submit" class="linkButton">Uitloggen</button>
            </li>

                </ul>
                    </nav>

        <form method="post">
            <label>Fabriek naam:</label><br>
            <input type="text" name="fabriek" required><br><br>
            <button type="submit">Toevoegen</button>
        </form>

        <form method="post">
            <label>Locatie naam:</label><br>
            <input type="text" name="locatie" required><br><br>
            <button type="submit">Toevoegen</button>
        </form>

</body>