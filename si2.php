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
  </head>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="boostrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="prop.css" />
    <link rel="stylesheet" href="si.css" />
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
        <nav class="navbar navbar-expand-lg container-fluid" style="position: fixed ; margin-top: -5%; z-index: 1;">
        
            <a class="navbar-brand" href="si.html">
                <img  src="images/logowhite.png" class="navbar-logo" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
            aria-controls="navbarSupportedContent" 
            aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
          
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                  <a class="nav-link " href="si.html">Home <span class="sr-only">(current)</span></a>
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
          <div class="col-12 col-md-9" style="margin-top: -2.5%;" id="text_page1">
            
            <h3 style="  margin-top: 10%;" id="titre1"> <br>Connecting Minds  Sharing Expertise </h3>            
          </div>
          <div class="container">
      <span class="letter-s">A</span>
      <img style="margin-top: 10%;" src="images/main.webp" alt="header" />
      <h4 class="text__left">CO</h4>
      <h4 class="text__right">CH</h4>
  
      <h5 class="feature-1">Service</h5>
      <h5 class="feature-2">Freelance</h5>
      <h5 class="feature-3">Learn</h5>
      <h5 class="feature-4">Coaching</h5>
    </div>
    <br><br>
        <div class="row container-fluid mx-auto " style=" margin-bottom: 50px;">
            <div class="col-sm-9">
                  <h1 id="titre3" style="margin-right: 27%;  margin-bottom: -17%; ">Our service </h1>
                    <div class="col-4 col-sm-6" id="coa" style="margin-left: 70%; margin-top: 5%;" >
                        <div id="coach" style=" width: 70%; height: 100%; ">
                            <img  src="images/coach.jpg" alt="" class="rounded-circle">
                            <p style="color: aliceblue;">coaching</p>
                            <div class="cercle">></div>
                        </div>
                        <script>
                            document.getElementById('coach').addEventListener('click', function() {
                                window.location.href = 'coaching_domains.php'; 
                            });
                        </script>
                    </div>
 
                </div>
         </div>


    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="main.js"></script>
  </body>
</html>

