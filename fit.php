<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
// Inclure le fichier de configuration de la base de données
include 'database.php';

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];

// Requête SQL pour récupérer les coachs qui ont lancé un enseignement pour le cours "Relationships and Social Skills"
$query = $db->prepare("
    SELECT utilisateur.prenom, utilisateur.nom, utilisateur.email, cours.nom_cours, enseignement.prix
    FROM enseignement
    JOIN coach ON enseignement.id_coach = coach.id_coach
    JOIN cours ON enseignement.id_cours = cours.id_cours
    JOIN utilisateur ON coach.email = utilisateur.email
    WHERE cours.nom_cours = 'Fitness and Exercise'
");
$query->execute();
$coaches = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="coach.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg" >
    <title>KnoShare</title>
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
    </style>
</head>
<body><nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: -4%; z-index: 1; ">
        
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

    <div class="container mt-5"></br></br>
        <h3 id="join_titre" class="text-center">Fitness and Exercise</h3>
        <div class="card__container">
            <?php foreach ($coaches as $coach) : ?>
                <article class="card__article">
                    <img src="images/imgprofil.jpg" alt="image" class="card__img">
                    <div class="card__data">
                        <h3><?php echo htmlspecialchars($coach['nom_cours']); ?></h3>
                        <span class="card__description"><?php echo htmlspecialchars($coach['prix']); ?> €</span>
                        <h2 class="card__title"><?php echo htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']); ?></h2>
                        <div style="display: flex;">

                            <a style="margin-left:5%;" href="about_coach.php?prenom=<?php echo urlencode($coach['prenom']); ?>&nom=<?php echo urlencode($coach['nom']); ?>&email=<?php echo urlencode($coach['email']); ?>&cours=<?php echo urlencode($coach['nom_cours']); ?>&prix=<?php echo urlencode($coach['prix']); ?>" class="btn btn-primary btn-block" style="width: 50%; height: 5%; margin-top: 0%;">
                                <img class="rounded-circle" style="width: 5%; height: 5%; margin-right: 5%; margin-bottom: 0%;"> Contact
                            </a>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>