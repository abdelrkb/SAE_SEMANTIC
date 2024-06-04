<?php
session_start();
// Messages d'erreurs possibles
$messagesErreur = [
    1 => ["Les mots de passes ne correspondent pas.", "alert-danger"],
    2 => ["Le mot de passe doit comporter au moins 12 caractères et contenir au minimum 1 lettre minuscule, 1 lettre majuscule, 1 chiffre et 1 caractère spécial.", "alert-danger"],
    3 => ["Une erreur est survenue lors de la mise à jour du mot de passe.", "alert-danger"]
];

// Récupérer le code d'erreur depuis l'URL
$codeErreur = isset($_GET['erreur']) ? (int)$_GET['erreur'] : 0;

if ($_GET['code']) {

    include '../conf.bkp.php';

    $code_reinitialisation = $_GET['code'];

    // Rechercher le code de réinitialisation dans la base de données
    $query_select_code = "SELECT * FROM SAE_RESET_CODE WHERE code = :code";
    $stmt_select_code = $cnx->prepare($query_select_code);
    $stmt_select_code->bindParam(":code", $code_reinitialisation);
    $stmt_select_code->execute();
    $reset_code = $stmt_select_code->fetch(PDO::FETCH_ASSOC);

    if ($reset_code) {
        //Code de réinitialisation trouvé, afficher alors le formulaire
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Réinistialiser mot de passe - Semantic Analogy Explorer</title>
			<meta name="description" content="Venez Jouez à Semantic Analogy Explorer (SAE), un jeu en ligne à un ou plusieurs joueurs basé sur les similarités entre mots : « Semantic Analogy Explorer ». Chaque joueur reçoit un mot de départ et un mot cible et propose des mots proches afin de créer une chaîne de mots similaires pour relier le mot de départ au mot cible. ">
			<meta name="keywords" content="Semantic Analogy Explorer, SAE, jeu, jeu en ligne, jeu de mots, jeu de lettres, jeu de lettres en ligne, jeu de mots en ligne, jeu de lettres multijoueur, jeu de mots multijoueur, jeu de lettres multijoueur en ligne, jeu de mots multijoueur en ligne, jeu de lettres multijoueur gratuit, jeu de mots multijoueur gratuit, jeu de lettres multijoueur gratuit en ligne, jeu de mots multijoueur gratuit en ligne, jeu de lettres multijoueur gratuit sans inscription, jeu de mots multijoueur gratuit sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription, jeu de mots multijoueur gratuit en ligne sans inscription, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de lettres multijoueur gratuit en ligne sans inscription et sans téléchargement, jeu de mots multijoueur gratuit en ligne sans inscription et sans téléchargement">
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
			<link rel="stylesheet" href="../css/style.css">
            <link rel="stylesheet" type="text/css" href="../css/form.css">
            <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </head>
        <body class="black">
        <main class="position-absolute top-50 start-50 translate-middle">
            <div class="glassmorphism">
                <h1 class="title">Définissez un nouveau mot de passe :</h1>
                <form action="traitement_reset_password.php" method="POST">
                    <input type="hidden" name="num_user" value="<?php echo $reset_code['num_user']; ?>">
                    <input type="hidden" name="code_reinitialisation"
                           value="<?php echo htmlspecialchars($code_reinitialisation); ?>">
                    <div class="input-field">
                        <input type="password" name="nouveau_mot_de_passe" id="nouveau_mot_de_passe" minlength="12"
                               required>
                        <label for="nouveau_mot_de_passe">Nouveau mot de passe :</label>
                    </div>
                    <div class="input-field">
                        <input type="password" name="confirmer_nouveau_mot_de_passe" id="confirmer_nouveau_mot_de_passe"
                               minlength="12" required>
                        <label for="confirmer_nouveau_mot_de_passe">Confirmer le nouveau mot de passe :</label>
                    </div>
                    <button id="formButton" type="submit" class="btn fw-semibold">Réinitialiser le mot de passe</button>
                </form>
                <?php
                // Si le message d'erreur est différent de 0
                if ($codeErreur > 0 && $codeErreur < 8) {
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
            if (msgCode > 0 && msgCode < 8) {
                // J'affiche le message d'erreur
                document.getElementById('msg-error').innerHTML = msgError;
                // Je change la couleur du message d'erreur
                document.getElementById('msg-error').classList.add(<?php echo json_encode($messagesErreur[$codeErreur][1]); ?>);
                // Après l'expiration du cookie, on actualise la page pour le supprimer
                setTimeout(function () {
                    window.location.href = '../forgotpassword/reset_password.php?code=<?php echo $code_reinitialisation; ?>';
                }, 10000);
            }
        </script>
        </body>
        </html>

        <?php

    } else {
        header('Location: forgot_password.php');
        exit;
    }
} else {
    header('Location: forgot_password.php');
    exit;
}
?>