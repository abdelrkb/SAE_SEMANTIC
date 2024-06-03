<?php
session_start();
if (!isset($_SESSION['pseudo']) ) {
    header('Location: ./');
    exit;
}
$isAdmin = $_SESSION['admin'];
$pseudo = $_SESSION['pseudo'];
include("includes/fonctions.php");
include("conf.bkp.php");
// Récupération les traces de tous les utilisateurs
$tracesEveryoneRequest = $cnx->prepare("SELECT timestamp, ip_adress, utilisateur_id, action FROM SAE_TRACES ORDER BY id DESC LIMIT 500;");
$tracesEveryoneRequest->execute();
$tracesEveryoneResult = $tracesEveryoneRequest->fetchAll(PDO::FETCH_OBJ);
$tracesEveryoneRequest->closeCursor();
// Récupération les traces de l'utilisateur
$tracesOnlyMeRequest = $cnx->prepare("SELECT timestamp, ip_adress, action FROM SAE_TRACES WHERE utilisateur_id = :num_user ORDER BY id DESC LIMIT 500;");
$tracesOnlyMeRequest->bindValue(':num_user', $_SESSION['num_user'], PDO::PARAM_INT);
$tracesOnlyMeRequest->execute();
$tracesOnlyMeResult = $tracesOnlyMeRequest->fetchAll(PDO::FETCH_OBJ);
$tracesOnlyMeRequest->closeCursor();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Traces - Semantic Analogy Explorer</title>
	<meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
	<meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
	<link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/table.css">
    <style>
        .nav-tabs {
            justify-content: center;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body class="black">
<a class="btn btn-light" href="home.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
<main class="glassmorphism">
    <?php
    
    if ($isAdmin) {
        echo '<ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#everyone" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Tout</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#only-me" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Profil</button>
        </li>
    </ul>';
    
    }
    ?>
    
    <div class="tab-content" id="myTabContent">
    
    <?php
    $active = "";
    
    if ($isAdmin) {
        echo '<div class="tab-pane fade show active" id="everyone" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
        <h1 class="title">500 Dernières Traces de Tous les Utilisateurs</h1>
        <!-- Nombre de traces -->
        <div class="text-end">
            <p class="text-white">Nombre de traces : <?php echo count($tracesEveryoneResult); ?></p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                <tr class="table-dark">
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Heure</th>
                    <th scope="col">IP</th>
                    <th scope="col">ID Utilisateur</th>
                    <th scope="col">Action réalisée</th>
                </tr>
                </thead>
                <tbody>';
                $i = 1;
                foreach ($tracesEveryoneResult as $trace) {
                    echo "<tr class='" . addStyleTableRow($trace->action) . "'>";
                    echo "<td>$i</td>";
                    echo "<td>" . makeDate($trace->timestamp) . "</td>";
                    echo "<td>" . makeHour($trace->timestamp) . "</td>";
                    echo "<td>$trace->ip_adress</td>";
                    echo "<td>$trace->utilisateur_id</td>";
                    echo "<td>$trace->action</td>";
                    echo "</tr>";
                    $i++;
                }
                
        echo '</tbody>
            </table>
        </div>  
    </div>';

    $active = "show active";
    
    }
    ?>
        <div class="tab-pane fade <?php $active ?>" id="only-me" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            <h1 class="title">500 Dernières Traces de <?php echo $pseudo; ?></h1>
            <!-- Nombre de traces -->
            <div class="text-end">
                <p class="text-white">Nombre de traces : <?php echo count($tracesOnlyMeResult); ?></p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr class="table-dark">
                        <th scope="col">#</th>
                        <th scope="col">Date</th>
                        <th scope="col">Heure</th>
                        <th scope="col">IP</th>
                        <th scope="col">Action réalisée</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    foreach ($tracesOnlyMeResult as $trace) {
                        echo "<tr class='" . addStyleTableRow($trace->action) . "'>";
                        echo "<td>$i</td>";
                        echo "<td>" . makeDate($trace->timestamp) . "</td>";
                        echo "<td>" . makeHour($trace->timestamp) . "</td>";
                        echo "<td>$trace->ip_adress</td>";
                        echo "<td>$trace->action</td>";
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>