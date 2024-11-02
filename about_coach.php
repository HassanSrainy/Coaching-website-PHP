<?php
session_start();

// Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Inclure le fichier de configuration de la base de données
include 'database.php';

try {
    // Récupérer les informations du coach depuis l'URL
    $prenom = isset($_GET['prenom']) ? $_GET['prenom'] : '';
    $nom = isset($_GET['nom']) ? $_GET['nom'] : '';
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    $cours = isset($_GET['cours']) ? $_GET['cours'] : '';
    $prix = isset($_GET['prix']) ? $_GET['prix'] : '';

    // Vérifier que l'email est valide avant d'exécuter la requête
    if (!empty($email)) {
        // Requête SQL pour récupérer le contact du coach
        $query = $db->prepare("SELECT contact FROM coach WHERE Email = :email");
        $query->execute(['email' => $email]);

        // Récupérer le résultat
        $coach = $query->fetch(PDO::FETCH_ASSOC);
        $contact = $coach['contact'] ?? '';
    }

    // Fetch messages
    $messages = [];
    $message_error = '';
    if ($email && isset($_SESSION['user']['email'])) {
        $stmt = $db->prepare("SELECT * FROM messages WHERE (sender_email = :user_email AND receiver_email = :coach_email) OR (sender_email = :coach_email AND receiver_email = :user_email) ORDER BY sent_at DESC");
        $stmt->execute([
            'user_email' => $_SESSION['user']['email'],
            'coach_email' => $email
        ]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Handle sending a message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
        $message_text = trim($_POST['message_text']);
        if ($message_text) {
            $stmt = $db->prepare("INSERT INTO messages (sender_email, receiver_email, message_text, sent_at) VALUES (:sender_email, :receiver_email, :message_text, NOW())");
            $stmt->execute([
                'sender_email' => $_SESSION['user']['email'],
                'receiver_email' => $email,
                'message_text' => $message_text
            ]);
            // Set a session variable to keep the messaging section open
            $_SESSION['open_messaging_section'] = true;
            // Refresh the page to show the new message
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $message_error = 'Message cannot be empty';
        }
    }

    // Message de succès
    $success_message = "";
    $error_message = "";

    // Traiter la soumission du formulaire d'inscription
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $cours = isset($_POST['cours']) ? $_POST['cours'] : '';
        $prix = isset($_POST['prix']) ? $_POST['prix'] : '';

        // Récupérer l'id_client à partir de l'email
        $id_client = null;
        if (isset($_SESSION['user']['email'])) {
            $user_email = $_SESSION['user']['email'];
            $client_query = $db->prepare("SELECT id_client FROM client WHERE Email = :email");
            $client_query->execute(['email' => $user_email]);
            $client_result = $client_query->fetch(PDO::FETCH_ASSOC);
            if ($client_result) {
                $id_client = $client_result['id_client'];
            }
        }

        if ($id_client) {
            // Requête pour récupérer l'id du coach et l'id de l'enseignement
            $query = $db->prepare("SELECT id_enseignement, id_coach FROM enseignement WHERE id_cours = (SELECT id_cours FROM cours WHERE nom_cours = :cours) AND id_coach = (SELECT id_coach FROM coach WHERE Email = :email) AND prix = :prix");
            $query->execute(['email' => $email, 'cours' => $cours, 'prix' => $prix]);
            $enseignement = $query->fetch(PDO::FETCH_ASSOC);

            if ($enseignement) {
                $id_enseignement = $enseignement['id_enseignement'];
                $id_coach = $enseignement['id_coach'];

                // Vérifier si le client est déjà inscrit à cet enseignement
                $checkQuery = $db->prepare("SELECT COUNT(*) FROM inscriptioncours WHERE id_client = :id_client AND id_enseignement = :id_enseignement");
                $checkQuery->execute(['id_client' => $id_client, 'id_enseignement' => $id_enseignement]);
                $isAlreadyEnrolled = $checkQuery->fetchColumn();

                if ($isAlreadyEnrolled) {
                    $error_message = "Vous êtes déjà inscrit à cet enseignement.";
                } else {
                    // Insérer une nouvelle ligne dans la table inscriptioncours
                    $insert = $db->prepare("INSERT INTO inscriptioncours (id_client, id_enseignement) VALUES (:id_client, :id_enseignement)");
                    $insert->execute(['id_client' => $id_client, 'id_enseignement' => $id_enseignement]);

                    // Message de succès
                    $success_message = "Inscription réussie !";

                    // Effacer les variables POST pour éviter une nouvelle soumission involontaire
                    $_POST = array();
                }
            } else {
                $error_message = "Enseignement non trouvé.";
            }
        } else {
            $error_message = "Id client non trouvé dans la session.";
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
// Requête pour récupérer la description du coach
$query = $db->prepare("SELECT description FROM coach WHERE Email = :email");
$query->execute(['email' => $email]);
$coach = $query->fetch(PDO::FETCH_ASSOC);

// Vérifier si le coach a été trouvé

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="coach.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <title>About Coach - KnoShare</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        #about-section {
            width: 100%;
            height: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 80px 12%;
        }
        .about-left img {
            width: 420px;
            height: auto;
            transform: translateY(50px);
        }
        .about-right {
            width: 54%;
        }
        .about-right ul li {
            display: flex;
            align-items: center;
        }
        .about-right h1 {
            color: rgba(221, 124, 55, 1);
            font-size: 37px;
            margin-bottom: 5px;
        }
        .about-right p {
            color: #444;
            line-height: 26px;
            font-size: 15px;
        }
        .about-right .address {
            margin: 25px 0;
        }
        .about-right .address ul li {
            margin-bottom: 5px;
        }
        .address .address-logo {
            margin-right: 15px;
            color: rgba(221, 124, 55, 1);
        }
        .address .saprater {
            margin: 0 35px;
        }
        .about-right .expertise ul {
            width: 80%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .expertise h3 {
            margin-bottom: 10px;
        }
        .expertise .expertise-logo {
            font-size: 19px;
            margin-right: 10px;
            color: rgba(221, 124, 55, 1);
        }
        #success-message, #error-message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    text-align: center; /* Centrer le texte à l'intérieur du div */
    width: 80%; /* Vous pouvez ajuster la largeur en fonction de vos besoins */
    max-width: 400px; /* Limite maximale de la largeur */
}

#success-message {
    background-color: rgba(221, 124, 55, 1);
    color: white;
}

#error-message {
    background-color: #F2A7A7;
}
        #messaging-section {
    display: none;
    position: fixed;
    bottom: 0;
    right: 0;
    width: calc(100vw / 3); /* un tiers de la largeur de l'écran */
    height: 65vh; /* 50% de la hauteur de la fenêtre */
    background-color: beige;
    border-top: 1px solid #ccc;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow-y: auto; /* Ajoute une barre de défilement verticale si nécessaire */
}
        #messages {
            max-height: 70%;
            overflow-y: auto;
        }
        #messages p {
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
        }
        #message-error {
            color: red;
        }
        .message-container {
    display: flex;
    flex-direction: column-reverse;
    height: 200px;
    overflow-y: auto;
    background: #f9f9f9;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
}

