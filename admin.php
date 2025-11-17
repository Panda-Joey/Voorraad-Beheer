<?php
session_start();
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['rol'] != 0) { // alleen admin
    echo "Geen toegang.";
    exit;
}
?>


<!DOCTYPE html>
<html lang="nl">
<head>
    <link rel="stylesheet" href="tool.css">
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Welkom Admin</h1>


<form action="voorraad.php" method='post' style="display:inline;">
    <button type="submit">Voorraad beheren</button>
</form>

<form action="gebruikers.php" method='post' style="display:inline;">
    <button type="submit">Gebruikers beheren</button>
</form>

</body>
</html>
