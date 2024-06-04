<?php
    session_start();
    include '../conf.bkp.php';

    if (isset($_GET['code'])) {
        $code_confirmation = $_GET['code'];
    
        // Vérifier le code dans la table code_confirmation
        $query_check_code = "SELECT * FROM SAE_CONFIRMATION_CODES WHERE code = :code";
        $stmt_check_code = $cnx->prepare($query_check_code);
        $stmt_check_code->bindParam(":code", $code_confirmation);
        $stmt_check_code->execute();
        $confirmation_data = $stmt_check_code->fetch(PDO::FETCH_ASSOC);
    
        if ($confirmation_data) {
            // Vérifier si le code n'a pas expiré (20 minutes dans cet exemple)
            $expiration_time = strtotime($confirmation_data['created_at']) + (20 * 60); // 20 minutes en secondes
            $current_time = time();

            if ($current_time <= $expiration_time) {
                // Mise à jour du statut à 1 dans la table users
                $query_update_statut = "UPDATE SAE_USERS SET statut = 1 WHERE num_user = :num_user";
                $stmt_update_statut = $cnx->prepare($query_update_statut);
                $stmt_update_statut->bindParam(":num_user", $confirmation_data['num_user']);
                $stmt_update_statut->execute();
        
                // Supprimer l'enregistrement dans la table code_confirmation
                $query_delete_confirmation = "DELETE FROM SAE_CONFIRMATION_CODES  WHERE code = :code";
                $stmt_delete_confirmation = $cnx->prepare($query_delete_confirmation);
                $stmt_delete_confirmation->bindParam(":code", $code_confirmation);
                $stmt_delete_confirmation->execute();

                // Journalisation
                include '../includes/fonctions.php';
                trace($confirmation_data['num_user'], "Confirmation d'inscription", $cnx);
        
                header('Location: ../index.php?erreur=4');
            } else {
                // Code de confirmation expiré, mais supprimer quand même l'enregistrement
                $query_delete_confirmation = "DELETE FROM SAE_CONFIRMATION_CODES WHERE code = :code";
                $stmt_delete_confirmation = $cnx->prepare($query_delete_confirmation);
                $stmt_delete_confirmation->bindParam(":code", $code_confirmation);
                $stmt_delete_confirmation->execute();

                header('Location: ../index.php?erreur=3');
                exit();
            }
        } else {
            // Code de confirmation invalide l’utilisateur déjà confirmé
            header('Location: ../index.php?erreur=5');
            exit();
        }
    }    
?>