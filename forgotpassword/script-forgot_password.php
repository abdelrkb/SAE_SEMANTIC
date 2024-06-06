<?php
session_start();
// Utilisateur connecté ?
if (isset($_SESSION['pseudo'])) {
    header('Location: ../home.php');
    exit;
}

include '../conf.bkp.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = $_POST['pseudo'];
    $email = $_POST['email'];

    // Rechercher l'utilisateur dans la base de données
    $query_select_user = "SELECT * FROM SAE_USERS WHERE pseudo = :pseudo AND email = :email";
    $stmt_select_user = $cnx->prepare($query_select_user);
    $stmt_select_user->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $stmt_select_user->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt_select_user->execute();
    $user = $stmt_select_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        //Pseudo et email correspondent

        //Générer un code de réinitialisation
        $code_reinitialisation = bin2hex(random_bytes(16));

        // Enregistrement du code de réinitialisation dans la base de données
        $query_insert_code = "INSERT INTO SAE_RESET_CODE (num_user, code) VALUES (:num_user, :code)";
        $stmt_insert_code = $cnx->prepare($query_insert_code);
        $stmt_insert_code->bindParam(":num_user", $user['num_user']);
        $stmt_insert_code->bindParam(":code", $code_reinitialisation);
        $stmt_insert_code->execute();

        // Configurer PHPMailer
        include '../mail/mailer.php';

        $mail->addAddress($email, $pseudo);

        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de votre mot de passe';
        $mail->Body = "Bonjour $pseudo,<br><br>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien suivant pour choisir un nouveau mot de passe : <a href='http://perso-etudiant.u-pem.fr/~thamiz.sarboudine/Projet-SAE/forgotpassword/reset_password.php?code=$code_reinitialisation'>Réinitialiser le mot de passe</a>";
        $mail->CharSet = 'UTF-8';
        $mail->send();

        // Journalisation
        include '../includes/fonctions.php';
        trace($user['num_user'], 'Mot de passe oublié', $cnx);

        header('Location: forgot_password.php?erreur=1');
        exit;
    }
}

?>