<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
}

include '../conf.bkp.php';
include '../includes/fonctions.php';

//Récuperer le num_user pour la journalisation
$queryNum = "SELECT * FROM SAE_USERS WHERE pseudo = :pseudo";
$stmtNum = $cnx->prepare($queryNum);
$stmtNum->bindParam(':pseudo', $_SESSION['pseudo'], PDO::PARAM_STR);
$stmtNum->execute();
$resultat = $stmtNum->fetch(PDO::FETCH_ASSOC);
$num_user = $resultat['num_user'];

// Journalisation
trace($num_user, 'Déconnexion du Site', $cnx);

session_start();
session_destroy();
header('Location: ../index.php?erreur=6');
?>