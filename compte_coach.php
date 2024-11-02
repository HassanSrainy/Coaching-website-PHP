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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['specialite'])) {
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
        echo "<script>setTimeout(function(){ window.location.href = 'compte_coach.php'; }, 500);</script>";
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
    <link rel="stylesheet" href="si.css">
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <center>
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
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer les spécialités</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Si des spécialités sont trouvées, les afficher -->
                        <div class="alert alert-success" role="alert">
                            <b>Vous avez déjà choisi les spécialités suivantes :</b>
                            <ul>
                                <?php foreach ($specialites as $specialite) : ?>
                                    <li><?php echo htmlspecialchars($specialite['nom_domaine']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <b>Vous avez déjà choisi les cours suivants :</b>
                            <ul>
                                <?php foreach ($cours as $cour) : ?>
                                    <li><?php echo htmlspecialchars($cour['nom_cours']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Bouton de déconnexion -->
                    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
                </div>
            </div>
        </center>
    </div>
    <?php if (!isset($message)) : ?>
    <center>
        <div class="role" style="width: 20%;">
            <div id="coach">
                <img src="images/programer.jpg" alt="Freelancer" class="rounded-circle">
                <p>Lancer un cours</p>
            </div>
        </div>
    </center>
    <?php endif; ?>

    






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
