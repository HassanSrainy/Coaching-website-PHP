<?php
session_start();

if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$messageText = trim($data['message_text']);
$receiverEmail = trim($data['receiver_email']);

if (!$messageText || !$receiverEmail) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message and receiver cannot be empty']);
    exit();
}

include 'database.php';

$stmt = $db->prepare("INSERT INTO messages (sender_email, receiver_email, message_text, sent_at) VALUES (:sender_email, :receiver_email, :message_text, NOW())");
$stmt->execute([
    'sender_email' => $_SESSION['user']['email'],
    'receiver_email' => $receiverEmail,
    'message_text' => $messageText
]);

echo json_encode(['success' => true]);
?>
