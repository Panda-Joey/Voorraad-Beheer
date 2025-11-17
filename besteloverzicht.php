<?php
session_start();
include 'database.php';

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

$iduser = $_SESSION['iduser'];


$filterLevering = isset($_GET['levering']) ? intval($_GET['levering']) : -1; // -1 = alle

// Leveringstatus aanpassen
if (isset($_POST['update_levering'])) {
    $idbestelling = intval($_POST['idbestelling']);
    $geleverd = intval($_POST['geleverd']);

    $stmt = $conn->prepare("UPDATE bestelling SET levering = ? WHERE idbestelling = ?");
    $stmt->bind_param("ii", $geleverd, $idbestelling);
    $stmt->execute();
    $stmt->close();
}

// Query met filter
$query = 
"   SELECT 
        b.idbestelling,
        b.besteldatum,
        b.aankomstdatum,
        b.levering,
        phb.aantal,
        p.naam AS productnaam,
        l.regio AS locatie,
        f.naam AS fabriek
    FROM bestelling b
    INNER JOIN product_has_bestelling phb ON b.idbestelling = phb.idbestelling
    INNER JOIN product p ON phb.idproduct = p.idproduct
    INNER JOIN fabriek f ON p.idfabriek = f.idfabriek
    INNER JOIN voorraad v ON v.idproduct = p.idproduct
    INNER JOIN locatie l ON v.idlocatie = l.idlocatie
    WHERE b.iduser = ?
";

if ($filterLevering === 1) {
    $query .= " AND b.levering = 1";
} elseif ($filterLevering === 0) {
    $query .= " AND b.levering = 0";
}

$query .= " ORDER BY b.besteldatum DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Besteloverzicht</title>
<link rel="stylesheet" href="bestelling.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-title">ToolsForEver</div>
    <ul>
        <li><a href="voorraad.php">Voorraad</a></li>
        <li><a href="bijvullen.php">Nieuwe Bestelling</a></li>
        <li><a href="besteloverzicht.php" class="active">Besteloverzicht</a></li>
        <li><a href="toevoeg.php">Product toevoegen</a></li>
        <li><a href="Fabriek_Locatie.php">Fabriek/Locatie toevoegen</a></li>
    </ul>
</nav>

<div class="container" style="width:90%; margin: 20px auto;">
<h1>Overzicht van Jouw Bestellingen</h1>

<!-- Filter formulier -->
<form method="GET" style="margin-bottom:20px;">
    <label>Filter op levering:</label>
    <select name="levering">
        <option value="-1" <?= $filterLevering === -1 ? 'selected' : '' ?>>Alle</option>
        <option value="1" <?= $filterLevering === 1 ? 'selected' : '' ?>>Geleverd</option>
        <option value="0" <?= $filterLevering === 0 ? 'selected' : '' ?>>Niet geleverd</option>
    </select>
    <button type="submit">Filter toepassen</button>
</form>

<?php if ($result->num_rows > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Aantal</th>
        <th>Locatie</th>
        <th>Fabriek</th>
        <th>Besteldatum</th>
        <th>Aankomstdatum</th>
        <th>Status</th>
        <th>Actie</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['idbestelling']) ?></td>
            <td><?= htmlspecialchars($row['productnaam']) ?></td>
            <td><?= $row['aantal'] ?></td>
            <td><?= htmlspecialchars($row['locatie']) ?></td>
            <td><?= htmlspecialchars($row['fabriek']) ?></td>
            <td><?= htmlspecialchars($row['besteldatum']) ?></td>
            <td><?= htmlspecialchars($row['aankomstdatum'] ?? '-') ?></td>
            <td>
                <?= $row['levering'] == 1
                    ? '<span class="status-geleverd">Geleverd</span>'
                    : '<span class="status-niet">Nog niet geleverd</span>' ?>
            </td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idbestelling" value="<?= $row['idbestelling'] ?>">
                    <input type="hidden" name="geleverd" value="<?= $row['levering'] == 1 ? 0 : 1 ?>">
                    <?php if ($row['levering'] == 0): ?>
                        <button type="submit" name="update_levering">Update als geleverd</button>
                    <?php else: ?>
                        <button type="submit" name="update_levering">Update naar niet geleverd</button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p>Geen bestellingen gevonden.</p>
<?php endif; ?>

</div>
</body>
</html>
