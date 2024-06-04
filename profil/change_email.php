<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
    exit;
}

// Messages d’erreurs possibles
$messagesErreur = [
    1 => ["L'adresse e-mail a bien été modifiée.", "alert-success"],
    2 => ["L'ancienne adresse e-mail est incorrecte.", "alert-danger"],
    3 => ["La nouvelle adresse e-mail est incorrecte.", "alert-danger"],
    4 => ["Les deux adresses e-mail sont identiques.", "alert-danger"],
    5 => ["La nouvelle adresse e-mail est déjà utilisée par un autre utilisateur.", "alert-danger"]
];

// Récupérer le code d’erreur depuis l'URL
$codeErreur = isset($_GET['erreur']) ? (int)$_GET['erreur'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
<link rel="icon" href="img/sinje_magique.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Changer Email - Wacky Monkey's Words</title>
	<meta name="description" content="Venez Jouez à Wacky Monkey's Words (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Wacky Monkey's Words ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
	<meta name="keywords" content="Wacky Monkey's Words, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/form.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/3f3ecfc27b.js"></script>
</head>
<body class="black">
    <a class="btn btn-light mb-3" href="profil.php">Retour&emsp;<i class="fa-solid fa-left-long"></i></a>
    <main class="position-absolute top-50 start-50 translate-middle">
        <div class="glassmorphism">
            <h1 class="title">Changer l'adresse email</h1>
            <form action="script-email.php" method="POST">
                <div class="input-field">
                    <input type="email" name="email1" id="email1" required>
                    <label for="email1">Ancienne adresse email :</label>
                </div>
                <div class="input-field">
                    <input type="email" name="email2" id="email2" required>
                    <label for="email2">Nouvelle adresse email :</label>
                </div>
                <button id="formButton" type="submit" class="btn fw-semibold">Valider</button>
            </form>
            <?php
            // Si le message d'erreur est différent de 0
            if ($codeErreur > 0 && $codeErreur < 6) {
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
        if (msgCode > 0 && msgCode < 6) {
            // J'affiche le message d'erreur
            document.getElementById('msg-error').innerHTML = msgError;
            // Je change la couleur du message d'erreur
            document.getElementById('msg-error').classList.add(<?php echo json_encode($messagesErreur[$codeErreur][1]); ?>);
            document.getElementById('msg-error').classList.add('visible');
            // Après l'expiration du cookie, on actualise la page pour le supprimer
            setTimeout(function () {
                window.location.href = 'change_email.php';
            }, 10000);
        }
    </script>
</body>
</html>