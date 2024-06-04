<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldPassword = $_SESSION['password1'];
    $newPassword = $_SESSION['password2'];
    $confirmPassword = $_SESSION['password3'];

    $_SESSION['msgPassword'] = "";

    include("../conf.bkp.php");
     // Vérifier si le code correspond et n'a pas expiré
     $user_input_code = $_POST['verification_code'];
     $stored_code = $_SESSION['verification_code'];
     $code_time = $_SESSION['verification_time'];

     if ($user_input_code != $stored_code || (time() - $code_time) > 600) { // 600 secondes = 10 minutes
        header('Location: change_password.php?erreur=6'); // Code incorrect ou expiré
        exit;
    }

    // Vérifier si l'ancien mot de passe est correct
    $sql = "SELECT num_user, motdepasse, salt, email FROM SAE_USERS WHERE pseudo = :pseudo";
    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
    $stmt->execute();
    $user = $stmt->fetch();
    $stmt->closeCursor();

    //Pour la journalisation, on récupère le num_user
    $num_user = $user['num_user'];

    // L'utilisateur existe maintenant, tu peux vérifier le mot de passe
    $stored_motdepasse = $user['motdepasse']; // Mot de passe stocké dans la base de données
    $stored_salt = $user['salt']; // Sel stocké dans la base de données

    // Recalculer le hachage avec le sel
    $hashed_input_motdepasse = hash_pbkdf2("sha256", $oldPassword, $stored_salt, 5000, 32);

    // Comparer les mots de passe hachés
    if ($hashed_input_motdepasse === $stored_motdepasse) {
        // Vérifier si le nouveau mot de passe est différent de l'ancien
        if ($oldPassword === $newPassword) {
            header('Location: change_password.php?erreur=4');
            exit;
        }
        // Vérifier si les nouveaux mots de passe correspondent entre eux
        if ($newPassword !== $confirmPassword) {
            header('Location: change_password.php?erreur=3');
            exit;
        }
        // Vérifier si le nouveau mot de passe respecte les règles de la CNIL
        $passwordError = "";
        if (strlen($newPassword) < 12) {
            header("Location: change_password.php?erreur=5");
            exit;
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            header("Location: change_password.php?erreur=5");
            exit;
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            header("Location: change_password.php?erreur=5");
            exit;
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            header("Location: change_password.php?erreur=5");
            exit;
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
            header("Location: change_password.php?erreur=5");
            exit;
        }
        if (strlen($newPassword) < 12 && !preg_match('/[0-9]/', $newPassword) && !preg_match('/[A-Z]/', $newPassword) && !preg_match('/[a-z]/', $newPassword) && !preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
            header("Location: change_password.php?erreur=5");
            exit;
        }

        // Générer un nouveau sel
        $new_salt = random_bytes(16);
        // Hacher le mot de passe avec le nouveau sel
        $hashed_new_motdepasse = hash_pbkdf2("sha256", $newPassword, $new_salt, 5000, 32);

        // Mettre à jour le mot de passe et le sel dans la base de données
        $stmt = $cnx->prepare("UPDATE SAE_USERS SET motdepasse = :motdepasse, salt = :salt WHERE pseudo = :pseudo");
        $stmt->bindParam(':motdepasse', $hashed_new_motdepasse);
        $stmt->bindParam(':salt', $new_salt);
        $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
        $stmt->execute();
        $stmt->closeCursor();

        //Mail
        include '../mail/mailer.php';

        $mail->addAddress($user['email']);

        $mail->isHTML(true);
        $mail->Subject = "Changement de mot de passe";
        $mail->Body =
            "Bonjour $_SESSION[pseudo],<br>
        Votre mot de passe a été modifié. Si vous n'êtes pas à l'origine de cette modification, veuillez contacter l'administrateur du site.<br>
        Cordialement,<br>
        L'équipe de Wacky Monkey's Words.";
        $mail->CharSet = 'UTF-8';

        $mail->send();

        // Journalisation
        include '../includes/fonctions.php';
        trace($num_user, 'Changement de mot de passe', $cnx);

        // Rediriger vers la page de connexion avec un message
        header('Location: change_password.php?erreur=1');
    } else {
        // L'ancien mot de passe est incorrect
        header('Location: change_password.php?erreur=2');
    }
    exit;
} else {
    header('Location: change_password.php');
    exit;
}
