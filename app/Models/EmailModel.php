<?php

namespace App\Models;

use App\Models\ApiModel;
use CodeIgniter\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\ParametroModel;

class EmailModel extends Model
{
    public function ApiEmail($_petitions = [], $archivo = "", $action = false)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->IsSMTP();
            
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;
            $mail->CharSet = "UTF-8";
            $mail->Username = (new ParametroModel())->obtenerParametro("CORREO_ELECTRONICO", false);
            $mail->Password = (new ParametroModel())->obtenerParametro("CONTRASENA_CORREO_ELECTRONICO", false);

            $mail->isHTML(true);            
            $mail->Subject = '';
            $mail->Body    = '';
            $mail->AltBody = '';

            foreach($_petitions as $item){

                switch ($item->name) {
                    case "_correo":
                        $mail->addAddress($item->value); 
                        break;
                    case "_subject":
                        $mail->Subject = $item->value;
                        break;
                    case "_body":
                        $mail->Body =  $item->value;
                        break;    
                    case "_altBody":
                        $mail->AltBody = $item->value;
                        break;
                }
            }

            $mail->setFrom($mail->Username, 'MENSAJE CATASTRO');

            if(strlen($archivo) > 0) $mail->addAttachment($archivo);
            $mail->send();

            return (new ApiModel())->ClientResponse([], [] ,200, "Correo enviado con éxito.");

        } catch (Exception $e) {
            throw new \Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}