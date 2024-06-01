<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['pseudo'])) {
    header("Location: ./");
    exit();
}

if (!isset($_SESSION['paires'])) {
    header("Location: game.php");
    exit();
}

// Ajout du score final dans la base de données
include("../conf.bkp.php");
include("../includes/fonctions.php");
include("game_fonctions.php");

// Score final
$finalScore = calculateScore();
$requestAddFinalScore = $cnx->prepare("INSERT INTO SAE_SCORES (num_user, score) VALUES (:num_user, :score)");
$requestAddFinalScore->bindParam(':num_user', $_SESSION['num_user']);
$requestAddFinalScore->bindParam(':score', $finalScore);
$requestAddFinalScore->execute();
$requestAddFinalScore->closeCursor();
trace($_SESSION['num_user'], "A Joué une partie", $cnx);
unset($_SESSION['paires']);
unset($_SESSION['words']);

//Supprimer le fichier partie associé à l'utilisateur (C)
$nomFichier = "partie/game_data_" . $_SESSION['pseudo'] . ".txt";
unlink($nomFichier);
// Supprimer le fichier partie associé à l'utilisateur (Java)
$javaFile = "partie/resultjava_" . $_SESSION['pseudo'] . ".txt";
unlink($javaFile);

header("Location: ../");
exit();
?>