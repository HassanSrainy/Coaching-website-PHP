<?php
session_start();

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $description = $_POST['description'];

    // Update the session data
    $_SESSION['user']['prenom'] = $prenom;
    $_SESSION['user']['nom'] = $nom;
    $_SESSION['user']['email'] = $email;

    // Update the user details in the utilisateur table
    $queryUser = $db->prepare("UPDATE utilisateur SET Prenom = :prenom, Nom = :nom, Email = :email WHERE Email = :old_email");
    $queryUser->execute([
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'old_email' => $_SESSION['user']['email']
    ]);

    // Update the user details in the coach table
    $queryCoach = $db->prepare("UPDATE coach SET Email = :email, contact = :contact, description = :description WHERE Email = :old_email");
    $queryCoach->execute([
        'email' => $email,
        'contact' => $contact,
        'description' => $description,
        'old_email' => $_SESSION['user']['email']
    ]);

    // Update session with the new email if it has changed
    $_SESSION['user']['email'] = $email;

    // Redirect back to the profile page
    header('Location: coach_profile.php');
    exit();
}
?>
