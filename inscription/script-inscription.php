<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    //Connexion à la BDD
    include '../conf.bkp.php';

    $pseudo = $_POST["pseudo"];
    $email = $_POST["email"];
    $annee_naissance = $_POST["annee_naissance"];
    $motdepasse1 = $_POST["motdepasse1"];
    $motdepasse2 = $_POST["motdepasse2"];

    //Vérifier si le pseudo n'existe pas dans la base de données
    $query_pseudo_exists = "SELECT COUNT(*) FROM SAE_USERS WHERE pseudo = :pseudo";
    $stmt_pseudo_exists = $cnx->prepare($query_pseudo_exists);
    $stmt_pseudo_exists->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $stmt_pseudo_exists->execute();
    $pseudo_exists = $stmt_pseudo_exists->fetchColumn();

    if ($pseudo_exists) { // Si le pseudo existe déjà
        header('Location: inscription.php?erreur=1');
        exit;
    }

    //Vérifier si l'email n'existe pas dans la base de données
    $query_email_exists = "SELECT COUNT(*) FROM SAE_USERS WHERE email = :email";
    $stmt_email_exists = $cnx->prepare($query_email_exists);
    $stmt_email_exists->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt_email_exists->execute();
    $email_exists = $stmt_email_exists->fetchColumn();

    if ($email_exists) {
        header('Location: inscription.php?erreur=2');
        exit;
    }

    //Les mots de passe sont-ils identiques ?
    if ($motdepasse1 != $motdepasse2) {
        header('Location: inscription.php?erreur=3');
        exit;
    }
    // Vérifier si le nouveau mot de passe respecte les règles de la CNIL
    if (strlen($motdepasse1) < 12 && !preg_match('/[0-9]/', $motdepasse1) && !preg_match('/[A-Z]/', $motdepasse1) && !preg_match('/[a-z]/', $motdepasse1) && !preg_match('/[^a-zA-Z0-9]/', $motdepasse1)) {
        header("Location: inscription.php?erreur=4");
        exit;
    }

    //Vérifier si l'année est cohérente
    $annee_naissance = $_POST["annee_naissance"];

    // Vérifier si l'année de naissance est valide
    if ($annee_naissance < 1930 || $annee_naissance > 2017) {
        header('Location: inscription.php?erreur=6');
        exit;
    }

    $salt = random_bytes(16); // Générer un sel aléatoire
    $iterations = 5000; // Tu peux ajuster le nombre d'itérations selon tes besoins

    $motdepasse = hash_pbkdf2("sha256", $motdepasse1, $salt, $iterations, 32); // hachage du mot de passe

    // Préparer la requête SQL d'insertion
    $query = "INSERT INTO SAE_USERS (pseudo, email, annee_naissance, motdepasse, salt, statut) VALUES (:pseudo, :email, :annee_naissance, :mot_de_passe, :salt, 0)";
    
    // Exécuter la requête avec des paramètres
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->bindParam(":annee_naissance", $annee_naissance, PDO::PARAM_STR);
    $stmt->bindParam(":mot_de_passe", $motdepasse, PDO::PARAM_STR);
    $stmt->bindParam(":salt", $salt);
    $stmt->execute();

    // Récupération de l'ID de l'utilisateur nouvellement inséré
    $num_user = $cnx->lastInsertId();

    // Journalisation
    include '../includes/fonctions.php';
    trace($num_user, 'Inscription au Site', $cnx);

    // Génération d'un code de confirmation (peut être un jeton unique)
    $code_confirmation = bin2hex(random_bytes(16));

    // Enregistrement du code de confirmation dans la base de données
    $query_insert_confirmation = "INSERT INTO SAE_CONFIRMATION_CODES (num_user, code) VALUES (:num_user, :code)";
    $stmt_insert_confirmation = $cnx->prepare($query_insert_confirmation);
    $stmt_insert_confirmation->bindParam(":num_user", $num_user, PDO::PARAM_INT);
    $stmt_insert_confirmation->bindParam(":code", $code_confirmation);
    $stmt_insert_confirmation->execute();

    include '../mail/mailer.php';
    $mail->addAddress($email, $pseudo);

    // Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation d\'inscription';
    $mail->Body = "Bienvenue $pseudo sur notre site !<br><br>Veuillez confirmer votre inscription en cliquant sur le lien suivant : <a href='http://perso-etudiant.u-pem.fr/~thamiz.sarboudine/SAE/SAE_SEMANTIC/inscription/script-confirmermail.php?code=$code_confirmation'>Confirmer</a>";
    $mail->CharSet = 'UTF-8';
    
    // Envoi du mail
    $mail->send();

    header('Location: inscription.php?erreur=5');
    exit;
}
?>
