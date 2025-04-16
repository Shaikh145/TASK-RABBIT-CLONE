<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$task_id = $_GET['task_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM task_applications WHERE task_id = ? AND worker_id = ?");
$stmt->execute([$task_id, $user_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "<script>alert('You have already applied for this task!'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $pdo->prepare("INSERT INTO task_applications (task_id, worker_id) VALUES (?, ?)");
$stmt->execute([$task_id, $user_id]);
echo "<script>alert('Application submitted successfully!'); window.location.href='dashboard.php';</script>";
?>
