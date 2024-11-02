<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Connexion à la base de données
include 'database.php';

// Récupérer les données du formulaire
$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];

// Récupérer l'email actuel de l'utilisateur connecté
$currentEmail = $_SESSION['user']['email'];

try {
    $db->beginTransaction();

    // Mettre à jour les informations dans la table utilisateur
    $updateUser = $db->prepare("UPDATE utilisateur SET Email = :email, Prenom = :prenom, Nom = :nom WHERE Email = :currentEmail");
    $updateUser->execute([
        'email' => $email,
        'prenom' => $prenom,
        'nom' => $nom,
        'currentEmail' => $currentEmail
    ]);

    // Mettre à jour les informations dans la table client
    $updateClient = $db->prepare("UPDATE client SET Email = :email WHERE Email = :currentEmail");
    $updateClient->execute([
        'email' => $email,
        'currentEmail' => $currentEmail
    ]);

    $db->commit();

    // Mettre à jour l'email dans la session
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['prenom'] = $prenom;
    $_SESSION['user']['nom'] = $nom;

    header("Location: profilclient.php");
    exit();
} catch (PDOException $e) {
    $db->rollBack();
    echo "Erreur : " . $e->getMessage();
}
?>
