<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'worker') {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM worker_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['bio'];
    $skills = $_POST['skills'];
    $experience = $_POST['experience'];
    $hourly_rate = $_POST['hourly_rate'];

    if ($profile) {
        $stmt = $pdo->prepare("UPDATE worker_profiles SET bio = ?, skills = ?, experience = ?, hourly_rate = ? WHERE user_id = ?");
        $stmt->execute([$bio, $skills, $experience, $hourly_rate, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO worker_profiles (user_id, bio, skills, experience, hourly_rate) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $bio, $skills, $experience, $hourly_rate]);
    }
    echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Profile - TaskRabbit Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .profile-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            width: 500px;
            text-align: center;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        textarea {
            height: 100px;
            resize: none;
        }
        button {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Worker Profile</h2>
        <form method="POST">
            <textarea name="bio" placeholder="Bio" required><?php echo $profile ? htmlspecialchars($profile['bio']) : ''; ?></textarea>
            <input type="text" name="skills" placeholder="Skills (comma-separated)" value="<?php echo $profile ? htmlspecialchars($profile['skills']) : ''; ?>" required>
            <input type="number" name="experience" placeholder="Years of Experience" value="<?php echo $profile ? htmlspecialchars($profile['experience']) : ''; ?>" required>
            <input type="number" name="hourly_rate" placeholder="Hourly Rate ($)" step="0.01" value="<?php echo $profile ? htmlspecialchars($profile['hourly_rate']) : ''; ?>" required>
            <button type="submit">Update Profile</button>
        </form>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
