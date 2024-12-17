<?php
session_start();
require 'db.php';

if (isset($_POST['chat_partner_id'])) {
    $user_id = $_SESSION['user_id'];
    $chat_partner_id = $_POST['chat_partner_id'];

    $sql = "UPDATE messages SET read_status = TRUE 
            WHERE receiver_id = ? AND sender_id = ? AND read_status = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $chat_partner_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Chat partner ID not provided']);
}
?>
