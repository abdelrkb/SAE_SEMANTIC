<?php
//PHPMailer
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Configurer PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $host;
$mail->Port = 465;
$mail->SMTPSecure = 'ssl';

$mail->SMTPAuth = true; // Activer l'authentification SMTP
$mail->Username = $username;
$mail->Password = $password;
$mail->CharSet = 'UTF-8';

$mail->setFrom($username, $name);
?>