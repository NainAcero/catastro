<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Generador de codigo para enviar por correo
     * @param longitud del codigo
     * @return codigo para enviar por correo
     */
	public function genera_codigo($longitud) {
		$caracteres = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
		$codigo = '';
	
		for ($i = 1; $i <= $longitud; $i++) $codigo .= $caracteres[$this->numero_aleatorio(0, 9)];
		return $codigo;
	}
    
    /**
     * Generador de número aleatorio
     */
	public function numero_aleatorio($ninicial, $nfinal) {
		$numero = rand($ninicial, $nfinal);
		return $numero;
	}

	public function mensaje_correo($codigo, $_time, $_exp, $url){

        $time = $_time . " minutos ";

        if($_exp) $time = "24 horas ";

		$message = '
            <html>
            <head>
                <meta name="viewport" content="width=device-width">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta charset="ISO-8859-1">
                <title>SMTP CATASTRO</title>
                <style>
                @media only screen and (max-width: 620px) {
                    table[class=body] h1 {
                    font-size: 28px !important;
                    margin-bottom: 10px !important;
                    }
            
                    table[class=body] p,
                    table[class=body] ul,
                    table[class=body] ol,
                    table[class=body] td,
                    table[class=body] span,
                    table[class=body] a {
                    font-size: 16px !important;
                    }
            
                    table[class=body] .wrapper,
                    table[class=body] .article {
                    padding: 10px !important;
                    }
            
                    table[class=body] .content {
                    padding: 0 !important;
                    }
            
                    table[class=body] .container {
                    padding: 0 !important;
                    width: 100% !important;
                    }
            
                    table[class=body] .main {
                    border-left-width: 0 !important;
                    border-radius: 0 !important;
                    border-right-width: 0 !important;
                    }
            
                    table[class=body] .btn table {
                    width: 100% !important;
                    }
            
                    table[class=body] .btn a {
                    width: 100% !important;
                    }
            
                    table[class=body] .img-responsive {
                    height: auto !important;
                    max-width: 100% !important;
                    width: auto !important;
                    }
                }
                @media all {
                    .ExternalClass {
                    width: 100%;
                    }
            
                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                    line-height: 100%;
                    }
            
                    .apple-link a {
                    color: inherit !important;
                    font-family: inherit !important;
                    font-size: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                    }
            
                    #MessageViewBody a {
                    color: inherit;
                    text-decoration: none;
                    font-size: inherit;
                    font-family: inherit;
                    font-weight: inherit;
                    line-height: inherit;
                    }
            
                    .btn-primary table td:hover {
                    background-color: #34495e !important;
                    }
            
                    .btn-primary a:hover {
                    background-color: #34495e !important;
                    border-color: #34495e !important;
                    }
                }
                </style>
            </head>';
            
            $message.= '<body
                style="background-color: #f6f6f6; font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <span class="preheader"
                style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Código de restablecimiento</span>
                <table border="0" cellpadding="0" cellspacing="0" class="body"
                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
                <tr>
                    <td style="font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
                    <td class="container"
                    style="font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
                    <div style="padding: 13px 0;text-align: center;">
                        <img src="cid:logo_ciis" width="140" heigth="42">
                    </div>
                    <div class="content"
                        style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 550px; padding: 10px;text-align: center;">
                        <table class="main"
                        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">
                        <tr>
                            <td class="wrapper"
                            style="font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 50px 40px;color: #363b3e;">
                            <table border="0" cellpadding="0" cellspacing="0"
                                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                <tr>
                                <td style=" font-size: 14px; vertical-align: top;">
                                    <p
                                    style="font-size: 30px; font-weight: bold; margin: 0; Margin-bottom: 25px;">
                                    Mensaje de restauración de contraseña al sistema CATASTRO</p>
                                    <p
                                    style="font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
                                    Código para restablecer tu contraseña.</p>
                                    <p
                                    style="font-size: 30px; font-weight: bold; margin: 20px 0;">
                                    '.$codigo.'</p>

                                    <p style="font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Este código tiene '.$time.' de validez</p>
                                    <a href="'.$url.'/recover-password" target="_blank"
                                        style="display: inline-block; color: #ffffff; background-color: #3f4ce7; border: solid 1px #3f4ce7; border-radius: 2px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 15px 30px;border-color: #3f4ce7;">Restablece tu contraseña</a>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                        <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                            <tr>
                            <td class="content-block"
                                style="vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
                                
                                
                                <br><span style="display: block;">© 2021 CATASTRO</span>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    </td>
                </tr>
                </table>
            </body>
            
            </html>';
        return $message;
	}

    public function mensaje_bloqueado($tiempo){

		$message = '
            <html>
            <head>
                <meta name="viewport" content="width=device-width">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta charset="ISO-8859-1">
                <title>SMTP CATASTRO</title>
                <style>
                @media only screen and (max-width: 620px) {
                    table[class=body] h1 {
                    font-size: 28px !important;
                    margin-bottom: 10px !important;
                    }
            
                    table[class=body] p,
                    table[class=body] ul,
                    table[class=body] ol,
                    table[class=body] td,
                    table[class=body] span,
                    table[class=body] a {
                    font-size: 16px !important;
                    }
            
                    table[class=body] .wrapper,
                    table[class=body] .article {
                    padding: 10px !important;
                    }
            
                    table[class=body] .content {
                    padding: 0 !important;
                    }
            
                    table[class=body] .container {
                    padding: 0 !important;
                    width: 100% !important;
                    }
            
                    table[class=body] .main {
                    border-left-width: 0 !important;
                    border-radius: 0 !important;
                    border-right-width: 0 !important;
                    }
            
                    table[class=body] .btn table {
                    width: 100% !important;
                    }
            
                    table[class=body] .btn a {
                    width: 100% !important;
                    }
            
                    table[class=body] .img-responsive {
                    height: auto !important;
                    max-width: 100% !important;
                    width: auto !important;
                    }
                }
                @media all {
                    .ExternalClass {
                    width: 100%;
                    }
            
                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                    line-height: 100%;
                    }
            
                    .apple-link a {
                    color: inherit !important;
                    font-family: inherit !important;
                    font-size: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                    }
            
                    #MessageViewBody a {
                    color: inherit;
                    text-decoration: none;
                    font-size: inherit;
                    font-family: inherit;
                    font-weight: inherit;
                    line-height: inherit;
                    }
            
                    .btn-primary table td:hover {
                    background-color: #34495e !important;
                    }
            
                    .btn-primary a:hover {
                    background-color: #34495e !important;
                    border-color: #34495e !important;
                    }
                }
                </style>
            </head>';
            
            $message.= '<body
                style="background-color: #f6f6f6; font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <span class="preheader"
                style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;"></span>
                <table border="0" cellpadding="0" cellspacing="0" class="body"
                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
                <tr>
                    <td style="font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
                    <td class="container"
                    style="font-family: '. chr(39).'Segoe UI'.chr(39) .', sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
                    <div style="padding: 13px 0;text-align: center;">
                        <img src="cid:logo_ciis" width="140" heigth="42">
                    </div>
                    <div class="content"
                        style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 550px; padding: 10px;text-align: center;">
                        <table class="main"
                        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">
                        <tr>
                            <td class="wrapper"
                            style="font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 50px 40px;color: #363b3e;">
                            <table class="main"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">
                            <tr>
                                <td class="wrapper"
                                style="font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 50px 40px;color: #363b3e;">
                                <table border="0" cellpadding="0" cellspacing="0"
                                    style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                    <tr>
                                    <td style=" font-size: 14px; vertical-align: top;">
                                        <p
                                        style="font-size: 30px; font-weight: bold; margin: 0; Margin-bottom: 25px;">
                                        Notificación de seguridad de CATASTRO intento de inicio de sesión bloqueado</p>
    
                                        <p style="font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"> Alguien itentó ingresar con una contraseña errónea mas de 2 veces para intentar iniciar sesión en su cuenta. CATASTRO bloqueó esta cuenta temporalmente por '.$tiempo.' minutos.</p>
                                    </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        </table>
                        <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                            <tr>
                            <td class="content-block"
                                style="vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
                                
                                <span class="apple-link"
                                style="color: #999999; font-size: 12px; text-align: center;display: block;margin-top: 7px;">
                                Enviado por el Gobierno Regional de Tacna - Gerencia Regional de Desarrollo e Inclusión Social
                                <a href="#"
                                    style="text-decoration: underline; color: #999999; font-size: 12px; text-align: center;">Visítanos</a></span>
                                <br><span style="display: block;">© 2021 CATASTRO</span>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    </td>
                </tr>
                </table>
            </body>
            
            </html>';
        return $message;
	}

    public function mensaje_bienvenida($usuario, $contrasenia, $codigo, $correo, $url){
        $message = '<html>
            <head>
                <meta name="viewport" content="width=device-width">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta charset="ISO-8859-1">
                <title>MENSAJERÍA CATASTRO</title>
                <style>
                @media only screen and (max-width: 620px) {
                    table[class=body] h1 {
                    font-size: 28px !important;
                    margin-bottom: 10px !important;
                    }
            
                    table[class=body] p,
                    table[class=body] ul,
                    table[class=body] ol,
                    table[class=body] td,
                    table[class=body] span,
                    table[class=body] a {
                    font-size: 16px !important;
                    }
            
                    table[class=body] .wrapper,
                    table[class=body] .article {
                    padding: 10px !important;
                    }
            
                    table[class=body] .content {
                    padding: 0 !important;
                    }
            
                    table[class=body] .container {
                    padding: 0 !important;
                    width: 100% !important;
                    }
            
                    table[class=body] .main {
                    border-left-width: 0 !important;
                    border-radius: 0 !important;
                    border-right-width: 0 !important;
                    }
            
                    table[class=body] .btn table {
                    width: 100% !important;
                    }
            
                    table[class=body] .btn a {
                    width: 100% !important;
                    }
            
                    table[class=body] .img-responsive {
                    height: auto !important;
                    max-width: 100% !important;
                    width: auto !important;
                    }
                }
                @media all {
                    .ExternalClass {
                    width: 100%;
                    }
            
                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                    line-height: 100%;
                    }
            
                    .apple-link a {
                    color: inherit !important;
                    font-family: inherit !important;
                    font-size: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                    }
            
                    #MessageViewBody a {
                    color: inherit;
                    text-decoration: none;
                    font-size: inherit;
                    font-family: inherit;
                    font-weight: inherit;
                    line-height: inherit;
                    }
            
                    .btn-primary table td:hover {
                    background-color: #34495e !important;
                    }
            
                    .btn-primary a:hover {
                    background-color: #34495e !important;
                    border-color: #34495e !important;
                    }
                }
                </style>
            </head>
            <body
                style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <span class="preheader"
                style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Código de restablecimiento</span>
                <table border="0" cellpadding="0" cellspacing="0" class="body"
                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
                <tr>
                    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
                    <td class="container"
                    style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
                    <div style="padding: 13px 0;text-align: center;">
                        <img src="cid:logo_ciis" width="140" heigth="42">
                    </div>
                    <div class="content"
                        style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 550px; padding: 10px;text-align: center;">
                        <table class="main"
                        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">
                        <tr>
                            <td class="wrapper"
                            style="font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 50px 40px;color: #363b3e;">
                            <table border="0" cellpadding="0" cellspacing="0"
                                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                <tr>
                                <td style=" font-size: 14px; vertical-align: top;">
                                    <p
                                    style="font-size: 30px; font-weight: bold; margin: 0; Margin-bottom: 25px;">
                                    BIENVENIDO A CATASTRO</p><br>
                                    <p>Se creó exitosamente tu usuario. A continuación aparece los detalles de tu cuenta: </p><br>
                                    <p
                                    style="font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
                                    Usuario: '.$usuario.'</p>
                                    <p
                                    style="font-size: 30px; font-weight: bold; margin: 20px 0;">
                                    </p>
                                    <p
                                    style="font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
                                    Correo electrónico: '.$correo.'</p><br>
                                    <p>Se le pedirá que establezca su contraseña en el primer inicio de sesión, haciendo clic en el siguiente enlace:</p><br>
                                    <a href="'.$url.'/recover-password?email='.$correo.'&codigo='.$codigo.'&user='.$usuario.'" target="_blank"
                                        style="display: inline-block; color: #ffffff; background-color: #3f4ce7; border: solid 1px #3f4ce7; border-radius: 2px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 15px 30px;border-color: #3f4ce7;">Establece tu contraseña</a>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                        <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                            <tr>
                            <td class="content-block"
                                style="vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
                                
                                <span class="apple-link"
                                style="color: #999999; font-size: 12px; text-align: center;display: block;margin-top: 7px;">
                                Enviado por el Gobierno Regional de Tacna - Gerencia Regional de Desarrollo e Inclusión Social
                                <a href="#"
                                    style="text-decoration: underline; color: #999999; font-size: 12px; text-align: center;">Visítanos</a></span>
                                <br><span style="display: block;">© 2021 CATASTRO</span>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    </td>
                </tr>
                </table>
            </body>
        </html>';

        return $message;
    }
}
