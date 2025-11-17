<?php 
session_start(); 

include 'database.php';


if (!isset($_SESSION['iduser'])) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productnaam = $_POST['product'];
    $type = $_POST['type'];
    $inkoop = $_POST['inkoop'];
    $verkoop = $_POST['verkoop'];
    $fabriek = $_POST['fabriek'];
    $aantal = $_POST['voorraad'];
    $locatie = $_POST['locatie'];

    //product toevoegen
    $stmt = $conn->prepare("INSERT INTO product (naam, type, inkoopwaarde, verkoopwaarde, idfabriek)VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddi", $productnaam, $type, $inkoop, $verkoop, $fabriek);
    $stmt->execute();

    
    $idproduct = $conn->insert_id;

    //totale waarde voorraad berekening
    $waarde_voorraad = $inkoop * $aantal;

    //totale waarde in de db zetten
    $stmt2 = $conn->prepare("INSERT INTO voorraad (idproduct, idlocatie, aantal, waarde_voorraad)VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiid", $idproduct, $locatie, $aantal, $waarde_voorraad);
    $stmt2->execute();

 
    echo "Product en voorraad toegevoegd!";
}

?>

<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="tool.css">
  <title>Producten toevoegen</title>
</head>
<body>

    <nav class="navbar">
        <div class="nav-title">⚒️ ToolsForEver</div>
        <ul>
            <li><a href="Bijvullen.php">🛒 Bestelling</a></li>
            <li><a href="besteloverzicht.php" > Besteloverzicht</a></li>
            <li><a href="voorraad.php">📦 voorraad</a></li>
            <li><a href="Fabriek_Locatie.php">➕ Fabriek/Locatie toevoegen</a></li>

                </ul>
                    </nav>

        <div id="toevoegen">

        <h2>Nieuw product toevoegen</h2>

            <form method="post">
    <label>Product naam:</label><br>
    <input type="text" name="product" required><br><br>

    <label>Type:</label><br>
    <input type="text" name="type" required><br><br>

    <label>Fabriek:</label><br>
        <select name="fabriek" required>
            <?php
                $result = $conn->query("SELECT idfabriek, naam FROM fabriek ORDER BY naam");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['idfabriek']}'>{$row['naam']}</option>";
                            } ?>  
</select><br><br>

    <label>Inkoopprijs:</label><br>
    <input type="number" step="1" name="inkoop" required><br><br>

    <label>Verkoopprijs:</label><br>
    <input type="number" step="1" name="verkoop" required><br><br>

    <label>Voorraad aantal:</label><br>
    <input type="number" name="voorraad" required><br><br>

    <label>Locatie:</label><br>
    <select name="locatie">
        <?php
        $result = $conn->query("SELECT idlocatie, regio FROM locatie");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['idlocatie']}'>{$row['regio']}</option>";
        }
        
        ?>
    </select><br><br>

    <button type="submit">Toevoegen</button>
</form>

</div>

    

        