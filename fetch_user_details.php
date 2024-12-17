<?php
require 'db.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $query = "SELECT id, username, profile_picture, last_seen FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user = [
            'id' => $row['id'],
            'username' => $row['username'],
            'profile_picture' => $row['profile_picture'] ?: 'uploads/default.png',
            'last_seen' => $row['last_seen']
        ];
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'User ID not provided']);
}
