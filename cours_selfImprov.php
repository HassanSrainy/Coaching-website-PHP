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
              <a class="nav-link " href="coaching_domains.php">Coaching Services</a>
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
      <div class="container mt-5" >
        
        <h3  id="join_titre" class="text-center" style="margin-top:10%;">For your self Improvement</h3>
        <div class="row justify-content-center" style="margin-left: 1%;">
    
            <div class="col-12 col-sm-4 role" >
              <div id="skills" style="width: 90%;">
                <img src="images/relationship.png" alt="it">
                <p style="margin-top: 5%;">Relationships and Social Skills</p></div>
            </div>
            <div class="col-12 col-sm-4 role">
              <div  id="education" style="width: 90%;" >
                <img src="images/learning.png" alt="ai" >
                <p style="margin-top: 5%;">Education and Continuous Learning</p></div>
            </div>
            <div class="col-12 col-sm-4 role" >
              <div id="personal" style="width: 90%;">
                <img src="images/dev_perso.png" alt="Design" >
                <p style="margin-top: 5%;">Personal Development</p></div>
            </div>
        </div>
    </div>
    <script>
      document.getElementById('education').addEventListener('click', function() {
          window.location.href = 'EducationandContinuous.php'; 
      });
  </script>
    <script>
      document.getElementById('personal').addEventListener('click', function() {
        window.location.href = 'PersonalDevelopment.php'; 
         });
         </script>
    <script>
      document.getElementById('skills').addEventListener('click', function() {
         window.location.href = 'RelationshipsandSocialSkills.php'; 
       });
          </script>
</body>
</html>