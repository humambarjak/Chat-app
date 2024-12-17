<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_id = intval($_POST['message_id']);
    $reaction = htmlspecialchars($_POST['reaction']);

    // Fetch the existing reactions
    $stmt = $conn->prepare("SELECT reactions FROM messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();

    // Initialize reactions if empty
    $reactions = !empty($message['reactions']) ? json_decode($message['reactions'], true) : [];
    $reactions[] = $reaction; // Add the new reaction

    // Save updated reactions back to the database
    $stmt = $conn->prepare("UPDATE messages SET reactions = ? WHERE id = ?");
    $updatedReactions = json_encode($reactions);
    $stmt->bind_param("si", $updatedReactions, $message_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
