<?php
session_start();
// erreur php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Utilisateur déjà connecté ?
if (isset($_SESSION['pseudo'])) {
    header('Location: ../home.php');
    exit;
}

include '../conf.bkp.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = $_POST["pseudo"];
    $motdepasse = $_POST["motdepasse"];

    // Rechercher l'utilisateur dans la base de données
    $query_select_user = "SELECT * FROM SAE_USERS WHERE pseudo = :pseudo";
    $stmt_select_user = $cnx->prepare($query_select_user);
    $stmt_select_user->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $stmt_select_user->execute();
    $user = $stmt_select_user->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if ($user) {
        $_SESSION['pseudo_temp'] = $pseudo;
        //Utilisateur à vérifié son email ?
        if($user['statut']==1) {
            // L'utilisateur existe, maintenant tu peux vérifier le mot de passe
            $stored_motdepasse = $user['motdepasse']; // Mot de passe stocké dans la base de données
            $stored_salt = $user['salt']; // Sel stocké dans la base de données

            // Recalculer le hachage avec le sel
            $hashed_input_motdepasse = hash_pbkdf2("sha256", $motdepasse, $stored_salt, 5000, 32);

            // Comparer les hachages
            if ($hashed_input_motdepasse === $stored_motdepasse) {
                // Mot de passe correct
                $_SESSION['pseudo'] = $pseudo;
                $_SESSION['num_user'] = $user['num_user'];

                include '../mail/mailer.php';

                //Contenu du mail
                $mail->isHTML(true);
                $mail->Subject = 'Connexion';
                $mail->Body = "Bonjour ". $_SESSION['pseudo'] . ", Vous venez de vous connecter sur notre site ! <br><br> Si vous n'êtes pas à l'origine de cette connexion, veuillez changer immédiatement votre mot de passe !";

                $mail->addAddress($user['email'], $_SESSION['pseudo']);
                $mail->Content = 'UTF-8';
                //Envoi du mail
                $mail->send();

                $num_user = $user['num_user'];

                // Journalisation
                include '../includes/fonctions.php';
                trace($num_user, 'Connexion au Site', $cnx);

                // Admin 
                $stmt = $cnx->prepare('SELECT admin FROM SAE_USERS WHERE num_user = :id');
                $stmt->bindParam(':id', $_SESSION['num_user'], PDO::PARAM_INT);
                $stmt->execute();

                $isAdmin = $stmt->fetchColumn();
                $_SESSION['admin'] = $isAdmin;
                
                header('Location: ../home.php');
                exit;
            } else {
                // Mot de passe incorrect
                header('Location: ../index.php?erreur=2');
                exit;
            }
        } else {
            // Demander à l'utilisateur de verifier son email
            include "../mail/envoyer_mail_token.php";
            header("Location: ../index.php?erreur=7");
            exit;
        }
    } else {
        //L'utilisateur n'existe pas
        header('Location: ../index.php?erreur=1');
        exit;
    }
}
?>