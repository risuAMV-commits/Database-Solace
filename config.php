<?php
$host = 'localhost:3307/';      // usually 'localhost' in XAMPP
$db   = 'resort_booking'; // your database name
$user = 'root';           // your MySQL username
$pass = '';               // your MySQL password (empty if no password)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>