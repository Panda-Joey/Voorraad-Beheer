<?php
include 'database.php';

// inlog gegevens admin
$email = 'admin@1.com';
$password = '1';


// kijken of account al bestaat
$stmt = $conn->prepare("SELECT email FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Admin bestaat al.";
    exit;
}
$stmt->close();

// Maak admin
$hash = password_hash($password, PASSWORD_DEFAULT);
$rol = 0; // 0 = admin

$stmt = $conn->prepare("INSERT INTO user (email, wachtwoord, Rol) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $email, $hash, $rol);

if ($stmt->execute()) {
    echo "Admin aangemaakt: $email";
} else {
    echo "Fout: " . $stmt->error;
}
$stmt->close();
$conn->close();
