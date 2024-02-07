<?php
include 'conf.php';

try {
    $cnx = new PDO('mysql:host=sqletud.u-pem.fr;dbname=chamsedine.amouche_db;charset=utf8mb4', $user, $pass);
}
catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
}

?>