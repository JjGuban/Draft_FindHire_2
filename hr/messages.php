<?php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch messages sent to the logged-in HR
$stmt = $pdo->prepare("
    SELECT messages.*, users.username AS applicant_name 
    FROM messages 
    INNER JOIN users ON messages.sender_id = users.id 
    WHERE messages.receiver_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sending replies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantId = $_POST['applicant_id'];
    $reply = trim($_POST['reply']);

    if (!empty($reply)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $applicantId, $reply]);
        $success = "Reply sent successfully.";
    } else {
        $error = "Reply cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR - Messages</title>
</head>
<body>
    <h1>Messages from Applicants</h1>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <ul>
        <?php foreach ($messages as $msg): ?>
            <li>
                <strong>From:</strong> <?= htmlspecialchars($msg['applicant_name']) ?><br>
                <strong>Message:</strong> <?= nl2br(htmlspecialchars($msg['content'])) ?><br>
                <form method="POST">
                    <input type="hidden" name="applicant_id" value="<?= $msg['sender_id'] ?>">
                    <label>Reply: <textarea name="reply" required></textarea></label><br>
                    <button type="submit">Send Reply</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="job_posts.php">Back to Job Posts</a>
</body>
</html>
