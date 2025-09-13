<?php
include 'config.php';

$name = "Asmit";
$email = "asmitnavodit621@gmail.com";
$password = password_hash("asmit123", PASSWORD_DEFAULT); // hashed password
$role = "admin";

$sql = "INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$password', '$role')";

if ($conn->query($sql) === TRUE) {
    echo "✅ Admin user created!<br>";
    echo "Login with: <br>Email: asmitnavodit621@gmail.com<br>Password: asmit123";
} else {
    echo "Error: " . $conn->error;
}
?>
