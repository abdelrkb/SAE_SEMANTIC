<?php
session_start();

if (!isset($_SESSION['pseudo_temp'])) {
    header('Location: ../');
    exit;
}

include 'conf.bkp.php';

$pseudo = $_SESSION['pseudo_temp'];

//requête récuperer le num_user de l'utilisateur
$queryGetNum = "SELECT num_user FROM SAE_USERS WHERE pseudo = :pseudo";
$stmtGetNum = $cnx->prepare($queryGetNum);
$stmtGetNum->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
$stmtGetNum->execute();

// Récupère le résultat
$num_user = $stmtGetNum->fetchColumn();

// Génération d'un code de confirmation (peut être un jeton unique)
$code_confirmation = bin2hex(random_bytes(16));

// Enregistrement du code de confirmation dans la base de données
$query_insert_confirmation = "INSERT INTO SAE_CONFIRMATION_CODES (num_user, code) VALUES (:num_user, :code)";
$stmt_insert_confirmation = $cnx->prepare($query_insert_confirmation);
$stmt_insert_confirmation->bindParam(":num_user", $num_user, PDO::PARAM_INT);
$stmt_insert_confirmation->bindParam(":code", $code_confirmation);
$stmt_insert_confirmation->execute();

//requête récuperer le mail de l'utilisateur
$queryGetMail = "SELECT email FROM SAE_USERS WHERE pseudo = :pseudo";
$stmtGetmail = $cnx->prepare($queryGetMail);
$stmtGetmail->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
$stmtGetmail->execute();
// Récupère le résultat
$email = $stmtGetmail->fetchColumn();

include("mailer.php");
$mail->addAddress($email, $pseudo);

// Contenu du mail
$mail->isHTML(true);
$mail->Subject = "Confirmation d'inscription";
$mail->Body = "Bienvenue $pseudo sur notre site !<br><br>Veuillez confirmer votre inscription en cliquant sur le lien suivant : <a href='http://perso-etudiant.u-pem.fr/~chamsedine.amouche/Projet-SAE/inscription/script-confirmermail.php?code=$code_confirmation'>Confirmer</a>";
$mail->CharSet = 'UTF-8';
$mail->send();
?>