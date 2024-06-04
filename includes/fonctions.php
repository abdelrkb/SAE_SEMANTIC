<?php
//Journalisation
function trace($userId, $action, $cnx)
{
    $query = "INSERT INTO SAE_TRACES (utilisateur_id, action, ip_adress) VALUES (:utilisateur_id, :action, :ip_adress)";
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(":utilisateur_id", $userId, PDO::PARAM_INT);
    $stmt->bindParam(":action", $action, PDO::PARAM_STR);
    $stmt->bindParam(":ip_adress", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();
}

// Formatage des dates
function makeDate($date)
{
    $date = date_create($date);
    return date_format($date, 'd/m/Y');
}

// Formatage des heures
function makeHour($date)
{
    $date = date_create($date);
    return date_format($date, 'H:i:s');
}

// Formatage des dates et heures
function makeDateTime($date)
{
    $date = date_create($date);
    return date_format($date, 'd/m/Y à H:i:s');
}

// Calcul le temps écoulé entre maintenant et une date donnée (date en format datetime)
function passedTime($dateHeure)
{
    $dateHeure = date_create($dateHeure);
    $now = date_create(date("Y-m-d H:i:s"));
    $diff = date_diff($dateHeure, $now);
    $passedTime = $diff->format("%d jours, %h heures, %i minutes et %s secondes");
    // Si le temps écoulé est inférieur à 1 jour, on ne retourne que les heures, minutes et secondes
    if ($diff->format("%d") == 0) {
        $passedTime = $diff->format("%h heures, %i minutes et %s secondes");
        // Si le temps écoulé est inférieur à 1 heure, on ne retourne que les minutes et secondes
        if ($diff->format("%h") == 0) {
            $passedTime = $diff->format("%i minutes et %s secondes");
            // Si le temps écoulé est inférieur à 1 minute, on ne retourne que les secondes
            if ($diff->format("%i") == 0) {
                $passedTime = $diff->format("%s secondes");
            }
        }
    }
    return $passedTime;
}

function addStyleTableRow($action): string
{
    // Casse
    $action = mb_strtolower($action);
    if ($action == "connexion au site") {
        return "table-success";
    } else if ($action == "déconnexion du site") {
        return "table-danger";
    } else if ($action == "confirmation d'inscription") {
        return "table-info";
    } else if ($action == "inscription au site") {
        return "table-info";
    } elseif ($action == "a joué une partie") {
        return "table-primary";
    } else {
        return "table-warning";
    }
}

function podiumClass(int $position) {
    if ($position == 1) {
        return "gold";
    } else if ($position == 2) {
        return "silver";
    } else if ($position == 3) {
        return "bronze";
    } else {
        return "";
    }
}

?>