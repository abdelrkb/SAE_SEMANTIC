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
$pseudo = $_SESSION['pseudo'];
include("conf.bkp.php");
include("includes/fonctions.php");
// Requête pour récupérer le score des 10 premiers utilisateurs
$top10Request = $cnx->prepare("
SELECT pseudo, MAX(score) AS score
FROM SAE_SCORES s,
     SAE_USERS u
WHERE u.num_user = s.num_user
GROUP BY pseudo
ORDER BY score DESC
LIMIT 10;");
$top10Request->execute();
$top10Result = $top10Request->fetchAll(PDO::FETCH_OBJ);
$top10Request->closeCursor();
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Classement - Semantic Analogy Explorer</title>
		<meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
		<meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/table.css">
	</head>

	<body class="black">
		<a class="btn btn-light mb-3" href="home.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
		<main class="glassmorphism">
			<h1 class="title">Classement des 10 meilleurs joueurs</h1>
			<div class="table-responsive">
				<table class="table table-hover table-bordered">
					<thead>
						<tr class="table-dark">
							<th scope="col">Position&emsp;<i class="fa-solid fa-ranking-star"></i></th>
							<th scope="col">Pseudo</th>
							<th scope="col">Score</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$top10Pseudos = array();
						$position = 1;
						foreach ($top10Result as $ligne) {
							echo "<tr class='" . podiumClass($position) . "'>";
							echo "<td>$position</td>";
							echo "<td>$ligne->pseudo</td>";
							echo "<td>$ligne->score</td>";
							echo "</tr>";
							$position++;
							$top10Pseudos[] = $ligne->pseudo;
						}
						// Si le joueur n'est pas dans le top 10, on affiche son score
						if (!in_array($pseudo, $top10Pseudos)) {
							$scoreRequest = $cnx->prepare("SELECT MAX(score) AS maxS FROM SAE_SCORES s, SAE_USERS u WHERE u.num_user = s.num_user AND u.pseudo = :pseudo");
							$scoreRequest->bindParam(':pseudo', $pseudo);
							$scoreRequest->execute();
							$scoreResult = $scoreRequest->fetch(PDO::FETCH_OBJ);
							$scoreRequest->closeCursor();
							echo "<tr class='table-secondary'>";
							echo "<td colspan='3'>Votre score : $scoreResult->maxS</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</main>
	</body>
</html>
