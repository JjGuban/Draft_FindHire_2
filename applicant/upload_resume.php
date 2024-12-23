<?php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'Applicant') {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = $_POST['job_id'] ?? null;

    // Check if a file is uploaded
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = $_FILES['resume']['name'];
        $fileSize = $_FILES['resume']['size'];
        $fileType = $_FILES['resume']['type'];

        // Validate file type and size
        $allowedExtensions = ['pdf'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $errorMessage = "Only PDF files are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB file size limit
            $errorMessage = "File size exceeds the 2MB limit.";
        } else {
            // Define upload directory
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
            }

            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFilePath = $uploadDir . $newFileName;

            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                // Insert application data into the database
                $stmt = $pdo->prepare("
                    INSERT INTO applications (job_id, applicant_id, resume_path, status) 
                    VALUES (:job_id, :applicant_id, :resume_path, 'Pending')
                ");
                $stmt->execute([
                    ':job_id' => $jobId,
                    ':applicant_id' => $_SESSION['user_id'],
                    ':resume_path' => $uploadFilePath
                ]);

                $successMessage = "Application submitted successfully!";
            } else {
                $errorMessage = "Error uploading file. Please try again.";
            }
        }
    } else {
        $errorMessage = "Please upload a resume.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for a Job</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            color: white;
            text-align: center;
        }
        .error {
            background-color: #e74c3c;
        }
        .success {
            background-color: #2ecc71;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="file"] {
            margin-bottom: 15px;
        }
        button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back {
            margin-top: 10px;
            text-align: center;
        }
        .back a {
            text-decoration: none;
            color: #007bff;
        }
        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Apply for a Job</h2>
        <?php if (isset($errorMessage)): ?>
            <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php elseif (isset($successMessage)): ?>
            <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <form action="upload_resume.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($_POST['job_id'] ?? '') ?>">
            <label for="resume">Upload your resume (PDF only):</label>
            <input type="file" name="resume" id="resume" accept=".pdf" required>
            <button type="submit">Submit Application</button>
        </form>
        <div class="back">
            <a href="apply_job.php">Back to Job Listings</a>
        </div>
    </div>
</body>
</html>
