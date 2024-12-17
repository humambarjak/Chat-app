
<?php
session_start();
require 'db.php';
/* Add this new PHP file: mark_messages_unread.php */
if (isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $current_user_id = $_SESSION['user_id'];

    $sql = "UPDATE messages SET read_status = FALSE WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $message_id, $current_user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to mark message as unread.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Message ID not provided.']);
}
?>