<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'client') {
    $stmt = $pdo->prepare("SELECT t.*, GROUP_CONCAT(ta.id) as application_ids, GROUP_CONCAT(u.username) as applicants 
                           FROM tasks t 
                           LEFT JOIN task_applications ta ON t.id = ta.task_id 
                           LEFT JOIN users u ON ta.worker_id = u.id 
                           WHERE t.user_id = ? 
                           GROUP BY t.id");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $app_stmt = $pdo->prepare("SELECT ta.*, t.title, u.username 
                               FROM task_applications ta 
                               JOIN tasks t ON ta.task_id = t.id 
                               JOIN users u ON ta.worker_id = u.id 
                               WHERE t.user_id = ?");
    $app_stmt->execute([$user_id]);
    $applications = $app_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT ta.*, t.title 
                           FROM task_applications ta 
                           JOIN tasks t ON ta.task_id = t.id 
                           WHERE ta.worker_id = ?");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $role == 'client') {
    $application_id = $_POST['application_id'];
    $action = $_POST['action'];
    $status = $action == 'accept' ? 'accepted' : 'rejected';

    $stmt = $pdo->prepare("UPDATE task_applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    if ($action == 'accept') {
        $task_id = $pdo->query("SELECT task_id FROM task_applications WHERE id = $application_id")->fetchColumn();
        $pdo->prepare("UPDATE tasks SET status = 'assigned' WHERE id = ?")->execute([$task_id]);
    }

    echo "<script>alert('Application $status!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskRabbit Clone</title>
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
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .task-card, .application-card {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .task-card:last-child, .application-card:last-child {
            border-bottom: none;
        }
        .action-btn {
            background: #3498db;
            color: white;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            transition: background 0.3s;
        }
        .action-btn:hover {
            background: #2980b9;
        }
        .reject-btn {
            background: #e74c3c;
        }
        .reject-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <?php if ($role == 'client'): ?>
                <a href="#" onclick="redirect('post_task.php')">Post Task</a>
            <?php else: ?>
                <a href="#" onclick="redirect('worker_profile.php')">Profile</a>
            <?php endif; ?>
            <a href="#" onclick="redirect('logout.php')">Logout</a>
        </nav>
    </header>
    <div class="container">
        <?php if ($role == 'client'): ?>
            <div class="section">
                <h2>Your Tasks</h2>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($task['status']); ?></p>
                        <p><strong>Budget:</strong> $<?php echo htmlspecialchars($task['budget']); ?></p>
                        <p><strong>Applicants:</strong> <?php echo $task['applicants'] ? htmlspecialchars($task['applicants']) : 'None'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section">
                <h2>Applications</h2>
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <p><strong>Task:</strong> <?php echo htmlspecialchars($app['title']); ?></p>
                        <p><strong>Applicant:</strong> <?php echo htmlspecialchars($app['username']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($app['status']); ?></p>
                        <?php if ($app['status'] == 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                <button type="submit" name="action" value="accept" class="action-btn">Accept</button>
                                <button type="submit" name="action" value="reject" class="action-btn reject-btn">Reject</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="section">
                <h2>Your Applications</h2>
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <p><strong>Task:</strong> <?php echo htmlspecialchars($app['title']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($app['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
