<?php
session_start();
include 'database.php';

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

$iduser = $_SESSION['iduser'];
$besteldatum = date('Y-m-d');

if (isset($_POST['plaats_bestelling'])) {
    if (!empty($_POST['idproductlocatie'])) {
        list($idproduct, $idlocatie) = explode('|', $_POST['idproductlocatie']);
        $idproduct = intval($idproduct);
        $idlocatie = intval($idlocatie);
    } else {
        echo "<p style='color:red;'>Selecteer een product.</p>";
        exit;
    }

    $aantal = intval($_POST['aantal']);
    if ($aantal < 1) {
        echo "<p style='color:red;'>Aantal moet minimaal 1 zijn.</p>";
        exit;
    }

    $aankomstdatum = $_POST['aankomstdatum'] ?? null;

    // Bestelling aanmaken, levering = 0 (nog niet geleverd)
    $stmt = $conn->prepare("INSERT INTO bestelling (iduser, besteldatum, aankomstdatum, levering) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iss", $iduser, $besteldatum, $aankomstdatum);
    $stmt->execute();
    $idbestelling = $conn->insert_id;
    $stmt->close();

    // Koppeling naar product
    $stmt2 = $conn->prepare("INSERT INTO product_has_bestelling (idproduct, idbestelling, aantal) VALUES (?, ?, ?)");
    $stmt2->bind_param("iii", $idproduct, $idbestelling, $aantal);
    $stmt2->execute();
    $stmt2->close();

    
    header("Location: geleverd.php?idbestelling=$idbestelling&idproduct=$idproduct&idlocatie=$idlocatie&aantal=$aantal");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Bestelling plaatsen</title>
<link rel="stylesheet" href="bestelling.css">

</head>
<body>

<nav class="navbar">
    <div class="nav-title">ToolsForEver</div>
    <ul>
        <li><a href="voorraad.php">Voorraad</a></li>
        <li><a href="bijvullen.php" class="active">Nieuwe bestelling</a></li>
        <li><a href="besteloverzicht.php">Besteloverzicht</a></li>
        <li><a href="toevoeg.php">Product toevoegen</a></li>
        <li><a href="Fabriek_Locatie.php">Fabriek/Locatie toevoegen</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Nieuwe bestelling plaatsen</h2>

    <form method="POST">
        <label for="idproductlocatie">Product</label>
        <select name="idproductlocatie" required>
            <option value="">-- Kies een product --</option>
            <?php
            $query = "SELECT 
                    p.idproduct, p.naam AS productnaam,
                    l.idlocatie, l.regio AS locatie,
                    f.naam AS fabriek, v.aantal AS voorraad
                FROM voorraad v
                INNER JOIN product p ON v.idproduct = p.idproduct
                INNER JOIN locatie l ON v.idlocatie = l.idlocatie
                INNER JOIN fabriek f ON p.idfabriek = f.idfabriek
                ORDER BY p.naam
            ";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()):
            ?>
                <option value="<?= $row['idproduct'] ?>|<?= $row['idlocatie'] ?>">
                    <?= htmlspecialchars($row['productnaam'] . " — " . $row['locatie'] . " — " . $row['fabriek'] . " (voorraad: " . $row['voorraad'] . ")") ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="aantal">Aantal</label>
        <input type="number" name="aantal" min="1" required>

        <label for="aankomstdatum">Aankomstdatum</label>
        <input type="date" name="aankomstdatum">

        <button type="submit" name="plaats_bestelling">Bestelling plaatsen</button>
    </form>
</div>

</body>
</html>
