<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
$email = $user['email'];

// Connexion à la base de données
include 'database.php';

// Récupérer l'id_client à partir de l'email
$queryClient = $db->prepare("SELECT id_client FROM client WHERE Email = :email");
$queryClient->execute(['email' => $email]);
$client = $queryClient->fetch(PDO::FETCH_ASSOC);

if ($client) {
    $id_client = $client['id_client'];

    // Compter le nombre de cours auxquels le client est inscrit
    $queryCountCourses = $db->prepare("
        SELECT COUNT(*) AS nombre_de_cours
        FROM inscriptioncours
        WHERE id_client = :id_client
    ");
    $queryCountCourses->execute(['id_client' => $id_client]);
    $resultcours = $queryCountCourses->fetch(PDO::FETCH_ASSOC);

    if ($resultcours) {
        $nombre_de_cours = $resultcours['nombre_de_cours'];
    } else {
        $nombre_de_cours = 0; // Valeur par défaut en cas d'erreur
    }
} else {
    $nombre_de_cours = 0; // Valeur par défaut si le client n'est pas trouvé
}

// Email de l'utilisateur connecté
$receiver_email = $user['email'];

// Requête pour récupérer les messages reçus auxquels l'utilisateur n'a pas répondu
$queryMessages = $db->prepare("
    SELECT m1.*, utilisateur.Prenom, utilisateur.Nom 
    FROM messages m1
    JOIN utilisateur ON m1.sender_email = utilisateur.Email
    WHERE m1.receiver_email = :receiver_email
    AND NOT EXISTS (
        SELECT 1
        FROM messages m2
        WHERE m2.sender_email = :receiver_email
        AND m2.receiver_email = m1.sender_email
        AND m2.sent_at > m1.sent_at
    )
    ORDER BY m1.sent_at DESC
");
$queryMessages->execute(['receiver_email' => $receiver_email]);
$messages = $queryMessages->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>KnoShare</title>
    <link rel="stylesheet" href="dashb.css">
    <link rel="stylesheet" href="responsive.css">
    <style>
        /* Your CSS styles here */
        .content-section {
            display: none;
        }
        .content-section:target {
            display: block;
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
        .profile-image2 {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 10px;
    
        }
    </style>
</head>
<body>
    <!-- for header part -->
    
    <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: 0%; z-index: 1; ">
        
        
            <a class="navbar-brand" href="si2.php">
                <img src="images/logowhite.png" class="navbar-logo" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="si2.php">Home <span class="sr-only">(current)</span></a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="coaching_domains.php">Coaching Service</a>
                    </li>
                  
                </ul>
                
                <!-- User info -->
                <ul class="navbar-nav">
                    <li class="nav-item profile-container">
                        <a href="profilclient.php">
                            <img src="images/imgprofil.jpg" alt="Profile Image" class="profile-image">
                        </a>
                        <span class="nav-link"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . "<br>" . htmlspecialchars($user['email']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link" role="button" aria-pressed="true">Log out</a>
                    </li>
                </ul>
            </div>
        </nav>
    

    <!-- SIDEBAR -->
    <div class="wrapper" style="z-index: 2;">
        <aside id="sidebar" style="background-color: rgba(34, 22, 22, 1) ;  margin-top:5%; z-index:0;">
            <br><br><br>
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="profilclient.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>DashBoard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="cours_clients.php" class="sidebar-link">
                        <i class="lni lni-layout"></i>
                        <span>My Courses</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="clients_messages.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Messages</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="si.html" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        <div class="main p-3"></br></br></br>
            <?php foreach ($messages as $message): ?>
                <div class="message-block">
                    <h5><?php echo htmlspecialchars($message['Prenom'] . ' ' . $message['Nom']); ?></h5>
                    <p><?php echo htmlspecialchars($message['message_text']); ?></p>
                    <form action="send_messagecl.php" method="post">
                        <input type="hidden" name="receiver_email" value="<?php echo htmlspecialchars($message['sender_email']); ?>">
                        <textarea name="message_text" rows="3" class="form-control" placeholder="Write your reply here..."></textarea>
                        <button type="submit" class="btn btn-primary mt-2">Send</button>
                    </form>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="dashb.js"></script>   

</body>
</html>
