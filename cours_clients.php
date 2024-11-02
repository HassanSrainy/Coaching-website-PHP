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
include 'database.php';

// Récupérer l'id_client à partir de l'email
$queryClient = $db->prepare("SELECT id_client FROM client WHERE Email = :email");
$queryClient->execute(['email' => $email]);
$client = $queryClient->fetch(PDO::FETCH_ASSOC);

$courses = [];
if ($client) {
    $id_client = $client['id_client'];

    // Requête pour récupérer les cours et les informations du coach
    $queryCourses = $db->prepare("
        SELECT 
            c.nom_cours,
            u.Prenom AS prenom_coach,
            u.Nom AS nom_coach,
            u.Email AS email_coach
        FROM 
            inscriptioncours AS ic
        INNER JOIN 
            enseignement AS e ON ic.id_Enseignement = e.id_Enseignement
        INNER JOIN 
            cours AS c ON e.id_cours = c.id_cours
        INNER JOIN 
            coach AS co ON e.id_coach = co.id_coach
        INNER JOIN 
            utilisateur AS u ON co.Email = u.Email
        WHERE 
            ic.id_client = :id_client
    ");
    
    $queryCourses->execute(['id_client' => $id_client]);
    $courses = $queryCourses->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Client non trouvé.";
    exit();
}
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
        /* Hide all content sections by default */
        .content-section {
            display: none;
        }
        /* Show the content section that matches the targeted anchor */
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
    <header>
        <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: 0%; z-index: 1;">
            <a style="margin-left:5%" class="navbar-brand" href="si2.php">
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
                <ul class="navbar-nav"></br></br>
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
    <div class="wrapper"  style="z-index: 2;">
        </br></br></br>
        <aside id="sidebar" style="background-color: rgba(34, 22, 22, 1) ;  margin-top:5%; z-index:0;">
            </br></br></br>
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
        <div>
            
        </div>
        <!--CONTENT-->
        
<div class="main col-md-12 p-3" style="margin-top:6%;">
    <div class="text-center">
        <div id="clients">
            <div class="container">
                <div class="card__container">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $cour) : ?>
                <article class="card__article">
                    <img src="images/imgprofil.jpg" alt="image" class="card__img">
                    <div class="card__data">
                        <h3><?php echo htmlspecialchars($cour['nom_cours']); ?></h3>
                        <p>Prénom du coach: <?php echo htmlspecialchars($cour['prenom_coach']); ?></p>
                        <p>Nom du coach: <?php echo htmlspecialchars($cour['nom_coach']); ?></p>
                        <p>Email du coach: <?php echo htmlspecialchars($cour['email_coach']); ?></p>
                      
                        <div style="display: flex;">
                        <a style="margin-right:5%;" href="about_coach.php?prenom=<?php echo urlencode($cour['prenom_coach']); ?>&nom=<?php echo urlencode($cour['nom_coach']); ?>&email=<?php echo urlencode($cour['email_coach']); ?>&cours=<?php echo urlencode($cour['nom_cours']); ?>" 
                        class="btn btn-primary btn-block" style="width: 50%; height: 50%;
                         margin-top: 0%;">Contact</a>

                        </div>
                        
                    </div>
                </article>
            <?php endforeach; ?>
                    <?php else: ?>
                        <p>No courses found.</p>
                    <?php endif; ?></br></br>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="dashb.js"></script>
            
    

</body>
</html>
