<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
    header('Location: ./');
    exit;
}
$pseudo = $_SESSION['pseudo'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["executeButton"])) {
    exec("echo 1", $output, $return_var);
    echo $output[0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/sinje_magique.ico" />
    <title>Home - Wacky Monkey's Words</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Venez Jouez à Wacky Monkey's Words (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Wacky Monkey's Words ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible.">
    <meta name="keywords" content="Wacky Monkey's Words, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/css_home.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/3f3ecfc27b.js" crossorigin="anonymous"></script>
</head>
<body class="black">
    <?php
    include_once('admin.php');
    ?>
    <figure class="text-end">
        <img src="img/sinje_off.png" alt="Logo">
        <a class="btn btn-light" href="rules.php">Règles&emsp;<i class="fa-solid fa-scale-balanced"></i></a>
        <a class="btn btn-light" href="classement.php">Classement&emsp;<i class="fa-solid fa-trophy"></i></a>
        <a class="btn btn-light" href="traces.php">Traces&emsp;<i class="fa-solid fa-clipboard-list"></i></a>
        <a class="btn btn-light" href="profil/profil.php">Mon Profil&emsp;<i class="fa-solid fa-user"></i></a>
    </figure>
    <main class="glassmorphism">
        <form method="post" action="game/start_game.php">
            <img src="img/sinje_off.png" alt="Logo">
            <h1 class="title">Wacky Monkey's Words</h1>
            <?php echo "<h2 class='subtitle'>Bienvenue, $pseudo</h2>"; ?>
            <h2 class="subtitle2"><i class="fa-solid fa-gamepad"></i>&emsp;Choisissez un mode de jeu&emsp;<i class="fa-solid fa-dice"></i></h2>
            <div class="buttons-play">
                <button id="executeButton" class="btn btn-primary btn-lg" type="submit">Solo&emsp;<i class="fa-solid fa-user"></i></button>
                <button id="executeButtonMulti" class="btn btn-primary btn-lg" type="reset" onclick="window.location.href='gotomulti.php';">Multijoueur&emsp;<i class="fa-solid fa-users"></i></button>
            </div>
        </form>
    </main>

    <div class="navbar-bottom">
        <a class="btn" href="rules.php"><i class="fa-solid fa-scale-balanced"></i></a>
        <a class="btn" href="classement.php"><i class="fa-solid fa-trophy"></i></a>
        <a class="btn" href="profil/profil.php"><i class="fa-solid fa-user"></i></a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="js/websocket.js"></script>
    <script>
        function showToast() {
            var toast = document.getElementById("adminToast");
            toast.className = "toast show";
            setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
        }
    </script>
</body>
</html>
