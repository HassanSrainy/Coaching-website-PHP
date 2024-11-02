<?php
include 'database.php';
echo "</br>";

// Récupérer les données des différentes tables
$q1 = $db->query("SELECT * FROM utilisateur");
$q2 = $db->query("SELECT * FROM client");
$q3 = $db->query("SELECT * FROM Domaines");
$q4 = $db->query("SELECT * FROM Cours");
$q5 = $db->query("SELECT * FROM coach");
$q6 = $db->query("SELECT * FROM specialitecoach");

// Afficher les informations des utilisateurs
while ($user = $q1->fetch()) {
    echo "Nom: " . $user['Nom'] . " | Email: " . $user['Email'] . " | Type: " . $user['type'] . "</br>";
}

// Afficher les informations des clients
while ($client = $q2->fetch()) {
    echo "id: " . $client['id_client'] . " | Email: " . $client['Email'] . "</br>";
}

// Afficher les informations des domaines
while ($domaine = $q3->fetch()) {
    echo "id_Domaine: " . $domaine['id_domaine'] . " | Nom du Domaine: " . $domaine['nom_domaine'] . "</br>";
}

// Afficher les informations des cours
while ($cours = $q4->fetch()) {
    echo "id_cours: " . $cours['id_cours'] . " | Nom du cours: " . $cours['nom_cours'] . " | Domaine: " . $cours['id_domaine'] . "</br>";
}

// Afficher les informations des coachs
while ($coach = $q5->fetch()) {
    echo "id_Coach: " . $coach['id_coach'] . " | Email: " . $coach['Email'] . " | Contact: " . $coach['contact'] . "</br>";
}

// Créer un tableau associatif pour les spécialités des coachs
$specialitesCoach = [];

while ($specialite = $q6->fetch()) {
    $specialitesCoach[$specialite['id_coach']][] = $specialite['id_domaine'];
}

// Récupérer les noms des domaines pour les afficher plus tard
$domaines = [];
$q3->execute();
while ($domaine = $q3->fetch()) {
    $domaines[$domaine['id_domaine']] = $domaine['nom_domaine'];
}

// Afficher chaque coach et les domaines auxquels ils sont affectés
foreach ($specialitesCoach as $idCoach => $domainesIds) {
    echo "Coach ID: " . $idCoach . " est affecté aux domaines: ";
    foreach ($domainesIds as $idDomaine) {
        echo $domaines[$idDomaine] . ", ";
    }
    echo "</br>";
}
?>
