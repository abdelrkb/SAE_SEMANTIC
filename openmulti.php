<?php
session_start();
include_once('code_secret.php');

$code = $_POST['code'];
$pseudo = $_SESSION["pseudo"];

// Générer un token aléatoire sécurisé
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['token'];

if ($code == $code_secret) {
    header('Location: http://localhost:3000/' .'?pseudo='. $pseudo . '&token=' . $token); // Corrected string concatenation
} else {
    header('Location: home.php');
}

exit(); // Always call exit after header redirection
?>
