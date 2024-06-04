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
    <meta name="description" content="Venez jouer à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible.">
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
        <h2><i class="fa-solid fa-bullseye icon"></i>Objectif :</h2>
        <p>L'objectif du jeu est de former une chaîne de mots connectés entre eux par leurs similarités sémantiques et orthographiques. En partant de deux mots donnés au début, vous devez créer une suite de mots où chaque nouveau mot est similaire, soit dans son sens, soit dans sa forme, au mot précédent. L'objectif est d'obtenir le score de ressemblance le plus élevé possible entre les mots.</p>

        <h2><i class="fa-solid fa-play icon"></i>Déroulement du jeu :</h2>
        <div class="rule">
            <h3>Démarrage :</h3>
            <p>Le jeu commence avec deux mots initiaux. Ces mots sont les premiers maillons de votre chaîne de mots.</p>
        </div>
        
        <div class="rule">
            <h3>Entrée du joueur :</h3>
            <p>Vous devez entrer cinq mots supplémentaires, un après l'autre. Chaque nouveau mot doit être choisi de manière à ressembler, par son sens ou sa forme, au mot précédent dans la chaîne.</p>
        </div>

        <div class="rule">
            <h3>Vérification du mot :</h3>
            <p>Si vous proposez un mot qui n'est pas reconnu par le jeu, vous pourrez réessayer sans pénalité. Le jeu vous indiquera simplement que le mot n'est pas reconnu et vous invitera à essayer un autre mot.</p>
            <p>Et si vous proposez un mot qui ne ressemble pas suffisamment aux mots de départ et ceux qui sont entre, vous devrez proposer un autre mot qui sera meilleur, mais le mot sera utilisé dans la partie. Mais n'ayez crainte, le mot peut revenir si un autre le ramène entre les deux mots de départ !</p>
        </div>

        <div class="rule">
            <h3>Fin de la partie :</h3>
            <p>La partie se termine après que vous ayez entré vos cinq mots. Le score final est basé sur le maillon le plus faible de votre chaîne, c'est-à-dire la paire de mots consécutifs qui se ressemblent le moins.</p>
        </div>

        <h2><i class="fa-solid fa-calculator icon"></i>Calcul du score :</h2>
        <div class="rule">
            <h3>Ressemblance orthographique :</h3>
            <p>La ressemblance orthographique entre deux mots est évaluée en regardant combien de lettres doivent être changées pour passer d'un mot à l'autre. Plus il y a de lettres en commun, plus le score est élevé.</p>
        </div>

        <div class="rule">
            <h3>Ressemblance lexicographique :</h3>
            <p>La ressemblance lexicographique est évaluée en examinant à quel point les mots sont généralement utilisés dans des contextes similaires. Des mots qui ont souvent un sens ou une utilisation proches auront un score élevé.</p>
        </div>

        <h2><i class="fa-solid fa-trophy icon"></i>Score final :</h2>
        <p>Le score final est déterminé en prenant en compte à la fois la ressemblance visuelle et la ressemblance de sens. La paire de mots consécutifs qui se ressemblent le moins détermine votre score final. Le but est donc de choisir des mots qui non seulement se suivent bien, mais qui maintiennent également une forte ressemblance tout au long de la chaîne.</p>
    </section>
    <h1 class="title">Règles du Jeu Semantic Analogy Explorer - Mode Multijoueur</h1>
    <section class="regles">
        <h2><i class="fa-solid fa-bullseye icon"></i>Objectif :</h2>
        <p>L'objectif du jeu est de former une chaîne de mots connectés entre eux par leurs similarités sémantiques et orthographiques. En mode multijoueur, vous devez coopérer avec un autre joueur pour créer la suite de mots la plus cohérente et obtenir le score de ressemblance le plus élevé possible.</p>

        <h2><i class="fa-solid fa-play icon"></i>Déroulement du jeu :</h2>
        <div class="rule">
            <h3>Démarrage :</h3>
            <p>Lorsque vous lancez une partie multijoueur, vous êtes automatiquement jumelé avec un autre joueur en ligne. Le jeu commence avec deux mots initiaux, qui sont les premiers maillons de votre chaîne de mots.</p>
        </div>

        <div class="rule">
            <h3>Communication :</h3>
            <p>Un chat intégré au jeu vous permet de communiquer avec votre partenaire. Utilisez-le pour échanger des idées et discuter des mots à ajouter à la chaîne. Rappelez-vous, deux cerveaux valent mieux qu'un !</p>
        </div>

        <div class="rule">
            <h3>Entrée des joueurs</h3>
            <p>Vous et votre partenaire devez insérer des mots alternativement. Chaque nouveau mot doit être choisi de manière à ressembler, par son sens ou sa forme, au mot précédent dans la chaîne. L'entraide est essentielle pour choisir les meilleurs mots et maintenir une forte ressemblance tout au long de la chaîne.</p>
        </div>

        <div class="rule">
            <h3>Vérifications des mots :</h3>
            <p>Si l'un de vous propose un mot qui n'est pas reconnu par le jeu, vous pourrez réessayer sans pénalité. Le jeu vous indiquera simplement que le mot n'est pas reconnu et vous invitera à essayer un autre mot.
                Si un mot proposé ne ressemble pas suffisamment aux mots de départ ou aux mots intermédiaires, il sera toujours utilisé dans la partie, mais vous devrez essayer de compenser avec des mots suivants mieux adaptés. Cependant, le mot peut revenir si un autre le rapproche des mots de départ.</p>
        </div>

        <div class="rule">
            <h3>Fin de la partie :</h3>
            <p>La partie se termine après que vous et votre partenaire ayez chacun entré cinq mots, soit un total de dix mots ajoutés à la chaîne. Le score final est basé sur le maillon le plus faible de votre chaîne, c'est-à-dire la paire de mots consécutifs qui se ressemblent le moins.</p>
        </div>

        <h2><i class="fa-solid fa-calculator icon"></i>Calcul du score :</h2>
        <div class="rule">
            <h3>Ressemblance orthographique :</h3>
            <p>La ressemblance orthographique entre deux mots est évaluée en regardant combien de lettres doivent être changées pour passer d'un mot à l'autre. Plus il y a de lettres en commun, plus le score est élevé.</p>
        </div>

        <div class="rule">
            <h3>Ressemblance lexicographique :</h3>
            <p>La ressemblance lexicographique est évaluée en examinant à quel point les mots sont généralement utilisés dans des contextes similaires. Des mots qui ont souvent un sens ou une utilisation proches auront un score élevé.</p>
        </div>

        <h2><i class="fa-solid fa-trophy icon"></i>Score final :</h2>
        <p>Le score final est déterminé en prenant en compte à la fois la ressemblance visuelle et la ressemblance de sens. La paire de mots consécutifs qui se ressemblent le moins détermine votre score final. Le but est donc de choisir des mots qui non seulement se suivent bien, mais qui maintiennent également une forte ressemblance tout au long de la chaîne.</p>

        <h2>Entraide :</h2>
        <p>N'oubliez pas de communiquer et de vous entraider tout au long de la partie. Un bon travail d'équipe et une bonne stratégie vous permettront de maximiser votre score. Prenez soin de ne pas nuire à votre allié avec des choix de mots inappropriés.</p>
    </section>
</main>
</body>
</html>
