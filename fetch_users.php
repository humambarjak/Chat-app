<?php
require 'db.php';
session_start();

$current_user_id = $_SESSION['user_id'];

$query = "
    SELECT users.id, users.username, users.profile_picture,
    (
        SELECT COUNT(*)
        FROM messages
        WHERE messages.sender_id = users.id
        AND messages.receiver_id = ?
        AND messages.read_status = FALSE
    ) AS unread_count
    FROM users
    WHERE users.id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'profile_picture' => $row['profile_picture'] ?: 'uploads/default.png',
        'unread_count' => $row['unread_count']
    ];
}

header('Content-Type: application/json');
echo json_encode($users);
?>
