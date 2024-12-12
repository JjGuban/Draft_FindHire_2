<?php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'Applicant') {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch HR representatives
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'HR'");
$hrUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = $_POST['hr_id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $receiverId, $content]);
        $success = "Message sent successfully.";
    } else {
        $error = "Message content cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant - Follow Up</title>
</head>
<body>
    <h1>Message HR</h1>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <form method="POST">
        <label>HR Representative: 
            <select name="hr_id" required>
                <?php foreach ($hrUsers as $hr): ?>
                    <option value="<?= $hr['id'] ?>"><?= htmlspecialchars($hr['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Message: <textarea name="content" required></textarea></label><br>
        <button type="submit">Send Message</button>
    </form>
    <a href="../dashboard.php">Back to Dashboard</a>
</body>
</html>