.message-left {
    align-self: flex-start;
    background: #e1ffc7;
    padding: 8px 12px;
    border-radius: 15px;
    margin: 5px 0;
}

.message-right {
    align-self: flex-end;
    background: #c7eaff;
    padding: 8px 12px;
    border-radius: 15px;
    margin: 5px 0;
}
.profile-container {
            display: flex;
            align-items: center;
        }
        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; z-index: 1;">
    <a class="navbar-brand" href="si2.php">
        <img src="images/logowhite.png" class="navbar-logo" alt="">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link " href="si2.php">Home <span class="sr-only">(current)</span></a>
            </li>
          
            <li class="nav-item">
                <a class="nav-link " href="coaching_domains.php">Coaching Service</a>
            </li>
            
        </ul>
        
        <!-- User info --> 
        <ul class="navbar-nav"></br>
        <a href="profilclient.php">
                        <img src="images/imgprofil.jpg" alt="Profile Image" class="profile-image">
                    </a>

            <li class="nav-item">
           
                <span class="nav-link"><?php echo htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) . "</br>" . htmlspecialchars($_SESSION['user']['email']); ?></span>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link" role="button" aria-pressed="true">Log out</a>
            </li>
        </ul>
    </div>
</nav>
<section id="about-section">
    <!-- about left  -->
    <div class="card" style="width: 18rem;">
        <img class="card-img-top" src="images/imgprofil.jpg" alt="Card image cap">
    </div>
    <!-- about right  -->
     
    <div class="about-right">
        <h1><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h1>
        <p><?php if ($coach) {
    $description = $coach['description'];
    echo "Description du coach : $description";
} else {
    echo "aucune description disponible.";
}?>
        </p>
        <div class="address">
            <ul>
                <li>
                    <span class="address-logo">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                </li>
                <li>
                    <span class="address-logo">
                        <i class="fas fa-phone-alt"></i>
                    </span>
                    <p>Cours</p>
                    <span class="saprater">:</span>
                    <p><?php echo htmlspecialchars($cours); ?></p>
                </li>
                <li>
                    <span class="address-logo">
                        <i class="fas fa-phone-alt"></i>
                    </span>
                    <p>Prix</p>
                    <span class="saprater">:</span>
                    <p><?php echo htmlspecialchars($prix); ?> €</p>
                </li>
                <li>
                    <span class="address-logo">
                        <i class="fas fa-phone-alt"></i>
                    </span>
                    <p>Phone No</p>
                    <span class="saprater">:</span>
                    <p><?php echo htmlspecialchars($contact); ?></p>
                </li>
                <li>
                    <span class="address-logo">
                        <i class="far fa-envelope"></i>
                    </span>
                    <p>Email Adress</p>
                    <span class="saprater">:</span>
                    <p><?php echo htmlspecialchars($email); ?></p>
                </li>
            </ul>
        </div>
        <div class="expertise">
            <form id="inscriptionForm" action="" method="post" style="display: none;">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="cours" value="<?php echo htmlspecialchars($cours); ?>">
                <input type="hidden" name="prix" value="<?php echo htmlspecialchars($prix); ?>">
                <input type="hidden" name="inscription" value="1">
            </form>
            <button id="contact" class="btn btn-primary btn-block" style="width: 50%; height: 5%; margin-left: 0%;" onclick="submitForm()">
                <img class="rounded-circle" style="width: 5%; height: 5%; margin-right: 5%; margin-bottom: 0%;"> S'inscrire
            </button>
            <button id="contact" class="btn btn-primary btn-block" style="width: 50%; height: 5%; margin-left: 0%;" onclick="toggleMessaging()">
                <img class="rounded-circle" style="width: 5%; height: 5%; margin-right: 5%; margin-bottom: 0%;"> contacter
            </button>
        </div>
    </div>
