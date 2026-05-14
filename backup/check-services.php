<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

    //check which servers are not reachable
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "smtp.office365.com";
    $mail->Port = "587"; // typically 587
    $mail->SMTPSecure = 'tls'; // ssl is depracated
    $mail->SMTPAuth = true;
    $mail->Username = "noreply@egitpro.com";
    $mail->Password = "Jad04108";
    $mail->setFrom("noreply@egitpro.com", "System Monitor");
    //$mail->addAddress("manisha@pluswealth.net", "manisha@pluswealth.net");
    $mail->addAddress("amit.mishra@pluswealth.net");
    $mail->addAddress("yogesh.dixit@pluswealth.net");
    $mail->addAddress("it.support@pluswealth.net");
    $mail->Subject = date('G:i:s').' SysMon: Service Status';
    $mail->msgHTML($msgbody); // remove if you do not want to send HTML email
    //$mail->AltBody = $message_txt;
    $mail->send();
    
//}
?>