<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

function recuperacion($email, $token)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ramirobelfiore@gmail.com';
        $mail->Password   = ''; // Agregar password para que funcione
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom("ramirobelfiore@gmail.com", 'Academia');
        $mail->addAddress($email, $token);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject =  'Recuperación de Cuenta';
        $cuerpo = '<h4>Has solicitado una recuperación de la cuenta en Academia.</h4>';
        $cuerpo .= '<h4>Sí ha realizado dicha solicitud, haga click en el siguiente enlace para restablecer su contraseña: <a style="text-decoration: none;" href="http://localhost/academia/reset_password.php?token=' . $token . '">Recuperar Cuenta</a></h4>';
        $cuerpo .= '<h5>Sí no ha realizado esta solicitud, ignore este correo electrónico.</h5>';
        $cuerpo .= '<p>Sí tenés otra duda/consulta, no dudes en consultarnos en <a style="text-decoration: none;" href="http://localhost/academia/contacto.php">Academia</a>, ó envianos un mail a este mismo correo: <b>academia@gmail.com</b></p>';
        $mail->Body = utf8_decode($cuerpo);
        $mail->AltBody = 'Recuperación de Cuenta';
        $mail->setLanguage('es', '../PHPMailer/language/phpmailer.lang-es.php');
        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo electrónico: {$mail->ErrorInfo} | " . $e;
        exit;
    }
}