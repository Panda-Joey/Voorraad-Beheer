<?php
session_start();
include 'database.php';

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}


$idbestelling = intval($_GET['idbestelling'] ?? 0);
$idproduct = intval($_GET['idproduct'] ?? 0);
$idlocatie = intval($_GET['idlocatie'] ?? 0);
$aantal = intval($_GET['aantal'] ?? 0);

if ($idbestelling === 0 || $idproduct === 0 || $idlocatie === 0) {
    die("Ongeldige bestelling.");
}

// Als gebruiker levering bevestigt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $geleverd = intval($_POST['geleverd']); // 1 of 0

    // Update levering in bestelling
    $stmt = $conn->prepare("UPDATE bestelling SET levering = ? WHERE idbestelling = ?");
    $stmt->bind_param("ii", $geleverd, $idbestelling);
    $stmt->execute();
    $stmt->close();

    // Als geleverd, dan voorraad bijwerken
    if ($geleverd === 1) {
        $stmt2 = $conn->prepare("UPDATE voorraad SET aantal = aantal + ? WHERE idproduct = ? AND idlocatie = ?");
        $stmt2->bind_param("iii", $aantal, $idproduct, $idlocatie);
        $stmt2->execute();
        $stmt2->close();
    }

    $conn->close();

    
    header("Location: besteloverzicht.php");
    exit;
}

// bestelling laten zien
$query = 
    "SELECT p.naam AS productnaam, l.regio AS locatie, f.naam AS fabriek 
    FROM product p
    INNER JOIN fabriek f ON p.idfabriek = f.idfabriek
    INNER JOIN locatie l ON l.idlocatie = $idlocatie
    WHERE p.idproduct = $idproduct
";
$result = $conn->query($query);
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Levering bevestigen</title>

</head>
<body>

<div class="container">
    <h2>Bevestig levering</h2>
    <p><strong>Product:</strong> <?= htmlspecialchars($product['productnaam']) ?></p>
    <p><strong>Locatie:</strong> <?= htmlspecialchars($product['locatie']) ?></p>
    <p><strong>Fabriek:</strong> <?= htmlspecialchars($product['fabriek']) ?></p>
    <p><strong>Aantal:</strong> <?= htmlspecialchars($aantal) ?></p>

    <form method="POST">
        <button type="submit" name="geleverd" value="1" class="btn-yes">Ja, geleverd</button>
        <button type="submit" name="geleverd" value="0" class="btn-no">Nee, niet geleverd</button>
    </form>
</div>

</body>
</html>
