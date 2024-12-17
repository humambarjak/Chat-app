<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = __DIR__ . "/uploads/";

        // Check if the uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the directory if it doesn't exist
        }

        $targetFile = $targetDir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            $profilePicture = "uploads/" . basename($_FILES['profile_picture']['name']);

            // Update the user's profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $profilePicture, $userId);
            if ($stmt->execute()) {
                echo "Profile picture updated successfully!";
            } else {
                echo "Failed to update profile picture.";
            }
        } else {
            echo "Error uploading the file.";
        }
    } else {
        echo "Please select a photo to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile Picture</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 <div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Update Profile Picture</h1>
        </div>
        <form method="POST" action="update_photo.php" enctype="multipart/form-data" class="auth-form">
            <div class="form-group">
                <label for="profile_picture">Choose a new profile picture:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
            </div>
            <button type="submit" class="auth-button">Update Photo</button>
        </form>
        <div class="auth-footer">
            <a href="chat.php">Back to Chat</a>
        </div>
    </div>
</div>

</body>
</html>
