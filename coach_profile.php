<?php
session_start();

// Vérifier si les informations de l'utilisateur sont définies dans la session
if (!isset($_SESSION['user'])) {
    header('Location: log_in.php');
    exit();
}

$user = $_SESSION['user'];
$email = $user['email'];

// Connexion à la base de données
include 'database.php';

// Requête SQL pour récupérer le contact du coach et l'ID du coach
$query = $db->prepare("SELECT id_coach, contact, description FROM coach WHERE Email = :email");
$query->execute(['email' => $email]);

// Récupérer le résultat
$coach = $query->fetch(PDO::FETCH_ASSOC);
$coachId = $coach['id_coach'] ?? '';
$contact = $coach['contact'] ?? '';
$description = $coach['description'] ?? '';

// Vérifier si le coach a des spécialités enregistrées
$querySpecialite = $db->prepare("
SELECT Domaines.nom_domaine
FROM specialitecoach
JOIN Domaines ON specialitecoach.id_domaine = Domaines.id_domaine
WHERE specialitecoach.id_coach = :coachId
");
$querySpecialite->execute(['coachId' => $coachId]);
$specialites = $querySpecialite->fetchAll(PDO::FETCH_ASSOC);
// Traitement de la suppression d'une spécialité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_specialite'])) {
    $specialiteId = $_POST['specialite_id'];
    $deleteQuery = $db->prepare("DELETE FROM specialitecoach WHERE id_coach = :coachId AND id_domaine = :specialiteId");
    $deleteQuery->execute(['coachId' => $coachId, 'specialiteId' => $specialiteId]);

    // Rafraîchir la page pour mettre à jour la liste des spécialités
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>KnoShare</title>
    <link rel="stylesheet" href="dashb.css">
    <link rel="stylesheet" href="responsive.css">
    <style>
        .content-section {
            display: none;
        }
        .content-section:target {
            display: block;
        }
    </style>
    <script>
        function toggleEditForm() {
            const form = document.getElementById('editForm');
            const profileInfo = document.getElementById('profileInfo');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            profileInfo.style.display = profileInfo.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

      <!-- for header part -->
      <header>
    <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: 0%; z-index: 1;">
        <a class="navbar-brand" href="si.html">
            <img src="images/logowhite.png" class="navbar-logo" alt="" style="margin-left:70%;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            
          </ul>
        
           <ul class="navbar-nav" style="margin-left:95%;">
           <li class="nav-item" style="margi-top:-20%;">
                    <div class="nav-profile-circle"  >
                    <a href="coach_profile.php">
                            <img src="images/imgprofil.jpg" alt="Profile Picture" class="img-fluid rounded-circle" ></a>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <span class="nav-link"><?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']) . "</br>" . htmlspecialchars($user['email']); ?></span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link" role="button" aria-pressed="true">Log out</a>
                </li>
                
            </ul>
        </div>
      </nav>
</header>

    <!--SIDEBAR--></br></br></br>
    <div class="wrapper" >
        <aside id="sidebar"  style="background-color: rgba(34, 22, 22, 1) ;  margin-top:-1%; z-index:0;">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-grid-alt"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="dashboard_coach.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>DashBoard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_profile.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_clients.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>My Clients</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_courses.php" class="sidebar-link">
                        <i class="lni lni-layout"></i>
                        <span>My Courses</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_messages.php" class="sidebar-link">
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

        <!--PROFILE+EDIT-->
        <div class="container py-5">
            <div class="row mx-auto text-center">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center">
                                <img src="images/imgprofil.jpg" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
                            </div>
                            <div class="d-flex justify-content-center mb-2">
                                <button type="button" class="btn btn-primary" onclick="toggleEditForm()">Edit</button>
                            </div>
                            <h5 class="my-3"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                            <p class="text-muted mb-1">Coach of</p>
                            <p class="text-muted mb-4">
                                <?php 
                                if (!empty($specialites)) {
                                    foreach ($specialites as $specialite) {
                                        echo htmlspecialchars($specialite['nom_domaine']) . '<br>';
                                    }
                                } else {
                                    echo 'No Speciality';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div id="profileInfo" class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Full Name</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Email</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Contact</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($contact) ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Description</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($description) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="editForm" class="card mb-4" style="display: none;">
                        <div class="card-body">
                            <form action="update_profile_coach.php" method="post">
                                <div class="row mb-3">
                                    <label for="prenom" class="col-sm-3 col-form-label">First Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']) ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="nom" class="col-sm-3 col-form-label">Last Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']) ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="email" class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']) ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="contact" class="col-sm-3 col-form-label">Contact</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="contact" name="contact" value="<?php echo htmlspecialchars($contact) ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="description" class="col-sm-3 col-form-label">Description</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($description) ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="dashb.js"></script>


    <!-- for javascript -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".toggle-btn").click(function() {
                $("#sidebar").toggleClass("active");
            });
        });
    </script>
</body>
</html>
