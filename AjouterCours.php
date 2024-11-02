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

    // Traitement des données du formulaire de spécialités
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['specialite'])) {
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
        
        // Rafraîchir la page après soumission du formulaire
        echo "<script>setTimeout(function(){ window.location.href = 'coach_account.php'; }, 500);</script>";
    }

    // Vérifier si le coach a des spécialités enregistrées
    $querySpecialite = $db->prepare("
        SELECT Domaines.nom_domaine, Domaines.id_domaine
        FROM specialitecoach
        JOIN Domaines ON specialitecoach.id_domaine = Domaines.id_domaine
        WHERE specialitecoach.id_coach = :coachId
    ");
    $querySpecialite->execute(['coachId' => $coachId]);
    $specialites = $querySpecialite->fetchAll(PDO::FETCH_ASSOC);

    // Si aucune spécialité n'est trouvée, afficher un message pour choisir les spécialités
    if (empty($specialites)) {
        $message = "Vous n'avez pas encore choisi de spécialité. Veuillez choisir parmi les options suivantes : Self Improvement, Business, Health.";
    } else {
        // Récupérer les cours disponibles pour les spécialités du coach
        $domaineIds = array_column($specialites, 'id_domaine');
        $queryCourses = $db->prepare("
            SELECT * FROM cours
            WHERE id_domaine IN (" . implode(',', $domaineIds) . ")
        ");
        $queryCourses->execute();
        $courses = $queryCourses->fetchAll(PDO::FETCH_ASSOC);
    }

    // Traitement des données du formulaire d'enseignement
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cours']) && isset($_POST['prix'])) {
        $cours = $_POST['cours'];
        $prix = $_POST['prix'];

        // Vérifier que le nombre de cours est entre 1 et 9
        if (count($cours) >= 1 && count($cours) <= 9) {
            // Insérer les cours dans la table `enseignement`
            $queryCheck = $db->prepare("SELECT COUNT(*) FROM enseignement WHERE id_coach = :coachId AND id_cours = :coursId");
            $queryInsert = $db->prepare("INSERT INTO enseignement (id_coach, id_cours, prix) VALUES (:coachId, :coursId, :prix)");
            
            foreach ($cours as $coursId) {
                // Vérifier si le cours a déjà été lancé plus de deux fois par le coach
                $queryCheck->execute(['coachId' => $coachId, 'coursId' => $coursId]);
                $count = $queryCheck->fetchColumn();
                
                if ($count < 2) {
                    $queryInsert->execute(['coachId' => $coachId, 'coursId' => $coursId, 'prix' => $prix]);
                } else {
                    echo "<script>alert('Le cours ID $coursId a déjà été lancé plus de deux fois par ce coach.');</script>";
                }
            }

            // Rediriger vers la page du compte coach après la soumission
            echo "<script>setTimeout(function(){ window.location.href = 'compte_coach.php'; }, 500);</script>";
            exit();
        } else {
            echo "<script>alert('Vous devez choisir entre 1 et 9 cours.');</script>";
        }
    }
} else {
    $message = "Aucun coach trouvé pour cet email.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Coach</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <link rel="stylesheet" href="si.css">
    <link rel="stylesheet" href="coach.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .alert {
            margin-top: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
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
<nav class="navbar navbar-expand-lg container-fluid"  >
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
                <a class="nav-link " href="coaching_domains.php">coaching</a>
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
    <div class="container mt-5" style="margin-top: 10%;">
        <cemter>
            <div class="card">
                <div class="card-body">
                    <h2>Welcome, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>!</h2>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

                    <?php if (isset($message)) : ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo $message; ?>
                            <form action="" method="post" id="specialitesForm">
                                <div class="form-group">
                                    <label for="specialite1">Choisir une spécialité :</label>
                                    <select class="form-control" id="specialite1" name="specialite[]" required>
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="specialite2">Choisir une autre spécialité (optionnelle) :</label>
                                    <select class="form-control" id="specialite2" name="specialite[]">
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="specialite3">Choisir une autre spécialité (optionnelle) :</label>
                                    <select class="form-control" id="specialite3" name="specialite[]">
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value="Self Improvement">Self Improvement</option>
                                        <option value="Business">Business</option>
                                        <option value="Health">Health</option>
                                        <option value ="">
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer les spécialités</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Si des spécialités sont trouvées, les afficher -->
                        <div class="alert alert-success" role="alert">
                            Vous avez déjà choisi les spécialités suivantes :
                            <ul>
                                <?php foreach ($specialites as $specialite) : ?>
                                    <li><?php echo htmlspecialchars($specialite['nom_domaine']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                    <?php endif; ?>
                    
                    <!-- Bouton de déconnexion -->
                    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
                </div>
            </div>
        </cemter>
    </div><cemter>
    <!-- Formulaire pour lancer un enseignement -->
    <div class="card mt-4">
                            <div class="card-body">
                                <h4>Lancer un enseignement</h4>
                                <form action="" method="post">
                                    <div class="form-group">
                                        <label for="cours">Choisir des cours (de 1 à 9) :</label>
                                        <select class="form-control" id="cours" name="cours[]" multiple required>
                                            <?php foreach ($courses as $course) : ?>
                                                <option value="<?php echo htmlspecialchars($course['id_cours']); ?>">
                                                    <?php echo htmlspecialchars($course['nom_cours']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="prix">Prix :</label>
                                        <select class="form-control" id="specialite3" name="prix" required>
                                        <option value="" disabled selected>Choisir...</option>
                                        <option value=100>100</option>
                                        <option value=200>200</option>
                                        <option value=300>300</option>
                                        <option value =400>400</option>
                                        <option value=500>100</option>
                                        <option value=600>200</option>
                                        <option value=700>300</option>
                                        <option value =800>400</option>
                                        <option value=900>100</option>
                                        <option value=1000>200</option>
                                       
                                    </select>
                                        
                                    </div>
                                    <button type="submit" class="btn btn-primary">Lancer l'enseignement</button>
                                </form>
                            </div>
                        </div></center>

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
</body>
</html>
