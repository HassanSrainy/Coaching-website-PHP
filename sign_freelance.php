<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon.jpg" >
    <title>KnoShare</title>
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="si.css">
    <script>
        function validateForm() {
            const email = document.forms["login"]["email"].value;
            const password = document.forms["login"]["pass"].value;

            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const passwordPattern = /^(?=.*[a-zA-Z])(?=.*\d)[A-Za-z\d]{6,15}$/;

            if (!emailPattern.test(email)) {
                alert("Veuillez entrer une adresse e-mail valide.");
                return false;
            }

            if (!passwordPattern.test(password)) {
                alert("Le mot de passe doit contenir entre 6 et 10 caractères et inclure des lettres et des chiffres.");
                return false;
            }

            return true;
        }
        function showAlert(message) {
            alert(message);
        }
    </script>
</head>
<body>

        
        <div class="container mt-5">
           
            <a href="si.html"><img class="logo" src="images/logoblack.png" alt=""></a>
        <div style="margin-left: 25%; margin-bottom: 5%;">
            <h2 id="join_titre" style="margin-left: 10%;">Create Account as freelancer</h2>
            <div id="sign" style="height: 950px;">
        <form id="login" method="post" action="">
            Name :<input type="text" name ="name"id="text" class="form-control"  placeholder="Enter your name" required>
            Laste Name :<input type="text" name="lastname" id="text" class="form-control"  placeholder="Enter your laste name" required>
          Contact:<input type="text" name="contact" id="text" class="form-control"  placeholder="Enter your Contact" required>
            Email :<input type="email" id="email" name="email" class="form-control"  placeholder="email@domain.com" required>
           Password: <input type="password" id="pass" name ="password"class="form-control"  placeholder="Password" required>
            <button type="submit" name ="formsend"id="cont" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <div class="divider">---------------or sign up  with---------------</div>
        <button id="google" class="btn btn-primary btn-block">
            <img  class="rounded-circle" style="width: 5%; height: 5%; margin-right: 5%; margin-bottom: 0%;" src="https://s.yimg.com/fz/api/res/1.2/I2ucT7v2aEn9pInvuzBnPQ--~C/YXBwaWQ9c3JjaGRkO2ZpPWZpdDtoPTI0MDtxPTgwO3c9MjQw/https://s.yimg.com/zb/imgv1/e76fa261-e45b-3514-872d-e8fa3a2473e5/t_500x300" alt="Google logo"> Google
        </button>
    </div>
    </div>
</div>

</div>
<p style="margin-left:39%; font-weight: bold; margin-top: -8%;">Already have a compte ? <a  href="log_in.php">log in</a></p>
<?php 
    include 'database.php';
    global $db;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupérer les données du formulaire
        $name = $_POST['name'];
        $lastname = $_POST['lastname'];
        $contact = $_POST['contact'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $type = "freelancer";

        // Valider les données (ajoutez vos propres validations ici)
        if (!empty($name) && !empty($lastname) && !empty($contact) && !empty($email) && !empty($password)) {
            // Vérifier si l'email existe déjà
            $checkEmail = $db->prepare("SELECT * FROM utilisateur WHERE Email = :Email");
            $checkEmail->execute(['Email' => $email]);
            // Vérifier si le contact existe déjà
            $checkContact = $db->prepare("SELECT * FROM coach WHERE contact = :contact");
            $checkContact->execute(['contact' => $contact]);

            if ($checkEmail->rowCount() > 0) {
                echo "<script>showAlert('Cet email est déjà utilisé. Veuillez en choisir un autre.');</script>";
            }  elseif ($checkContact->rowCount() > 0) {
              echo "<script>showAlert('Ce contact est déjà utilisé. Veuillez en choisir un autre.');</script>";
          }
            else {
                // Hacher le mot de passe pour des raisons de sécurité
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Préparer la requête d'insertion
                $insertu = $db->prepare("INSERT INTO utilisateur (Email, Prenom, Nom, Password, type) VALUES (:Email, :Prenom, :Nom, :Password, :type)");
                $insertc = $db->prepare("INSERT INTO freelancer (Email, contact) VALUES (:Email, :contact)");

                // Exécuter la requête avec les paramètres
                $insertu->execute([
                    'Email' => $email,
                    'Prenom' => $name,
                    'Nom' => $lastname,
                    'Password' => $hashedPassword,
                    'type' => $type
                ]);
                $insertc->execute([
                    'Email' => $email,
                    'contact' => $contact
                ]);

                
                
                session_start();
                $_SESSION['user'] = [
                    'nom' => $name,
                    'prenom' => $lastname,
                    'email' => $email,
                    'contact' => $contact
                ];
                echo "<script>
    alert('Compte créé avec succès.');
    window.location.href = 'compte_fr.php';
</script>";

            
                exit();
            }
        } else {
            echo "Tous les champs sont obligatoires.";
        }
    }
    ?>
</body>
</html>