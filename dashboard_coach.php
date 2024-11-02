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

// Récupérer l'ID du coach à partir de l'email
$query = $db->prepare("SELECT id_coach FROM coach WHERE Email = :email");
$query->execute(['email' => $user['email']]);
$coach = $query->fetch();

if ($coach) {
    $coachId = $coach['id_coach'];

    // Traitement des données du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['specialite']) && !empty($_POST['specialite'])) {
            $specialites = $_POST['specialite'];
            $domaineIds = [
                'Self Improvement' => 4,
                'Business' => 5,
                'Health' => 6
            ];

            // Insérer les spécialités dans la table `specialitecoach`
            $query = $db->prepare("INSERT INTO specialitecoach (id_coach, id_domaine) VALUES (:coachId, :domaineId)");
            foreach ($specialites as $specialite) {
                if (array_key_exists($specialite, $domaineIds)) {
                    $query->execute(['coachId' => $coachId, 'domaineId' => $domaineIds[$specialite]]);
                }
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['course_name']) && !empty($_POST['course_name']) && isset($_POST['course_price']) && !empty($_POST['course_price'])) {
                $courseName = $_POST['course_name'];
                $coursePrice = $_POST['course_price'];
        
                // Vérifiez si le cours existe déjà
                $querySelectCourse = $db->prepare("SELECT id_cours FROM cours WHERE nom_cours = :courseName");
                $querySelectCourse->execute(['courseName' => $courseName]);
                $course = $querySelectCourse->fetch(PDO::FETCH_ASSOC);
        
                if ($course) {
                    $courseId = $course['id_cours'];
        
                    // Insérez le cours dans la table `enseignement`
                    $queryInsertEnseignement = $db->prepare("INSERT INTO enseignement (id_coach, id_cours, prix) VALUES (:coachId, :courseId, :coursePrice)");
                    $queryInsertEnseignement->execute(['coachId' => $coachId, 'courseId' => $courseId, 'coursePrice' => $coursePrice]);
        
                    // Redirigez vers la page du compte coach après la soumission
                    echo "<script>setTimeout(function(){ window.location.href = 'dashboard_coach.php'; }, 500);</script>";
                    exit();
                } else {
                    echo "<script>alert('Le cours n\'existe pas.');</script>";
                }
            }
        }
        

    // Vérifier si le coach a des spécialités enregistrées
    $querySpecialite = $db->prepare("
        SELECT Domaines.nom_domaine
        FROM specialitecoach
        JOIN Domaines ON specialitecoach.id_domaine = Domaines.id_domaine
        WHERE specialitecoach.id_coach = :coachId
    ");
    $querySpecialite->execute(['coachId' => $coachId]);
    $specialites = $querySpecialite->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les cours du coach
    $queryCours = $db->prepare("
        SELECT cours.*
        FROM cours
        INNER JOIN enseignement ON cours.id_cours = enseignement.id_cours
        WHERE enseignement.id_coach = :coachId
    ");
    $queryCours->execute(['coachId' => $coachId]);
    $cours = $queryCours->fetchAll(PDO::FETCH_ASSOC);

    // Si aucune spécialité n'est trouvée, afficher un message pour choisir les spécialités
    if (empty($specialites)) {
        $message = "Vous n'avez pas encore choisi de spécialité. Veuillez choisir parmi les options suivantes : Self Improvement, Business, Health.";
    }
} }else {
    $message = "Aucun coach trouvé pour cet email.";
}

// Compter le nombre d'enseignements lancés par ce coach
$queryEnseignements = $db->prepare("SELECT COUNT(*) AS total_enseignements FROM enseignement WHERE id_coach = :coachId");
$queryEnseignements->execute(['coachId' => $coachId]);
$result = $queryEnseignements->fetch();
$totalEnseignements = $result['total_enseignements'];

