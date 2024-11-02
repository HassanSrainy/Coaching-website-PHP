<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="si.css">
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
<body>
<nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: -9%; z-index: 1; ">
        
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
          </form>
        </div>
      </nav>
      <div class="container mt-5">
        
        <h3  id="join_titre" class="text-center" style="margin-top:10%">For your health</h3>
        <div class="row justify-content-center">
    
            <div class="col-12 col-sm-4 role" >
              <div id="fit">
                <img src="images/fitness.png" alt="fitness">
                <p style="margin-top: 5%;">Fitness and Exercise</p></div>
            </div>
            <div class="col-12 col-sm-4 role">
              <div  id="m_h">
                <img src="images/sante_mental.png" alt="mental health" >
                <p style="margin-top: 5%;">Mental Health</p></div>
            </div>
            <div class="col-12 col-sm-4 role" >
              <div id="nutr">
                <img src="images/Nutritional.png" alt="Nutritional" >
                <p style="margin-top: 5%;">Nutritional</p></div>
            </div>
        </div>
    </div>
    <script>
      document.getElementById('fit').addEventListener('click', function() {
          window.location.href = 'fit.php'; 
      });
  </script>
    <script>
      document.getElementById('m_h').addEventListener('click', function() {
        window.location.href = 'm_h.php'; 
         });
         </script>
    <script>
      document.getElementById('nutr').addEventListener('click', function() {
         window.location.href = 'nutr.php'; 
       });
          </script>
    
</body>
</html>