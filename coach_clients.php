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

// Requête pour récupérer l'ID du coach
$query = $db->prepare("SELECT id_coach FROM coach WHERE Email = :email");
$query->execute(['email' => $user['email']]);
$coach = $query->fetch(PDO::FETCH_ASSOC);
$coachId = $coach['id_coach'] ?? '';

if (!$coachId) {
    echo "Coach ID not found.";
    exit();
}

// Requête pour récupérer les clients inscrits dans les cours du coach
$queryClients = $db->prepare("
SELECT u.Prenom, u.Nom, u.Email, c.nom_cours
FROM utilisateur u
JOIN client cl ON u.Email = cl.Email
JOIN inscriptioncours ic ON cl.id_client = ic.id_client
JOIN enseignement e ON ic.id_Enseignement = e.id_Enseignement
JOIN cours c ON e.id_cours = c.id_cours
WHERE e.id_coach = :coachId
");
$queryClients->execute(['coachId' => $coachId]);
$clients = $queryClients->fetchAll(PDO::FETCH_ASSOC);

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
        #messages {
            max-height: 70%;
            overflow-y: auto;
        }
        #messages p {
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
        }
        #message-error {
            color: red;
        }
        .message-container {
            display: flex;
            flex-direction: column-reverse;
            height: 200px;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .message-left {
            align-self: flex-start;
            background: #e1ffc7;
            padding: 8px 12px;
            border-radius: 15px;
            margin: 5px 0;
        }
        .message-right {
            align-self: flex-end;
            background: #c7eaff;
            padding: 8px 12px;
            border-radius: 15px;
            margin: 5px 0;
        }
        #messaging-section {
            display: none;
            position: fixed;
            bottom: 0;
            right: 0;
            width: calc(100vw / 3);
            height: 65vh;
            background-color: #fff;
            border-top: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
    </style>
</head>
<body>
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
        
           <ul class="navbar-nav">
           <li class="nav-item"></br>
                    <div class="nav-profile-circle">
                    <a href="coach_profile.php">
                            <img src="images/imgprofil.jpg" alt="Profile Picture" class="img-fluid rounded-circle"></a>
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
    </br></br></br></br>
<div class="wrapper">
    <aside id="sidebar" style="background-color: rgba(34, 22, 22, 1) ;  margin-top:-2.5%; z-index:0;">
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
                    <span>My Profile </span>
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

    <div class="main col-md-12 p-3">
        <div class="text-center">
            <div id="clients">
                <div class="container">
                    <div class="card__container">
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $client) : ?>
                                <article class="card__article">
                                    <img src="images/imgprofil.jpg" alt="image" class="card__img">
                                    <div class="card__data">
                                        <h3><?php echo htmlspecialchars($client['nom_cours']); ?></h3>
                                        <h2 class="card__title"><?php echo htmlspecialchars($client['Prenom']) . ' ' . htmlspecialchars($client['Nom']); ?></h2>
                                        <div style="display: flex;">
                                            <button id="contact" class="btn btn-primary btn-block" style="width: 50%; height: 5%;" onclick="openMessagingSection('<?php echo htmlspecialchars($client['Prenom']); ?>', '<?php echo htmlspecialchars($client['Nom']); ?>', '<?php echo htmlspecialchars($client['Email']); ?>')">
                                                <img class="rounded-circle" style="width: 5%; height: 5%; margin-right: 5%; margin-bottom: 0%;"> contacter
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No clients found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section de messagerie -->
<section id="messaging-section">
    <button id="close-button" style="background-color: red; color: white;">Fermer</button>
    <h2>Messages with <span id="client-name"></span></h2>
    <div class="message-container" id="messages">
        <!-- Affichage des messages -->
    </div>
    <form id="message-form">
        <textarea name="message_text" id="message_text" placeholder="Type your message here..." rows="4" style="width: 100%;"></textarea>
        <input type="hidden" name="receiver_email" id="receiver-email">
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
    <p id="message-error"></p>
</section>

<script>
    async function fetchMessages(email) {
        const response = await fetch(`fetch_messages.php?client_email=${email}`);
        const messages = await response.json();
        const messagesContainer = document.getElementById('messages');
        messagesContainer.innerHTML = '';
        messages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.className = message.sender_email === '<?php echo $_SESSION['user']['email']; ?>' ? 'message-right' : 'message-left';
            messageDiv.innerHTML = `<p><strong>${message.sender_email}:</strong> ${message.message_text} <em>(${message.sent_at})</em></p>`;
            messagesContainer.appendChild(messageDiv);
        });
    }

    function openMessagingSection(prenom, nom, email) {
        document.getElementById('client-name').textContent = prenom + ' ' + nom;
        document.getElementById('receiver-email').value = email;
        document.getElementById('messaging-section').style.display = 'block';

        // Fetch messages for the selected client
        fetchMessages(email);

        // Add the client email to the URL
        const url = new URL(window.location);
        url.searchParams.set('client_email', email);
        window.history.pushState({}, '', url);
    }

    document.getElementById("close-button").addEventListener("click", function() {
        document.getElementById("messaging-section").style.display = 'none';
    });

    document.getElementById('message-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        const messageText = document.getElementById('message_text').value;
        const receiverEmail = document.getElementById('receiver-email').value;
        if (messageText && receiverEmail) {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message_text: messageText,
                    receiver_email: receiverEmail
                })
            });
            const result = await response.json();
            if (result.success) {
                document.getElementById('message_text').value = '';
                fetchMessages(receiverEmail);
            } else {
                document.getElementById('message-error').textContent = 'Message could not be sent';
            }
        } else {
            document.getElementById('message-error').textContent = 'Message and receiver cannot be empty';
        }
    });

    // Fetch messages if client email is present in URL on page load
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const clientEmail = urlParams.get('client_email');
        if (clientEmail) {
            openMessagingSection('', '', clientEmail);
        }
    };
</script>

<script src="dashb.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="dashb.css"></script>

</body>
</html>
