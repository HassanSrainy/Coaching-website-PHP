<!DOCTYPE html>
<html lang="en" >
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
<body  >
    <div class="container mt-5" style=" margin-bottom: 1%; ">
                   
            <div id="sign" style="height: fit-content; 
            width:fit-content; border: 0px solid white; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);">
            <div class="row justify-content-center">

        <div class="col-12 col-sm-4 role" style="height: fit-content; 
            width:fit-content; margin-top: 3%;" >
                    <a href="si.html" ><img class="logo" src="images/logoblack.png" alt="" style="margin-left:-30%;
        margin-top:-30%; margin-bottom:20%; width:60%; "></a>
          <h2 id="join_titre" style="margin-top:-40%;margin-left: -35%;">Sign up to KnoShare</h2>

        <form id="login" method="post" action="" onsubmit="return validateForm();"
         style=" margin-left:-40%;height: fit-content; 
            width:90%;">
                    Name :<input type="text" name="name"  class="form-control" placeholder="Enter your name" required>
                    Last Name :<input type="text" name="lastname"  class="form-control" placeholder="Enter your last name" required>
                    Contact:<input type="text" name="contact" class="form-control" placeholder="Enter your Contact" required>
                    Email :<input type="email"  name="email" class="form-control" placeholder="email@domain.com" required>
                    Password: <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <button type="submit" name="formsend" id="cont" class="btn btn-primary btn-block">Create Account</button>
                </form>
                <p style="margin-left:-25%; font-weight: bold; ">Already have an Account ? <a href="log_in.php">log in</a></p>
        </div>
        <div class="col-12 col-sm-4 role" style="background-color: rgba(221, 124, 55, 1); border-radius: 5%; ">
        
            <img src="images/Sign up-amico.png" alt="" style="width:150%; margin-left:-31%;margin-top:5%;">
         
        </div>

    </div>
                
                
            </div>
        </div>

    </div>
    
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
        $type = "coach";

        // Valider les données
        if (!empty($name) && !empty($lastname) && !empty($contact) && !empty($email) && !empty($password)) {
            // Vérifier si l'email existe déjà
            $checkEmail = $db->prepare("SELECT * FROM utilisateur WHERE Email = :Email");
            $checkEmail->execute(['Email' => $email]);
            // Vérifier si le contact existe déjà
            $checkContact = $db->prepare("SELECT * FROM coach WHERE contact = :contact");
            $checkContact->execute(['contact' => $contact]);

            if ($checkEmail->rowCount() > 0) {
                echo "<script>showAlert('Cet email est déjà utilisé. Veuillez en choisir un autre.');</script>";
            } elseif ($checkContact->rowCount() > 0) {
                echo "<script>showAlert('Ce contact est déjà utilisé. Veuillez en choisir un autre.');</script>";
            } else {
                // Hacher le mot de passe pour des raisons de sécurité
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Préparer la requête d'insertion
                $insertu = $db->prepare("INSERT INTO utilisateur (Email, Prenom, Nom, Password, type) VALUES (:Email, :Prenom, :Nom, :Password, :type)");
                $insertc = $db->prepare("INSERT INTO coach (Email, contact) VALUES (:Email, :contact)");

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

                // Démarrer la session et stocker les informations de l'utilisateur
                session_start();
                $_SESSION['user'] = [
                    'nom' => $name,
                    'prenom' => $lastname,
                    'email' => $email,
                    'contact' => $contact
                ];

                // Redirection immédiate vers la page de tableau de bord
                header('Location: dashboard_coach.php');
                exit();
            }
        } else {
            echo "Tous les champs sont obligatoires.";
        }
    }
    ?>
</body>
</html>
