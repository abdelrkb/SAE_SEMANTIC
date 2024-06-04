<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
    exit;
}
$pseudo = $_SESSION['pseudo'];

include("../conf.bkp.php");
include("../includes/fonctions.php");
// Requête pour récupérer les informations de l'utilisateur
$profilRequest = $cnx->prepare("
SELECT email, 
       annee_naissance,
        (SELECT MAX(timestamp) 
         FROM SAE_TRACES 
         WHERE utilisateur_id = u.num_user 
           AND action = 'Connexion au Site') AS lastConnexion
FROM SAE_USERS u,
     SAE_TRACES t
WHERE u.num_user = t.utilisateur_id
  AND action = 'Connexion au Site'
  AND pseudo = :pseudo");
$profilRequest->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
$profilRequest->execute();
$profilResult = $profilRequest->fetch(PDO::FETCH_OBJ);
$profilRequest->closeCursor();
// Requête pour récupérer les statistiques de l'utilisateur
$scoreRequest = $cnx->prepare("
SELECT MIN(score) AS minS,
       MAX(score) AS maxS,
       AVG(score) AS avgS,
       COUNT(score) AS nbParties
FROM SAE_SCORES s,
     SAE_USERS u
WHERE u.num_user = s.num_user 
  AND u.pseudo = :pseudo");
$scoreRequest->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
$scoreRequest->execute();
$scoreResult = $scoreRequest->fetch(PDO::FETCH_OBJ);
$scoreRequest->closeCursor();
// Requête pour récupérer le top 3 des meilleurs scores (max), le pseudo n'apparaît qu'une seule fois
$top3Request = $cnx->prepare("
SELECT pseudo, MAX(score) AS score
FROM SAE_SCORES s,
     SAE_USERS u
WHERE u.num_user = s.num_user
GROUP BY pseudo
ORDER BY score DESC
LIMIT 3");
$top3Request->execute();
$top3Result = $top3Request->fetchAll(PDO::FETCH_OBJ);
$top3Request->closeCursor();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-sacle=1.0">
        <meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
	    <meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
        <title>Profil - Semantic Analogy Explorer</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/css_profil.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    </head>

    <body class="black">
        <main>
            <a class="btn btn-light mb-3" href="../home.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
            <div class="parent glassmorphism">
                <div class="photo-pseudo-buttons glassmorphism-section">
                    <div class="photo-pseudo">
                        <img src="../img/profil.webp" alt="Photo de Profil" title="Photo <?php echo $pseudo;?>">
                        <h1 class="title-section h1"><?php echo $pseudo;?></h1>
                    </div>
                    <div class="buttons">
                        <a id="historique" href="historique.php" class="btn btn-primary g-col-6 text-nowrap">Voir mon historique&emsp;<i class="fa-solid fa-clock-rotate-left"></i></a>
                        <a id="btn-email" href="change_email.php" class="btn btn-warning text-nowrap">Changer l'email&emsp;<i class="fa-solid fa-envelope"></i></a>
                        <a id="btn-mdp" href="change_password.php" class="btn btn-warning text-nowrap">Changer le mot de passe&emsp;<i class="fa-solid fa-key"></i></a>
                        <button id="disconnect-btn" class="btn btn-danger g-col-6 text-nowrap">Se déconnecter&emsp;<i class="fa-solid fa-right-from-bracket"></i></button>
                    </div>
                </div>
                <div class="stats glassmorphism-section">
                    <h2 class="title-section h2">Mes Statistiques</h2>
                    <ul>
                        <li>
                            <ul>
                                <li>Score Minimum</li>
                                <li><button class="btn btn-dark"><?php echo $scoreResult->minS == null ? 0 : $scoreResult->minS; ?> <i class="fa-solid fa-arrow-down"></i></button></li>
                            </ul>
                        </li>
                        <li>
                            <ul>
                                <li>Score Maximum</li>
                                <li><button class="btn btn-dark"><?php echo $scoreResult->maxS == null ? 0 : $scoreResult->maxS; ?> <i class="fa-solid fa-arrow-up"></i></button></li>
                            </ul>
                        </li>
                        <li>
                            <ul>
                                <li>Score Moyen</li>
                                <li><button class="btn btn-dark"><?php echo round($scoreResult->avgS) == null ? 0 : round($scoreResult->avgS); ?> <i class="fa-solid fa-arrows-left-right"></i></button></li>
                            </ul>
                        </li>
                        <li>
                            <ul>
                                <li>Nombre de Parties</li>
                                <li><button class="btn btn-dark"><?php echo $scoreResult->nbParties == null ? "<span style='color: red'>Essayez de jouer !</span>" : $scoreResult->nbParties; ?> <i class="fa-solid fa-hashtag"></i></button></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="scores glassmorphism-section">
                    <h2 class="title-section">Meilleurs Scores</h2>
                    <div class="score-list">
                        <div id="first" class="btn gold">
                            <span class="pseudo"><?php echo $top3Result[0]->pseudo;?></span>
                            <span class="score"><?php echo $top3Result[0]->score;?></span>
                        </div>
                        <div id="second" class="btn silver">
                            <span class="pseudo"><?php echo $top3Result[1]->pseudo;?></span>
                            <span class="score"><?php echo $top3Result[1]->score;?></span>
                        </div>
                        <div id="third" class="btn bronze">
                            <span class="pseudo"><?php echo $top3Result[2]->pseudo;?></span>
                            <span class="score"><?php echo $top3Result[2]->score;?></span>
                        </div>
                        <?php
                        if ($top3Result[0]->pseudo != $pseudo && $top3Result[1]->pseudo != $pseudo && $top3Result[2]->pseudo != $pseudo) {
                            echo "<div id='myScore' class='btn btn-dark'>";
                        } else {
                            echo "<div id='myScore' class='btn " . ($top3Result[0]->pseudo == $pseudo ? "gold" : ($top3Result[1]->pseudo == $pseudo ? "silver" : "bronze")) . "'>";
                        }
                        echo "<span class='pseudo'>$pseudo</span>";
                        echo "<span class='score'>" . ($scoreResult->maxS == null ? 0 : $scoreResult->maxS) . "</span>";
                        echo "</div>";
                        ?>
                    </div>
                </div>
                <div class="mesinformations glassmorphism-section">
                    <h2 class="title-section">Mes Informations</h2>
                    <div>
                        <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Email : <?php echo $profilResult->email;?>">
                            Pseudo : <?php echo $pseudo;?>
                        </button>
                        <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Email : <?php echo $profilResult->email;?>">
                            Email : <?php echo $profilResult->email;?>
                        </button>
                        <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Année de Naissance : <?php echo $profilResult->annee_naissance;?>">
                            Année de Naissance : <?php echo $profilResult->annee_naissance;?>
                        </button>
                        <button id="tempsEcoule" type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Connecté depuis : <?php echo makeDateTime($profilResult->lastConnexion);?>">
                        </button>
                    </div>
                </div>
            </div>
        </main>
        <script>
            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip();
            });

            document.getElementById('disconnect-btn').addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    window.location.href = '../connexion/script-logout.php'; // Redirigez vers la page de déconnexion
                }
            });

            $(document).ready(function() {
                function updateElapsedTime() {
                    var now = Math.floor(new Date().getTime() / 1000); // Temps actuel en secondes
                    var lastConnexionTimestamp = <?php echo strtotime($profilResult->lastConnexion); ?>;
                    var elapsedSeconds = now - lastConnexionTimestamp;

                    var hours = Math.floor(elapsedSeconds / 3600);
                    var minutes = Math.floor((elapsedSeconds % 3600) / 60);
                    var seconds = elapsedSeconds % 60;

                    // Créez une chaîne de caractères pour afficher le temps écoulé
                    let elapsedTimeString = "";
                    if (hours > 0) {
                        elapsedTimeString += hours + "h ";
                    }
                    if (minutes > 0) {
                        elapsedTimeString += minutes + "m ";
                    }
                    elapsedTimeString += seconds + "s";
                    $("#tempsEcoule").text("Dernière connexion : " + elapsedTimeString);
                }

                // Mettez à jour le temps écoulé toutes les secondes
                setInterval(updateElapsedTime, 1000);

                // Appelez la fonction une fois au chargement de la page
                updateElapsedTime();
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
