<?php
session_start();
include 'database.php';

$loginError = "";

// Check of formulier is verzonden
if (isset($_POST['knop'])) {
    $email = $_POST['email'] ?? '';
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if (empty($email) || empty($wachtwoord)) {
        $loginError = "Vul e-mail en wachtwoord in";
    } else {

        // Haal user wachtwoord en rol op
        $stmt = $conn->prepare("SELECT iduser, wachtwoord, rol FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($iduser, $hashedWachtwoord, $rol);
            $stmt->fetch();

            if (password_verify($wachtwoord, $hashedWachtwoord)) {
                
                $_SESSION['iduser'] = $iduser;
                $_SESSION['email'] = $email;
                $_SESSION['rol'] = $rol;

                // 0 = admin 1= gewone gebruiker
                if ($rol == 0) {
                    header("Location: admin.php"); 
                } else {
                    header("Location: voorraad.php"); // gewone gebruiker
                }
                exit;
            } else {
                $loginError = "Verkeerd wachtwoord";
            }
        } else {
            $loginError = "Geen account gevonden";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="tool.css">
<title>Login</title>
</head>
<body>
<div class="achtergrond">
<div class="box">
    <h1>Login</h1>
    <form action="Login.php" method="post">
        <?php if (!empty($loginError)) echo "<p style='color:red;'>$loginError</p>"; ?>

        <input class="textInput" type="email" name="email" placeholder="E-mail" required><br>
        <input class="textInput" type="password" name="wachtwoord" placeholder="Wachtwoord" required><br><br>

        <input class="button" type="submit" name="knop" value="Login">
    </form>
</div>
</div>
</body>
</html>
