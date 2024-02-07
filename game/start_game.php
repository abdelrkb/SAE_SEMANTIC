<?php
include("game_fonctions.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['paires'] = [];
$_SESSION['words'] = [];
$_SESSION['scores'] = 0;

$verif_mot1 = -1;
$verif_mot2 = -1;

while ($verif_mot1 == -1) {
    $mot1 = randomWord('Liste_mots.txt');
    $commande_verif_mot1 = './C/bin/dictionary_lookup C/arbre_lexicographique.lex ' . $mot1;
    $verif_mot1 = shell_exec($commande_verif_mot1);
}

while ($verif_mot2 == -1) {
    $mot2 = randomWord('Liste_mots.txt');
    while ($mot1 == $mot2) {
        $mot2 = randomWord('Liste_mots.txt');
    }
    $commande_verif_mot2 = './C/bin/dictionary_lookup C/arbre_lexicographique.lex ' . $mot2;
    $verif_mot2 = shell_exec($commande_verif_mot2);
}

$commande_start_game = './C/bin/new_game C/frWac_non_lem_no_postag_no_phrase_200_cbow_cut100.bin ' . $mot1 . ' ' . $mot2 . ' ' . $_SESSION['pseudo'];
exec($commande_start_game);

$_SESSION['words'][0] = $mot1;
$_SESSION['words'][1] = $mot2;
$_SESSION['paires'] = ajouterPaire($_SESSION['paires'], $_SESSION['words'][0], $_SESSION['words'][1]);

// Création du fichier de résultat java le 'resultjava_pseudo.txt' avec les droits rw-rw-rw-
$fichier_resultat = fopen("partie/resultjava_$_SESSION[pseudo].txt", 'w');
// On écrit les 3 premières lignes du fichier
fwrite($fichier_resultat, "Score minimal: " . $_SESSION['paires'][0]['nombre'] . "\n");
fwrite($fichier_resultat, "Dernier mot ajouté: true\n");
fwrite($fichier_resultat, $_SESSION['paires'][0]['mot1'] . "-" . $_SESSION['paires'][0]['mot2'] . ", " . $_SESSION['paires'][0]['nombre'] . "\n");
fclose($fichier_resultat);

header('Location: game.php');
?>