</section>

<section id="messaging-section">
<div class=" text-center">
        <button id="close-button" class="btn btn-primary btn-block"  style="background-color: rgba(221, 124, 55, 1); 
        color: rgba(34, 22, 22, 1);width: 35px; ">x</button>
        <h5 class="text-center" style="margin-top:-8%;"> Messages with <?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h5>
        </div>
        <div class="message-container" id="messages">
            <?php foreach ($messages as $message): ?>
                <div class="<?php echo htmlspecialchars($message['sender_email']) === $_SESSION['user']['email'] ? 'message-right' : 'message-left'; ?>">
                    <p><strong><?php echo htmlspecialchars($message['sender_email']); ?>:</strong> <?php echo htmlspecialchars($message['message_text']); ?> <em>(<?php echo htmlspecialchars($message['sent_at']); ?>)</em></p>
                </div>
            <?php endforeach; ?>
        </div>
        <form action="" method="post">
            <textarea name="message_text" placeholder="Type your message here..." rows="4" style="width: 100%;"></textarea>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <button type="submit" class="btn btn-primary" style="background-color: rgba(221, 124, 55, 1);">Send</button>
        </form>
        <p id="message-error"><?php echo $message_error; ?></p>
    </section>



 <script>
        function submitForm() {
            document.getElementById('inscriptionForm').submit();
        }

        function toggleMessaging() {
            var messagingSection = document.getElementById('messaging-section');
            if (messagingSection.style.display === 'none' || messagingSection.style.display === '') {
                messagingSection.style.display = 'block';
            } else {
                messagingSection.style.display = 'none';
            }
        }

        document.getElementById("close-button").addEventListener("click", function() {
            document.getElementById("messaging-section").style.display = 'none';
        });

        // Check if the messaging section should be open after sending a message
        <?php if (isset($_SESSION['open_messaging_section']) && $_SESSION['open_messaging_section']): ?>
            document.getElementById('messaging-section').style.display = 'block';
            <?php unset($_SESSION['open_messaging_section']); // Clear the session variable ?>
        <?php endif; ?>

        // Scroll to bottom function
        function scrollToBottom() {
            var messagesDiv = document.getElementById('messages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Call the scroll function after page load
        window.onload = scrollToBottom;
    </script>

    <div id="success-message" style="display: <?php echo $success_message ? 'block' : 'none'; ?>">
        <?php echo $success_message; ?>
    </div>
    <div id="error-message" style="display: <?php echo $error_message ? 'block' : 'none'; ?>">
        <?php echo $error_message; ?>
    </div>

    <script>
        // Hide success and error messages after 3 seconds
        setTimeout(function() {
            var successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
            var errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 3000);
    </script>

</body>
</html>

