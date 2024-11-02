<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
$sender_email = $user['email'];

// Connexion à la base de données
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_email = $_POST['receiver_email'];
    $message_text = $_POST['message_text'];

    // Insérer le message dans la base de données
    $queryInsertMessage = $db->prepare("
        INSERT INTO messages (sender_email, receiver_email, message_text, sent_at)
        VALUES (:sender_email, :receiver_email, :message_text, NOW())
    ");
    $queryInsertMessage->execute([
        'sender_email' => $sender_email,
        'receiver_email' => $receiver_email,
        'message_text' => $message_text
    ]);

    // Rediriger vers la page de messages
    header("Location: clients_messages.php");
    exit();
}
?>
