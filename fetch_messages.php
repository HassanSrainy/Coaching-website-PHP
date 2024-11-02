<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_GET['client_email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

include 'database.php';

$coachEmail = $_SESSION['user']['email'];
$clientEmail = $_GET['client_email'];

$stmt = $db->prepare("SELECT * FROM messages WHERE (sender_email = :coach_email AND receiver_email = :client_email) OR (sender_email = :client_email AND receiver_email = :coach_email) ORDER BY sent_at DESC");
$stmt->execute([
    'coach_email' => $coachEmail,
    'client_email' => $clientEmail
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($messages);
?>
