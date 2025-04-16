<?php
session_start();
require 'db.php';

$stmt = $pdo->query("SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.id WHERE t.status = 'open'");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskRabbit Clone - Homepage</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            color: #333;
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #3498db;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filters select, .filters input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .task-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .task-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .task-card:hover {
            transform: translateY(-5px);
        }
        .task-card h3 {
            margin: 0 0 10px;
            color: #2c3e50;
        }
        .task-card p {
            margin: 5px 0;
            color: #555;
        }
        .apply-btn {
            background: #3498db;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .apply-btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <header>
        <h1>TaskRabbit Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
                <a href="#" onclick="redirect('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirect('signup.php')">Signup</a>
                <a href="#" onclick="redirect('login.php')">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <div class="filters">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Cleaning">Cleaning</option>
                <option value="Delivery">Delivery</option>
                <option value="Repair">Repair</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" placeholder="Location">
        </div>
        <div class="task-list">
            <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($task['username']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($task['category']); ?></p>
                    <p><strong>Budget:</strong> $<?php echo htmlspecialchars($task['budget']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($task['location']); ?></p>
                    <p><strong>Deadline:</strong> <?php echo htmlspecialchars($task['deadline']); ?></p>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'worker'): ?>
                        <button class="apply-btn" onclick="redirect('task_apply.php?task_id=<?php echo $task['id']; ?>')">Apply</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
