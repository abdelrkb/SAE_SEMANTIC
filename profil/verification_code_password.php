<?php
session_start();

// Utilisateur connecté ?
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../');
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../conf.bkp.php");

    $_SESSION['password1'] = $_POST['password1'];
    $_SESSION['password2'] = $_POST['password2'];
    $_SESSION['password3'] = $_POST['password3'];

    if (isset($_SESSION['pseudo'])) {
        $pseudo = $_SESSION['pseudo'];
        // Vérifier si l'ancien mot de passe est correct
        $sql = "SELECT num_user, email FROM SAE_USERS WHERE pseudo = :pseudo";
        $stmt = $cnx->prepare($sql);
        $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
        $stmt->execute();
        $user = $stmt->fetch();
        $stmt->closeCursor();
        $user_email = $user['email'];
        
        $verification_code = random_int(100000, 999999); // Générer un code à 6 chiffres

        $_SESSION['verification_code'] = $verification_code; // Stocker le code dans la session
        $_SESSION['verification_time'] = time(); // Stocker le temps de la demande

        // Envoyer le code par email
        include '../mail/mailer.php';
        $mail->addAddress($user_email);
        $mail->isHTML(true);
        $mail->Subject = "Votre Code de Vérification";
        $mail->Body = "Votre code de vérification pour changer votre mot de passe est : $verification_code <br><br>Si vous n'êtes pas à l'origine de cette modification, veuillez changer
        votre mot de passe immédiatement et/ou contacter l'administrateur du site !";
        $mail->send();
        
        header('Location: confirm_change_password.php'); // Rediriger l'utilisateur vers la page de vérification du code
    } else {
        header('Location: ../');
        exit;
    }
}
?>
