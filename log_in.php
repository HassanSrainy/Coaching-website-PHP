<?php
session_start();
include 'database.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['pass'];

    // Requête pour sélectionner l'utilisateur avec cet email
    $query = $db->prepare("SELECT * FROM utilisateur WHERE Email = :email");
    $q1 = $db->prepare("SELECT * FROM client WHERE Email = :email");
    
    // Exécution des requêtes préparées
    $query->execute(['email' => $email]);
    $q1->execute(['email' => $email]);
    
    // Récupérer les résultats
    $user = $query->fetch();
    $id = $q1->fetch();
   
    // Vérifier si l'utilisateur existe et si le mot de passe correspond
    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = [
            'email' => $user['Email'],
            'prenom' => $user['Prenom'],
            'nom' => $user['Nom'],
            'type' => $user['type'],
            'id_client' => $id ? $id['id_client'] : null  // Vérification ajoutée ici
        ];
        
        // Authentification réussie
        $message = "Login successful. Redirecting to dashboard...";
        if ($user['type'] == 'client') {
            echo "<script>window.location.href = 'si2.php';</script>";
            exit();
        } elseif ($user['type'] == 'coach') {
            echo "<script> window.location.href = 'dashboard_coach.php';</script>";
            exit();
        } elseif ($user['type'] == 'freelance') {
            echo "<script> window.location.href = 'compte_fr.php';</script>";
            exit();
        }
    } else {
        // Authentification échouée
        $message = "Invalid email or password. Please try again.";
        echo "<script>alert('$message');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <title>KnoShare</title>
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="si.css">
</head>
<body>
    
    <div class="container mt-5" >
        
        <div style=" margin-bottom: 1%; position:fixed; margin-top: -3%; ">
           
            <div id="sign" style="height: fit-content; 
            width:fit-content; border: 0px solid white; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);">
            <div class="row justify-content-center">

        <div class="col-12 col-sm-4 role" style="height: fit-content; 
            width:fit-content; margin-top: 10%; margin-right:10% ;margin-left:-10%;" >
        <h2 id="join_titre" style="margin-left: 15%;">Log in to KnoShare</h2>
            <div >
                <form id="login" method="post" action="">
                    Email:<input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                    Password: <input type="password" name="pass" class="form-control" placeholder="Password" required>
                    <button type="submit" id="cont" class="btn btn-primary btn-block">Continue</button>
                </form>
                <p style="margin-left:15%;"> You don't have an account ? <a href="join_us.html"> Join us</a></p>
            </div>
        </div>
        <div class="col-12 col-sm-4 role" style="background-color: rgba(221, 124, 55, 1); border-radius: 5%; ">
        <a href="si.html" ><img class="logo" src="images/logoblack.png" alt="" style="margin-left:-125%;
        margin-bottom:-20%; width:70% ;height:50%;margin-top:-150%; "></a>
            <img src="images/Mobile login-bro.png" alt="" style="width:150%; margin-left:21.5%;margin-top:3%;">
         
        </div> 
    </div>   
            </div>
        </div>
    </div>
</body>
</html>
