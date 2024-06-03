<?php
function randomWord($filename)
{
    // Lire le contenu du fichier dans une chaîne
    $content = file_get_contents($filename);

    // Supprimer les espaces éventuels et diviser la chaîne en un tableau de mots
    $listeDeMots = explode(';', trim($content));

    // Obtenir la longueur du tableau
    $nbMots = count($listeDeMots);

    // Générer un indice aléatoire pour sélectionner un mot au hasard
    $indiceMot = rand(0, $nbMots - 1);

    // Récupérer le mot correspondant à l'indice généré
    $mot = $listeDeMots[$indiceMot];

    return $mot;
}

function startGame()
{
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
}

function isWordBanned($word) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $fichier_resultat = fopen("partie/resultjava_$_SESSION[pseudo].txt", 'r');
    if (!$fichier_resultat) {
        return false; // Ou vous pouvez gérer l'erreur comme vous le souhaitez
    }

    $isBanned = false;
    $readingBannedWords = false;

    while (($ligne = fgets($fichier_resultat)) !== false) {
        $ligne = trim($ligne);

        if (strpos($ligne, "Mots bannis:") !== false) {
            $readingBannedWords = true;
            continue;
        }

        if ($readingBannedWords) {
            if (empty($ligne)) {
                break; // On sort de la boucle si on rencontre une ligne vide après "Mots bannis:"
            }

            if ($ligne === $word) {
                $isBanned = true;
                break;
            }
        }
    }

    fclose($fichier_resultat);
    return $isBanned;
}


function ajouterPaire($tableau, $mot1, $mot2)
{ // Ajoute une paire de mots au tableau
    $distance = null;
    $recherche = $mot1 . "-" . $mot2;
    $recherche2 = $mot2 . "-" . $mot1;
    $fichier_partie = fopen("partie/game_data_$_SESSION[pseudo].txt", 'r');
    while (($ligne = fgets($fichier_partie)) !== false) {
        if (((strpos($ligne, $recherche) !== false) || (strpos($ligne, $recherche2) !== false)) && strpos($ligne, "distance: ") !== false) {
            // Extrait la valeur de la distance de la ligne
            if ((strpos($ligne, $recherche) !== false)) {
                $distanceTexte = trim(str_replace("$mot1-$mot2, distance: ", "", $ligne));
            }
            if (strpos($ligne, $recherche2) !== false) {
                $distanceTexte = trim(str_replace("$mot2-$mot1, distance: ", "", $ligne));
            }
            $distance = floatval($distanceTexte);
            break;
        }
    }
    fclose($fichier_partie);

    $nouvellePaire = ["mot1" => $mot1, "mot2" => $mot2, "nombre" => $distance];
    $tableau[] = $nouvellePaire;
    return $tableau;
}

function calculateScore()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['scores'];
}

function createDataForGraph($paires) {
    // Utilisation d’un tableau associatif pour stocker les relations entre les mots
    $relations = [];

    foreach ($paires as $paire) {
        $mot1 = $paire["mot1"];
        $mot2 = $paire["mot2"];
        $nombre = $paire["nombre"];

        // Si la relation entre mot1 et mot2 n'existe pas encore
        if (!isset($relations[$mot1][$mot2])) {
            $relations[$mot1][$mot2] = $nombre;
        } else {
            // Si la relation existe déjà, additionner le nombre
            $relations[$mot1][$mot2] += $nombre;
        }
    }

    $nodes = [];
    $links = [];

    // Création des nodes et des links à partir des relations
    foreach ($relations as $mot1 => $rel) {
        $mot1 = ucfirst($mot1);
        $nodes[] = ["id" => $mot1];

        foreach ($rel as $mot2 => $nombre) {
            $mot2 = ucfirst($mot2);
            $nodes[] = ["id" => $mot2];
            $links[] = [
                "source" => $mot1,
                "target" => $mot2,
                "linkTextPath" => $nombre
            ];
        }
    }

    return [
        "nodes" => $nodes,
        "links" => $links
    ];
}

?>