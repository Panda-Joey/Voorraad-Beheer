<?php
session_start();
include 'database.php';

// Alleen admin mag op deze pagina
if (!isset($_SESSION['iduser']) || $_SESSION['rol'] != 0) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = $_POST['email'] ?? '';
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $rol = $_POST['rol'] ?? '';

    if (!empty($email) && !empty($wachtwoord) && $rol !== '') {
        $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO user (email, wachtwoord, rol) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $hash, $rol);

        if ($stmt->execute()) {
            $melding = "Gebruiker succesvol toegevoegd!";
        } else {
            $melding = "Fout bij toevoegen: " . $stmt->error;
        }

        $stmt->close(); 
    } 
        
    
}
    if (isset($_POST['iduser'])) {
        $iduser = $_POST['iduser'];

        $stmt0 = $conn->prepare("
            DELETE phb 
            FROM product_has_bestelling phb
            INNER JOIN bestelling b ON phb.idbestelling = b.idbestelling
            WHERE b.iduser = ?
        ");
        $stmt0->bind_param("i", $iduser);
        $stmt0->execute();
        $stmt0->close();

        // Bestelling verwijderen van de bijbehorende user
        $stmt1 = $conn->prepare("DELETE FROM bestelling WHERE iduser = ?");
        $stmt1->bind_param("i", $iduser);
        $stmt1->execute();
        $stmt1->close();

        // Gebruiker veranderen
        $stmt2 = $conn->prepare("DELETE FROM user WHERE iduser = ?");
        $stmt2->bind_param("i", $iduser);
        $stmt2->execute();
        $stmt2->close();
    }
  
  

    $result = $conn->query("SELECT iduser, email, rol FROM user ORDER BY iduser ASC");

?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="tool.css">
  <title>Gebruikers beheren</title>
</head>
<body>
  <h1>Gebruikers beheren</h1>

  <?php if (!empty($melding)) echo "<p>$melding</p>"; ?>

  <form method="post">
    <label>Email:</label><br>
    <input type="email" name="email" placeholder="E-mail"  required><br><br>

    <label>Wachtwoord:</label><br>
    <input type="password" name="wachtwoord" placeholder="Wachtwoord"  required><br><br>

    <label>Rol:</label><br>
    <select name="rol" required>
      <option value="1"> Gebruiker</option>
      <option value="0"> Admin</option>
    </select><br><br>

    <button type="submit">Toevoegen</button>
  </form>


     <h2>Overzicht van gebruikers</h2>

  <table border="1" cellpadding="8" cellspacing="0">
    
    <tr>
      <th>ID</th>
      <th>Email</th>
      <th>Rol</th>
      <th>Verwijder</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['iduser']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= $row['rol'] == 0 ? ' Admin' : ' Gebruiker' ?></td>
        <td>
      <form method="POST" action="" onsubmit="return confirm('Weet je zeker dat je dit wilt verwijderen');">
            <input type="hidden" name="iduser" value="<?= $row['iduser'] ?>">
            <button class="DEL" type="submit">🗑️ Verwijderen</button>
          </form>
      </tr>
    <?php endwhile; ?>
    
  <br><a href="admin.php">Terug</a>
</body>
</html>
