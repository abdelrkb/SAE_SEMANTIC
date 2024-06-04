<?php

include_once('code_secret.php');

$code = $_POST['code'];
echo $code;

if ($code == $code_secret) {
    header('Location: http://localhost:3000/');
} else {
    header('Location: home.php');
}

?>