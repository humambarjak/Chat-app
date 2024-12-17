<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            // Update last_seen
            $updateStmt = $conn->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();

            header('Location: chat.php');
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <img src="logo .png" alt="App Logo" class="auth-logo">
            <h1>Login</h1>
            <p>Securely log in to access your account</p>
        </div>
        <form method="POST" action="login.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="auth-button">Log In</button>
        </form>
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
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


</body>
</html>
