<?php
session_start();
// Utilisateur connecté ?
if (isset($_SESSION['pseudo'])) {
    header('Location: ../home.php');
    exit;
}

// Messages possibles
$messagesErreur = [
    1 => ["Si le pseudo et l'adresse e-mail que vous avez saisis correspondent, vous recevrez alors un e-mail pour changer votre mot de passe.", "alert-warning"],
    2 => ["Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.", "alert-success"]
];

// Récupérer le code d'erreur depuis l'URL
$codeErreur = isset($_GET['erreur']) ? (int)$_GET['erreur'] : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mot de passe Oublié ? - Semantic Analogy Explorer</title>
	<meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
	<meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/form.css">
</head>
<body class="black">
<a class="btn btn-light fw-semibold" href="../index.php">Retour</a>
<main class="position-absolute top-50 start-50 translate-middle">
    <div class="glassmorphism">
        <form method="POST" action="script-forgot_password.php">
            <h1 class="title">Récupérer votre<!--<br>--> mot de passe</h1>
            <div class="input-field">
                <input name="pseudo" type="text" id="pseudo" required>
                <label for="pseudo">Pseudo</label>
            </div>
            <div class="input-field">
                <input name="email" type="email" id="email" required>
                <label for="email">Email</label>
            </div>
            <button id="formButton" type="submit" class="btn fw-semibold">Récuperer votre mot de passe</button>
        </form>
        <?php
        // Si le message d'erreur est différent de 0
        if ($codeErreur > 0 && $codeErreur < 5) {
            echo "<br><div id='msg-error' class='alert' role='alert'></div>";
        }
        ?>
    </div>
</main>
<script>
    // Je récupère le message d’erreur
    let msgCode = <?php echo json_encode($codeErreur); ?>;
    let msgError = <?php echo json_encode($messagesErreur[$codeErreur][0]); ?>;
    // Si le message d’erreur est différent de 0
    if (msgCode > 0 && msgCode < 3) {
        // J'affiche le message d'erreur
        document.getElementById('msg-error').innerHTML = msgError;
        // Je change la couleur du message d'erreur
        document.getElementById('msg-error').classList.add(<?php echo json_encode($messagesErreur[$codeErreur][1]); ?>);
        document.getElementById('msg-error').classList.add('visible');
        // Après l'expiration du cookie, on actualise la page pour le supprimer
        setTimeout(function () {
            window.location.href = '../forgotpassword/forgot_password.php';
        }, 10000);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>