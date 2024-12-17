<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update username
    if (!empty($_POST['username'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
    }

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = __DIR__ . "/uploads/";

        // Check if the uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            $profilePicture = "uploads/" . basename($_FILES['profile_picture']['name']);
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $profilePicture, $userId);
            $stmt->execute();
        }
    }

    // Redirect to avoid form resubmission
    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Edit Profile</h1>
        </div>
        <form method="POST" action="profile.php" enctype="multipart/form-data" class="auth-form">
            <div class="form-group">
                <label for="username">Change Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="profile_picture">Change Profile Picture:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo $user['profile_picture']; ?>" alt="Current Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="auth-button">Update Profile</button>
        </form>
        <div class="auth-footer">
            <a href="chat.php">Back to Chat</a>
        </div>
    </div>
</div>
</body>
</html>
