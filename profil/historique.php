<?php
session_start();
// Erreur PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
    exit();
}
$pseudo = $_SESSION['pseudo'];

include("../conf.bkp.php");
include("../includes/fonctions.php");
// Requête pour récupérer l'historique
$historicRequest = $cnx->prepare("
SELECT score, dateHeure
FROM SAE_USERS u, SAE_SCORES s
WHERE pseudo = :pseudo AND u.num_user = s.num_user
ORDER BY dateHeure DESC;");
$historicRequest->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
$historicRequest->execute();
$historicResult = $historicRequest->fetchAll(PDO::FETCH_OBJ);
$historicRequest->closeCursor();
?>
<html lang="fr">
    <head>
        <title>Historique - Wacky Monkey's Words</title>
        <meta charset="UTF-8">
<link rel="icon" href="img/sinje_magique.ico" />
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/table.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Venez Jouez à Wacky Monkey's Words (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Wacky Monkey's Words ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
        <meta name="keywords" content="Wacky Monkey's Words, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
    </head>

    <body class="black">
        <a class="btn btn-light" href="profil.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
        <main class="glassmorphism">
            <h1 class="title">Votre Historique</h1>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr class="table-dark text-center">
                            <th scope="col">#</th>
                            <th scope="col">Date et Heure</th>
                            <th scope="col">Score</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        $i = 1;
                        foreach ($historicResult as $historic) {
                            echo "<tr>";
                            echo "<td>$i</td>";
                            echo "<td>" . makeDateTime($historic->dateHeure) . "</td>";
                            echo "<td>$historic->score</td>";
                            echo "</tr>";
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </body>
</html>