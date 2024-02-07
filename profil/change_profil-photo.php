<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $imageData = file_get_contents($_FILES["image"]["tmp_name"]);
        $imageType = $_FILES["image"]["type"];

        include("../conf.bkp.php");
        $stmt = $cnx->prepare("UPDATE SAE_USERS SET image_data = :image_data, image_type = :image_type WHERE num_user = :num_user");

        if ($stmt->execute()) {
            echo "L'image a été téléversée avec succès.";
        } else {
            echo "Une erreur s'est produite lors du téléversement de l'image.";
        }
    } else {
        echo "Erreur lors du téléversement de l'image.";
    }
}
?>
