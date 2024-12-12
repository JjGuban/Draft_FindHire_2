<?php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch job posts
$stmt = $pdo->query("SELECT * FROM job_posts ORDER BY created_at DESC");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Job Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
        }
        .job-post {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .job-post h3 {
            margin: 0;
        }
        .job-post p {
            margin: 5px 0;
        }
        .job-post .actions {
            margin-top: 10px;
            text-align: right;
        }
        .job-post button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 5px;
        }
        .job-post button:hover {
            background-color: #0056b3;
        }
        .add-job {
            text-align: right;
            margin-bottom: 20px;
        }
        .add-job a {
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .add-job a:hover {
            background-color: #218838;
        }
        .logout {
            text-align: right;
        }
        .logout a {
            text-decoration: none;
            color: #dc3545;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Job Posts</h2>
        <div class="add-job">
        <a href="/FindHire/hr/add_job.php" class="btn btn-success">Add New Job Post</a>
        </div>
        <?php if ($jobs): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-post">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><strong>Description:</strong> <?= htmlspecialchars($job['description']) ?></p>
                    <p><strong>Posted On:</strong> <?= htmlspecialchars($job['created_at']) ?></p>
                    <div class="actions">
                        <a href="view_applications.php?job_id=<?= $job['id'] ?>">
                            <button>View Applications</button>
                        </a>
                        <a href="edit_job.php?job_id=<?= $job['id'] ?>">
                            <button>Edit Job</button>
                        </a>
                        <a href="delete_job.php?job_id=<?= $job['id'] ?>" onclick="return confirm('Are you sure you want to delete this job post?');">
                            <button style="background-color: #dc3545;">Delete Job</button>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No job posts found. Start by adding one!</p>
        <?php endif; ?>
        <div class="logout">
            <a href="../core/handleForms.php?logout=1">Logout</a>
        </div>
    </div>
</body>
</html>
