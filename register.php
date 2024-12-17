<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $profilePicture = 'default.png'; // Default profile picture

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        
        // Check if the directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the directory if it doesn't exist
        }
    
        $targetFile = $targetDir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            $profilePicture = "uploads/" . basename($_FILES['profile_picture']['name']);
        } else {
            echo "Error uploading the file. Check permissions or path.";
            exit;
        }
    } else {
        $profilePicture = "uploads/default.png"; // Use a default profile picture
    }

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password, profile_picture) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $profilePicture);
    if ($stmt->execute()) {
        // Redirect to login.php upon successful registration
        header("Location: login.php");
        exit;
    } else {
        echo "Error: Could not register user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <img src="logo .png" alt="App Logo" class="auth-logo">
            <h1>Register</h1>
            <p>Create a new account to get started</p>
        </div>
        <form method="POST" action="register.php" class="auth-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
    <label for="profile_picture">Profile Picture:</label>
    <!-- Hidden input for file selection -->
    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;" required>
    
    <!-- Custom Button for File Input -->
    <div tabindex="0" class="plusButton" onclick="document.getElementById('profile_picture').click();">
        <svg class="plusIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
            <g mask="url(#mask0_21_345)">
                <path d="M13.75 23.75V16.25H6.25V13.75H13.75V6.25H16.25V13.75H23.75V16.25H16.25V23.75H13.75Z"></path>
            </g>
        </svg>
    </div>
</div>
            <button type="submit" class="auth-button">Sign Up</button>
        </form>
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
        <div class="social-login">
        <a href="https://www.google.com/webhp?hl=en&sa=X&ved=0ahUKEwjk1PmmqYGKAxU5VUEAHRy8HE8QPAgI" target="_blank">
    <img src="google.png" alt="Google Login">
</a>

            <a href="https://www.apple.com/nl/shop/gifts/shopping-event?afid=p238%7CsLWvmHFAR-dc_mtid_187079nc38483_pcrid_682059700958_pgrid_82186083781_pntwk_g_pchan__pexid__ptid_kwd-461511447351_&cid=aos-nl-kwgo-brand--slid---product-" target="_blank">
    <img src="apple.png" alt="Apple Login">
</a>
        </div>
    </div>
</div>
v>
    </div>
</body>
</html>

