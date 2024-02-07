<?php
session_start();
// Erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['pseudo'])) {
    header('Location: ./');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Règles - Semantic Analogy Explorer</title>
    <meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
    <meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
    <link rel="stylesheet" href="css/rules.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/table.css">
    
</head>

<body class="black">
<a class="btn btn-light mb-3" href="home.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
<main class="glassmorphism">
    <h1 class="title">Règles du Jeu Semantic Analogy Explorer - Mode Solo</h1>
    <section class="regles">
        <h2>Objectif :</h2>
        <p>L'objectif du jeu est de former une chaîne de mots connectés entre eux par leur similarités sémantiques et orthographiques. En partant de deux mots donnés au début, vous devez créer une suite de mots où chaque nouveau mot est similaire, soit dans son sens, soit dans sa forme, au mot précédent. L'objectif est d'obtenir le score de ressemblance le plus élevé possible entre les mots.</p>

        <h2>Déroulement du jeu :</h2>
        <h3>Démarrage :</h3>
        <p>Le jeu commence avec deux mots initiaux. Ces mots sont les premiers maillons de votre chaîne de mots.</p>

        <h3>Entrée du joueur :</h3>
        <p>Vous devez entrer cinq mots supplémentaires, un après l'autre. Chaque nouveau mot doit être choisi de manière à ressembler, par son sens ou sa forme, au mot précédent dans la chaîne.</p>

        <h3>Vérification du dictionnaire :</h3>
        <p>Si vous proposez un mot qui n'est pas reconnu par le jeu, vous pourrez réessayer sans pénalité. Le jeu vous indiquera simplement que le mot n'est pas reconnu et vous invitera à essayer un autre mot.</p>

        <h3>Fin de la partie :</h3>
        <p>La partie se termine après que vous ayez entré vos cinq mots. Le score final est basé sur le maillon le plus faible de votre chaîne, c'est-à-dire la paire de mots consécutifs qui se ressemblent le moins.</p>

        <h2>Calcul du score :</h2>
        <h3>Ressemblance visuelle :</h3>
        <p>La ressemblance visuelle entre deux mots est évaluée en regardant combien de lettres doivent être changées pour passer d'un mot à l'autre. Plus il y a de lettres en commun, plus le score est élevé.</p>

        <h3>Ressemblance de sens :</h3>
        <p>La ressemblance de sens est évaluée en examinant à quel point les mots sont généralement utilisés dans des contextes similaires. Des mots qui ont souvent un sens ou une utilisation proches auront un score élevé.</p>

        <h3>Score final :</h3>
        <p>Le score final est déterminé en prenant en compte à la fois la ressemblance visuelle et la ressemblance de sens. La paire de mots consécutifs qui se ressemble le moins détermine votre score final. Le but est donc de choisir des mots qui non seulement se suivent bien, mais qui maintiennent également une forte ressemblance tout au long de la chaîne.</p>
    </section>
</main>
</body>
</html>