// Compter le nombre de clients inscrits dans les enseignements lancés par ce coach
$queryClients = $db->prepare("
    SELECT COUNT(DISTINCT inscriptioncours.id_client) AS nombre_clients
    FROM inscriptioncours
    JOIN enseignement ON inscriptioncours.id_Enseignement = enseignement.id_Enseignement
    WHERE enseignement.id_coach = :coachId
");
$queryClients->execute(['coachId' => $coachId]);
$resultClients = $queryClients->fetch();
$nombreClients = $resultClients['nombre_clients'];

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
    <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ;  z-index: 1;">
            <a class="navbar-brand" href="si.html">
                <img src="images/logowhite.png" class="navbar-logo" alt="" style="margin-left:70%;">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto" style="margin-left:10%;">
                    <li class="nav-item active">
                        <a class="nav-link"> <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"></a>
                    </li>
                   
                </ul>
              
               <!-- User info -->
               <ul class="navbar-nav">
               <li class="nav-item"></br>
                        <div class="nav-profile-circle">
                        <a href="coach_profile.php">
                            <img src="images/imgprofil.jpg" alt="Profile Picture" class="img-fluid rounded-circle"></a>
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
                    <a href="dashboard_coach.php" class="sidebar-link" >
                        <i class="lni lni-agenda"></i>
                        <span >DashBoard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_profile.php" class="sidebar-link"  >
                        <i class="lni lni-agenda"></i>
                        <span >My Profile </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="coach_clients.php" class="sidebar-link" >
                        <i class="lni lni-agenda"></i>
                        <span>My Clients</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="coach_courses.php" class="sidebar-link ">
                        <i class="lni lni-layout"></i>
                        <span>My Courses</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="coach_messages.php" class="sidebar-link" >
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
        <div class="main p-3" style="margin-top:8%; position:relative;">
            <div class="text-center">
                <div class="box-container">
                    <div id="clientsDiv"class="box box1">
                        <div class="text">
                        <h2 class="topic-heading"><?php echo $nombreClients; ?></h2>

                            <h2 class="topic">Clients</h2>
                        </div>
    
                        <img src="images/costumer.png"
                            alt="Views">
                    </div>
                    <div id="coursesDiv" class="box box2">
                        <div class="text">
                            <h2 class="topic-heading"><?php echo $totalEnseignements ;?></h2>
                            <h2 class="topic">Courses</h2>
                        </div>
    
                        <img src="images/homework.png" 
                             alt="likes">
                    </div>
    
                   
    
                    <div id="messagesDiv"class="box box4">
                        <div class="text">
                            <h2 class="topic-heading"><?php echo $message_count ; ?></h2>
                            <h2 class="topic">Messages</h2>
                        </div>
    
                        <img src="images/chat.png" alt="message">
                    </div>
                </div>
                <!--ADD COURSE and Speciality-->
<div class="row">
<div class="col-md-6">
    <div class="card" style="width: 5000;">
        <div class="card-body">
            <h5 class="card-title">Add Course</h5>
            <form method="post" action="">
        <div class="form-group">
        <label for="course_name">Course Name:</label>
    <select style=" background-color:aliceblue" id="course_name" name="course_name" class="form-control" required>
        <option value="Relationships and Social Skills">Relationships and Social Skills</option>
        <option value="Education and Continuous Learning">Education and Continuous Learning</option>
        <option value="Personal Development">Personal Development</option>
        <option value="Entrepreneurship">Entrepreneurship</option>
        <option value="Marketing and Sales">Marketing and Sales</option>
        <option value="International Development">International Development</option>
        <option value="Fitness and Exercise">Fitness and Exercise</option>
        <option value="Mental Health">Mental Health</option>
        <option value="Nutritional">Nutritional</option>
    </select>
        </div>
        <div class="form-group">
            <label for="course_price">Course Price:</label>
            <input style=" background-color:aliceblue" type="number" id="course_price" name="course_price" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="course_domaine">Course Domain:</label>
            <select style=" background-color:aliceblue" id="course_domaine" name="course_domaine" class="form-control" required>
                <option value="4">Self Improvement</option>
                <option value="5">Business</option>
                <option value="6">Health</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
        </div>
    </div>
</div>
                <div class="col-md-6">
                    <div class="card" style="width: 5000;">
                        <div class="card-body">
                          <h5 class="card-title">Add Speciality</h5>
                          <form action="" method="post" id="specialitesForm">
                                <div class="form-group">
                                    <label for="specialite1">Choisir une spécialité :</label>
                                    <select style=" background-color:aliceblue" class="form-control" id="specialite1" name="specialite[]" required>
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="specialite2">Choisir une autre spécialité (optionnelle) :</label>
                                    <select style=" background-color:aliceblue" class="form-control" id="specialite2" name="specialite[]">
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="specialite3">Choisir une autre spécialité (optionnelle) :</label>
                                    <select style=" background-color:aliceblue" class="form-control" id="specialite3" name="specialite[]">
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer les spécialités</button>
                            </form>
                        </div>
                      </div>
                </div>
            </div>
            <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selects = document.querySelectorAll('select[name="specialite[]"]');
            selects.forEach(select => {
                select.addEventListener('change', function () {
                    const selectedValues = Array.from(selects).map(s => s.value);
                    selects.forEach(s => {
                        Array.from(s.options).forEach(option => {
                            if (selectedValues.includes(option.value) && option.value !== s.value) {
                                option.disabled = true;
                            } else {
                                option.disabled = false;
                            }
                        });
                    });
                });
            });
        });
    </script>
 <script>
    document.getElementById('coursesDiv').addEventListener('click', function() {
        window.location.href = 'coach_courses.php';
    });
    document.getElementById('messagesDiv').addEventListener('click', function() {
        window.location.href = 'coach_messages.php';
    });
    document.getElementById('clientsDiv').addEventListener('click', function() {
        window.location.href = 'coach_clients.php';
    });
</script>
    
        <script src="dashb.js"></script>
                </div>
            </div>
            </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="dashb.css"></script>

</body>
</html>
