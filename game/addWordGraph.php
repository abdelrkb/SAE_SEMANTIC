<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    // Erreur PHP
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['pseudo'])) {
        header('Location: ../home.php');
        exit();
    }
    $pseudo = $_SESSION['pseudo'];
    if (!isset($_POST['paires']) && in_array($_POST['word'], $_SESSION['words'])) {
        header('Location: game.php?erreur=2');
        exit();
    }
    include("game_fonctions.php");
    $newWord = strtolower($_POST['word']);

    if (in_array($newWord, $_SESSION['words'])) {
        header('Location: game.php');
        exit();
    }

    $commande_verif_mot = './C/bin/dictionary_lookup C/arbre_lexicographique.lex ' . $newWord;
    $verif_mot = shell_exec($commande_verif_mot);

    if ($verif_mot == -1) {
        header('Location: game.php?erreur=1');
        exit();
    }
    $_SESSION['words'][] = $newWord;
    unset($_POST['word']);

    $commande_add_word = './C/bin/add_word C/frWac_non_lem_no_postag_no_phrase_200_cbow_cut100.bin ' . $newWord . ' ' . $_SESSION['pseudo'];
    exec($commande_add_word);
    // Java : trier les paires
    $commandeJar = "../../../jdk-21.0.3/bin/java -cp java/target/classes sae.Main partie/resultjava_$_SESSION[pseudo].txt partie/game_data_" .$_SESSION["pseudo"]. ".txt 2>&1";
    exec($commandeJar);
    if (!ifLastWordAdd()) {
        header("Location: game.php?erreur=3");
        exit();
    }

    $_SESSION['paires'] = [];
    $cheminFichier = "partie/resultjava_$pseudo.txt";
    $fichier = fopen($cheminFichier, "r"); // Ouvre le fichier en lecture
    // Vérifie si le fichier est ouvert avec succès
    if ($fichier) {
        // Lit la première ligne de type "Score" et récupère le score
        $ligne = fgets($fichier);
        $ligne = explode("Score: ", $ligne);
        if ($_SESSION['scores'] < trim($ligne[1])) {
            $_SESSION['scores'] = trim($ligne[1]);
        }

        // Ignore les deux lignes suivantes "Mots de départ :" et le mot de départ
        fgets($fichier);
        fgets($fichier);

        // Lit et traite chaque ligne à partir de la ligne "Distance entre les mots :"
        while (($ligne = fgets($fichier)) !== false) {
            // Ignore les lignes non pertinentes
            if (strpos($ligne, 'distance:') !== false) {
                // Exemple ligne : vin_coriandre, distance: 35.45
                $ligne = explode(", distance: ", $ligne);
                $addingWords = trim($ligne[0]);
                $addingWords = explode("_", $addingWords);
                $_SESSION['paires'][] = ["mot1" => trim($addingWords[0]), "mot2" => trim($addingWords[1]), "nombre" => trim($ligne[1])];
            }
        }
        // Ferme le fichier
        fclose($fichier);
    } else {
        // Gestion d'erreur si le fichier ne peut pas être ouvert
        echo "Impossible d'ouvrir le fichier.";
    }
    header('Location: game.php');
    exit();
}
?>