<?php
$host = "localhost";
$user = "root";
$pass = "usbw"; // <-- ESSA É A SENHA DO USBWEBSERVER
$dbname = "tecla_nativa";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão com o banco: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>