<?php
require 'db.php';
session_start();

if (isset($_GET['chat_partner_id'])) {
    $current_user_id = $_SESSION['user_id'];
    $chat_partner_id = $_GET['chat_partner_id'];

    // Fetch messages
    $query = "
        SELECT id, message, sender_id, receiver_id, reactions, read_status
        FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $current_user_id, $chat_partner_id, $chat_partner_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'message' => $row['message'],
            'sender_id' => $row['sender_id'],
            'receiver_id' => $row['receiver_id'],
            'reactions' => $row['reactions'] ? json_decode($row['reactions'], true) : null,
            'read_status' => $row['read_status']
        ];
    }

    // Fetch unread message count
    $unread_query = "
        SELECT COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = ? AND sender_id = ? AND read_status = FALSE";
    $unread_stmt = $conn->prepare($unread_query);
    $unread_stmt->bind_param("ii", $current_user_id, $chat_partner_id);
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['unread_count'] ?? 0;

    header('Content-Type: application/json');
    echo json_encode([
        'messages' => $messages,
        'unread_count' => $unread_count
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Chat partner ID not provided']);
}

?>
