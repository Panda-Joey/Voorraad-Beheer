<?php
session_start(); 
include 'database.php';

if (!isset($_SESSION['iduser'])) {
    header("Location: Login.php");
    exit;
}

//Filters ophalen
$filterLocatie = isset($_GET['locatie']) ? intval($_GET['locatie']) : 0;
$filterFabriek = isset($_GET['fabriek']) ? intval($_GET['fabriek']) : 0;

//Filter query
$query = 
"    SELECT 
        l.regio AS locatie,
        p.idproduct,
        p.naam AS productnaam,
        p.type,
        f.naam AS fabriek,
        v.aantal,
        p.inkoopwaarde,
        p.verkoopwaarde,
        (v.aantal * p.inkoopwaarde) AS waarde_voorraad
    FROM voorraad v
    INNER JOIN product p ON v.idproduct = p.idproduct
    INNER JOIN locatie l ON v.idlocatie = l.idlocatie
    INNER JOIN fabriek f ON p.idfabriek = f.idfabriek
    WHERE 1=1
";

if ($filterLocatie > 0) {
    $query .= " AND l.idlocatie = " . $filterLocatie;
}
if ($filterFabriek > 0) {
    $query .= " AND f.idfabriek = " . $filterFabriek;
}

$query .= " ORDER BY l.regio, p.naam";
$result = $conn->query($query);

// Totale waarde opnieuw berekenen met filters
$totalQuery = 
"   SELECT SUM(v.aantal * p.inkoopwaarde) AS totaal
    FROM voorraad v
    INNER JOIN product p ON v.idproduct = p.idproduct
    INNER JOIN locatie l ON v.idlocatie = l.idlocatie
    INNER JOIN fabriek f ON p.idfabriek = f.idfabriek
    WHERE 1=1
";
if ($filterLocatie > 0) {
    $totalQuery .= " AND l.idlocatie = " . $filterLocatie;
}
if ($filterFabriek > 0) {
    $totalQuery .= " AND f.idfabriek = " . $filterFabriek;
}
$totalResult = $conn->query($totalQuery);
$total = $totalResult->fetch_assoc();

// Dropdown-data ophalen
$locaties = $conn->query("SELECT * FROM locatie ORDER BY regio");
$fabrieken = $conn->query("SELECT * FROM fabriek ORDER BY naam");
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Voorraadbeheer - ToolsForEver</title>
  <link rel="stylesheet" href="tool.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-title">⚒️ ToolsForEver</div>
    <ul>
        <li><a href="Bijvullen.php">🛒 Bestelling</a></li>
        <li><a href="besteloverzicht.php" > Besteloverzicht</a></li>
        <li><a href="toevoeg.php">➕ Product toevoegen</a></li>
        <li><a href="Fabriek_Locatie.php">➕ Fabriek/Locatie toevoegen</a></li>
        <li>
        <form action="login.php" method="post">
        <button type="submit" class="linkButton">Uitloggen</button>
        </li>
      </form>
    </ul>
</nav>

<h2>Voorraadbeheer</h2>

<!-- Filter -->
<form method="GET" style="margin-bottom:20px;">
    <label>Locatie:</label>
    <select name="locatie">
        <option value="0">Alle</option>
        <?php while($loc = $locaties->fetch_assoc()): ?>
            <option value="<?= $loc['idlocatie'] ?>" <?= ($filterLocatie == $loc['idlocatie']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['regio']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Fabriek:</label>
    <select name="fabriek">
        <option value="0">Alle</option>
        <?php while($fab = $fabrieken->fetch_assoc()): ?>
            <option value="<?= $fab['idfabriek'] ?>" <?= ($filterFabriek == $fab['idfabriek']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($fab['naam']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Filter toepassen</button>
    
</form>

<table>
    <tr>
        <th>Locatie</th>    
        <th>Product</th>
        <th>Type</th>
        <th>Fabriek</th>
        <th>Voorraad</th>
        <th>Inkoopprijs</th>
        <th>Verkoopprijs</th>
        <th>Waarde voorraad</th>
        <th>Opslaan</th>
        <th>Verwijderen</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php $style = ($row['aantal'] < 10) ? 'style="background-color:#ffdddd;"' : ''; ?> 
        <tr <?= $style ?>>
            <form method="POST" action="edit.php">
                <td><?= htmlspecialchars($row['locatie']) ?></td>
                <td><?= htmlspecialchars($row['productnaam']) ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><?= htmlspecialchars($row['fabriek']) ?></td>
                <td><?= htmlspecialchars($row['aantal']) ?></td>
                <td><input type="number" step="0.1" name="inkoop" value="<?= $row['inkoopwaarde'] ?>"></td>
                <td><input type="number" step="0.1" name="verkoopprijs" value="<?= $row['verkoopwaarde'] ?>"></td>
                <td>€ <?= number_format($row['waarde_voorraad'], 2, ',', '.') ?></td>
                <td>
                    <input type="hidden" name="idproduct" value="<?= $row['idproduct'] ?>">
                    <button class="Save" type="submit">💾 Opslaan</button>
                </td>
            </form>
            <td>
                <form method="POST" action="productDEL.php" onsubmit="return confirm('Weet je zeker dat je dit product wilt verwijderen?');">
                    <input type="hidden" name="idproduct" value="<?= $row['idproduct'] ?>">
                    <button class="DEL" type="submit">🗑️ Verwijderen</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="10">Geen producten gevonden.</td></tr>
    <?php endif; ?>

    <?php if (!empty($total['totaal'])): ?>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align:right; font-weight:bold;">Totale voorraadwaarde:</td>
            <td colspan="3" style="font-weight:bold;">
                € <?= number_format($total['totaal'], 2, ',', '.') ?>
            </td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>

<br><a href="admin.php">Terug</a>
</body>
</html>
