<?php
session_start();

// Vérifier si les informations de l'utilisateur sont définies dans la session
if (!isset($_SESSION['user'])) {
    header('Location: log_in.php');
    exit();
}

$user = $_SESSION['user'];
// Connexion à la base de données
include 'database.php';

// Activer les exceptions PDO pour les erreurs
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Requête pour récupérer l'ID du coach
$query = $db->prepare("SELECT id_coach FROM coach WHERE Email = :email");
$query->execute(['email' => $user['email']]);
$coach = $query->fetch(PDO::FETCH_ASSOC);
$coachId = $coach['id_coach'] ?? '';
if (!empty($coachId)) {
    // Requête pour récupérer les noms des cours lancés par le coach
    $query = $db->prepare("
    SELECT c.nom_cours, c.id_cours, e.prix
    FROM enseignement e
    JOIN cours c ON e.id_cours = c.id_cours
    WHERE e.id_coach = :id_coach
");
    $query->execute(['id_coach' => $coachId]);
    $courses = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Coach non trouvé.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idCours = $_POST['id_cours'];

    // Requête pour supprimer l'enseignement
    try {
        $query = $db->prepare("DELETE FROM enseignement WHERE id_cours = :id_cours");
        $query->execute(['id_cours' => $idCours]);

        // Vérification du nombre de lignes affectées
        if ($query->rowCount() > 0) {
            // Redirection après suppression
            header("Location: coach_courses.php"); // Redirigez vers la page appropriée après suppression
            exit();
        } else {
            echo "Erreur: Le cours n'a pas été supprimé.";
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg" >
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>KnoShare</title>
    <link rel="stylesheet" 
          href="dashb.css">
    <link rel="stylesheet" 
          href="responsive.css">
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
        </style>
</head>
<body>
  
    <!-- for header part -->
    <header>
        <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: 0%; z-index: 1;">
        
            <a class="navbar-brand" href="si.html">
                <img src="images/logowhite.png" class="navbar-logo" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
          
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                
              </ul>
             
               <!-- User info -->
               <ul class="navbar-nav">
               <li class="nav-item"></br>
                        <div class="nav-profile-circle">
                        <a href="coach_profile.php">
                            <img src="images/imgprofil.jpg" alt="Profile Picture" class="img-fluid rounded-circle"></a>
                        </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link"><?php echo $user['prenom'] . ' ' . $user['nom']."</br>".$user['email']  ; ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link" role="button" aria-pressed="true">Log out</a>
                    </li>
                    
                </ul>
            </div>
          </nav>
    </header>

    <!--SIDEBAR--></br></br></br>
    <div class="wrapper">
        <aside id="sidebar" style="background-color: rgba(34, 22, 22, 1) ;  margin-top:-1%; z-index:0;">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="dashboard_coach.php" class="sidebar-link" >
                        <i class="lni lni-agenda"></i>
                        <span >DashBoard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_profile.php" class="sidebar-link" id="showSection('profile')" >
                        <i class="lni lni-agenda"></i>
                        <span >My Profile </span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_clients.php" class="sidebar-link" id="showSection('clients')">
                        <i class="lni lni-agenda"></i>
                        <span>My Clients</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_courses.php" class="sidebar-link " id="showSection('courses')">
                        <i class="lni lni-layout"></i>
                        <span>My Courses</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_messages.php" class="sidebar-link" id="showSection('messages')">
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
<!--CONTENT-->
<div class="main col-md-12 p-3">
    <div class="text-center">
        <div id="clients">
            <div class="container">
                <div class="card__container">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $cour) : ?>
                            <article class="card__article">
                                    <h3><?php echo htmlspecialchars($cour['nom_cours']); ?></h3>
                                    <h4><?php echo htmlspecialchars($cour['prix']); ?>€</h4>
                                    <form action="coach_courses.php" method="POST">
                                        <input type="hidden" name="id_cours" value="<?php echo $cour['id_cours']; ?>">
                                        <button type="submit">supprimer cours</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No courses found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="dashb.css"></script>
    <script src="dashb.js"></script>

</body>
</html>
