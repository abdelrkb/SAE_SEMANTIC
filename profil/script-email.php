<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldEmail = $_POST['email1'];
    $newEmail = $_POST['email2'];

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['pseudo'])) {
        header('Location: ../');
        exit;
    }

    // Vérifier si les deux emails sont identiques
    if ($oldEmail == $newEmail) {
        header('Location: change_email.php?erreur=4');
        exit;
    }

    include("../conf.bkp.php"); // Connexion à la base de données
    // Vérifier si l'ancien email est correct
    $sql = "SELECT email FROM SAE_USERS WHERE pseudo = :pseudo";
    $stmt = $cnx->prepare($sql);
    $stmt->execute(['pseudo' => $_SESSION['pseudo']]);
    $user = $stmt->fetch();
    $stmt->closeCursor();

    // Vérifier si l'ancien email est correct
    if ($user['email'] != $oldEmail) {
        header('Location: change_email.php?erreur=2');
        exit;
    }

    // Vérifier si le nouvel email est déjà utilisé
    $sql = "SELECT email FROM SAE_USERS WHERE email = :email";
    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':email', $newEmail);
    $stmt->execute();
    $user = $stmt->fetch();
    $stmt->closeCursor();

    if ($user) {
        header('Location: change_email.php?erreur=5');
        exit;
    }

    // Modifier l'email
    $sql = "UPDATE SAE_USERS SET email = :email WHERE pseudo = :pseudo";
    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':email', $newEmail);
    $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
    $stmt->execute();
    $stmt->closeCursor();

    //Mail
    include '../mail/mailer.php';

    $mail->addAddress($newEmail);
    $mail->isHTML(true);
    $mail->Subject = "Changement d'email";
    $mail->Body = "
    Bonjour $_SESSION[pseudo],<br>
    Votre adresse e-mail a été modifiée. Si vous n'êtes pas à l'origine de cette modification, veuillez contacter l'administrateur du site.<br>
    Cordialement,<br>
    L'équipe de Wacky Monkey's Words.";
    $mail->CharSet = 'UTF-8';

    $mail->send();

    // Effacer les adresses précédentes
    $mail->clearAddresses();


    $mail->addAddress($oldEmail);
    $mail->isHTML(true);
    $mail->Subject = "Changement d'email";
    $mail->Body = "
    Bonjour $_SESSION[pseudo],<br>
    Votre adresse e-mail a été modifiée. Si vous n'êtes pas à l'origine de cette modification, veuillez contacter l'administrateur du site.<br>
    Cordialement,<br>
    L'équipe de Wacky Monkey's Words.";
    $mail->CharSet = 'UTF-8';

    $mail->send();

    // Journalisation
    $sql_num_user = "SELECT num_user FROM SAE_USERS WHERE pseudo = :pseudo";
    $stmt_num_user = $cnx->prepare($sql_num_user);
    $stmt_num_user->execute(['pseudo' => $_SESSION['pseudo']]);
    $user_num_user = $stmt_num_user->fetch();
    $stmt_num_user->closeCursor();

    $num_user = $user_num_user['num_user'];

    include '../includes/fonctions.php';
    trace($num_user, "Changement d'adresse email", $cnx);

    header('Location: change_email.php?erreur=1');
    exit;
}