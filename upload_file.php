<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || !isset($_POST['type'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        exit;
    }

    $file = $_FILES['file'];
    $fileType = $_POST['type'];
    $allowedTypes = ['photo', 'video'];

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
        exit;
    }

    // Validate file size (e.g., max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds the maximum allowed size of 5MB.']);
        exit;
    }

    // Validate file extensions
    $validExtensions = ($fileType === 'photo') ? ['jpg', 'jpeg', 'png', 'gif'] : ['mp4', 'avi', 'mov'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $validExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file extension.']);
        exit;
    }

    // Define the upload directory
    $uploadDir = $fileType === 'photo' ? 'uploads/photos/' : 'uploads/videos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate a unique filename
    $filename = uniqid() . '-' . basename($file['name']);
    $uploadFilePath = $uploadDir . $filename;

    // Move the uploaded file to the server
    if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
        // You can save file info to a database here (optional)

        echo json_encode(['success' => true, 'filename' => $filename, 'filepath' => $uploadFilePath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
