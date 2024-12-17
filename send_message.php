<?php
// send_message.php
// send_message.php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);

    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, reactions) VALUES (?, ?, ?, ?)");
    $null = null;
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $null);
    

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
