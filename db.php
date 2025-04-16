<?php
$host = 'localhost';
$dbname = 'dbshivvderqsia';
$username = 'uklz9ew3hrop3';
$password = 'zyrbspyjlzjb';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
