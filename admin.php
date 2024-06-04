<?php
    $isAdmin = $_SESSION['admin'];

    if ($isAdmin) {
        echo '<div id="adminToast" class="toast">Vous êtes connecté en tant qu’administrateur</div>';
        echo '<script>document.addEventListener("DOMContentLoaded", function() { showToast(); });</script>';
    }
    ?>