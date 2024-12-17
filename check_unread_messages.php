<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$reminder_time = 600; // 10 minutes in seconds

$query = "
    SELECT id, sender_id, receiver_id, message, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS time_since_sent
    FROM messages
    WHERE receiver_id = ? AND read_status = FALSE AND reminder_sent = FALSE
    HAVING time_since_sent > ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $current_user_id, $reminder_time);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    $reminders[] = [
        'message_id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'receiver_id' => $row['receiver_id'],
        'message' => $row['message'],
        'time_since_sent' => $row['time_since_sent']
    ];

    // Mark the reminder as sent
    $update_query = "UPDATE messages SET reminder_sent = TRUE WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $row['id']);
    $update_stmt->execute();
}

header('Content-Type: application/json');
echo json_encode($reminders);
?>
