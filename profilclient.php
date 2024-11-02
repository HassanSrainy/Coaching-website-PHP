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

// Récupérer les informations actuelles de l'utilisateur
$query = $db->prepare("SELECT u.Email, u.Prenom, u.Nom, c.id_client FROM utilisateur u JOIN client c ON u.Email = c.Email WHERE u.Email = :email");
$query->execute(['email' => $email]);
$userInfo = $query->fetch(PDO::FETCH_ASSOC);

// Récupérer le nombre de cours et de messages
$queryClient = $db->prepare("SELECT id_client FROM client WHERE Email = :email");
$queryClient->execute(['email' => $email]);
$client = $queryClient->fetch(PDO::FETCH_ASSOC);

if ($client) {
    $id_client = $client['id_client'];

    $queryCountCourses = $db->prepare("
        SELECT COUNT(*) AS nombre_de_cours
        FROM inscriptioncours
        WHERE id_client = :id_client
    ");
    $queryCountCourses->execute(['id_client' => $id_client]);
    $resultcours = $queryCountCourses->fetch(PDO::FETCH_ASSOC);
    $nombre_de_cours = $resultcours ? $resultcours['nombre_de_cours'] : 0;
} else {
    $nombre_de_cours = 0;
}
// Email de l'utilisateur connecté
$receiver_email = $user['email'];

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
$receiver_email = $user['email'];
$queryMessages->execute(['receiver_email' => $receiver_email]);
$messages = $queryMessages->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre de messages non lus
$message_count = count($messages);
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
        .edit-form {
            display: none;
        }
    </style>
    <script>
        function showEditForm() {
            document.getElementById('edit-form').style.display = 'block';
            
            document.getElementById('view-profile').style.display = 'none';
        }
    </script>
</head>
<body>
    <!-- for header part -->
    <header>
        <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: 0%; z-index: 1;">
            <a class="navbar-brand" href="si2.php">
                <img src="images/logowhite.png" class="navbar-logo" alt="" style="margin-left:70%;">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto" style="margin-left:10%;">
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
    </header>

    <!--SIDEBAR-->
    <div class="wrapper" style="z-index: 2;" >
    <aside id="sidebar" style="background-color: rgba(34, 22, 22, 1) ;  margin-top:5%; z-index:0;">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
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
        
        <div class="main p-3" style="margin-top: 6%;">
            <div class="text-center">
                <div class="box-container">
                    <div id="coursesDiv" class="box box2">
                        <div class="text">
                            <h2 class="topic-heading"><?php echo $nombre_de_cours; ?></h2>
                            <h2 class="topic">Courses</h2>
                        </div>
                        <img src="images/homework.png" alt="likes">
                    </div>
                    <div id="messagesDiv" class="box box4">
                        <div class="text">
                            <h2 class="topic-heading"><?php echo $message_count; ?></h2>
                            <h2 class="topic">Messages</h2>
                        </div>
                        <img src="images/chat.png" alt="message">
                    </div>
                </div>
            </div>
            <!-- PROFILE VIEW AND EDIT -->
            <div class="container py-5">
                <div id="view-profile" class="row mx-auto text-center">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <div class="d-flex justify-content-center">
                                    
                                    <img src="images/imgprofil.jpg" alt="Profile Image" class="profile-image2">
                                </div>
                                <h5 class="my-3"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                                <button >Edit Photo</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">First Name</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($userInfo['Prenom']); ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Last Name</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($userInfo['Nom']); ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Email</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($userInfo['Email']); ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <button onclick="showEditForm()" class="btn btn-info">Edit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EDIT FORM -->
                <div id="edit-form" class="row mx-auto text-center edit-form" >
                    <div class="col-md-4" >
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="images/imgprofil.jpg" alt="Profile Image" class="profile-image2">
                                </div>
                                <h5 class="my-3"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7" style="margin-left:33%; margin-top:-15.5%;">
                        <div class="card mb-4">
                            <div class="card-body">
                                <form action="update_profile.php" method="POST">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">First Name</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <input style=" background-color:aliceblue" type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($userInfo['Prenom']); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Last Name</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <input style=" background-color:aliceblue" type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($userInfo['Nom']); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Email</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <input style=" background-color:aliceblue" type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userInfo['Email']); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <button type="submit" class="btn btn-info">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="dashb.js"></script>   
     <script>
    document.getElementById('coursesDiv').addEventListener('click', function() {
        window.location.href = 'cours_clients.php';
    });
    document.getElementById('messagesDiv').addEventListener('click', function() {
        window.location.href = 'clients_messages.php';
    });
</script>

</body>
</html>
