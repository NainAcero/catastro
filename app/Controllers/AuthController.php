<?php 

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;

use App\Models\AuthModel;
use App\Models\ErrorModel;
use App\Models\ParametroModel;
use App\Models\UsuariosModel;
use App\Models\EmailModel;
use App\Models\CodigoCorreoModel;
use App\Models\ApiModel;

class AuthController extends BaseController
{
    use ResponseTrait;

    private $TIEMPO_EXPIRACION_JWT;
	private $TIEMPO_EXPIRACION_CORREO_ELECTRONICO;

    public function __construct()
    {
		date_default_timezone_set('America/Lima');
		
        $this->auth = new AuthModel();
		$this->TIEMPO_EXPIRACION_JWT = (new ParametroModel())->obtenerParametro("TIME_EXPIRACION");
		$this->TIEMPO_EXPIRACION_CORREO_ELECTRONICO = (new ParametroModel())->obtenerParametro("CORREO_TIEMPO_VALIDACION");
    }

    private function privateKey()
	{
		$privateKey = (new ParametroModel())->obtenerParametro("CLAVE_PRIVADA", false);
		return $privateKey;
	}

    public function login()
	{
		try {
			$json = $this->request->getJSON();
			
			$usuario 		= $json->usuario;
			$contrasenia 	= $json->constrasenia;
			$ippublica 		= $json->ip;

			if($usuario == null || $contrasenia == null || strlen($usuario) == 0 || strlen($contrasenia) == 0 )
				throw new \Exception("El campo no puede estar vacío.");

			$cek_login = $this->auth->cek_login($usuario);

			if(password_verify($contrasenia, $cek_login['constrasenia']))
			{
				(new ErrorModel())->insert_loguser("Inicio de sesión", "Login de usuario", $ippublica, $usuario);

				$secret_key = $this->privateKey();
				$issuer_claim = "THE_CLAIM";
				$audience_claim = "THE_AUDIENCE";
				$issuedat_claim = time();
				$notbefore_claim = $issuedat_claim + 10;
				$expire_claim = $issuedat_claim + $this->TIEMPO_EXPIRACION_JWT; // TIEMPO EN SEGUDNOS
				$token = array(
					"iss" => $issuer_claim,
					"aud" => $audience_claim,
					"iat" => $issuedat_claim,
					"nbf" => $notbefore_claim,
					"exp" => $expire_claim,
					"data" => array(
						"idusuario" => $cek_login['idusuario'],
						"usuario"  	=> $cek_login['usuario'],
						"correo"   	=> $cek_login['correo']
					)
				);

				$token = JWT::encode($token, $secret_key);
				
				$output = [
					'status' 	=> 200,
					"token" 	=> $token,
					"usuario" 	=> array(
						"idusuario"     => $cek_login['idusuario'],
						"usuario"  	    => $cek_login['usuario'],
						"correo"   	    => $cek_login['correo'],
						"apellido"      => $cek_login['apellido'],
						"fechaingreso"  => $cek_login['fechaingreso'],
						"celular"       => $cek_login['celular'],
						"nombre"   	    => $cek_login['nombre'],
						"roles" 	    => (new AuthModel())->roles($cek_login['idusuario']),
					),
					"modulos"	=> (new AuthModel())->roles_accesos_usuario_array($cek_login['idusuario']),
					"expireAt" 	=> $expire_claim
				];

				(new UsuariosModel())->cambiar_intento(0, $usuario);
				(new UsuariosModel())->cambiar_estado_new(1, $usuario);

				$response = (new ApiModel())->ClientResponse([$output], [] ,200, "Inicio de sesión de usuario.");
				return $this->respond($response, 200);
			} else {
				$intentos = (new UsuariosModel())->incrementar_bloquear($usuario);

				if($intentos == 0) {
					$_petitions = [];
					$tiempo = (new ParametroModel())->obtenerParametro("TIME_BLOQUEO", true);

					$_petitions[] = (object)[
						"name"  => "_correo",
						"value" =>  $cek_login['correo'],
						"type"  => "string"
					];

					$_petitions[] = (object)[
						"name"  => "_subject",
						"value" =>  "INTENTO DE ACCESO: CATASTRO",
						"type"  => "string"
					];
			
					$_petitions[] = (object)[
						"name"  => "_body",
						"value" =>  $this->mensaje_bloqueado($tiempo),
						"type"  => "string"
					];
			
					$_petitions[] = (object)[
						"name"  => "_altBody",
						"value" =>  "",
						"type"  => "string"
					];
					
					(new ErrorModel())->insert_loguser("Bloqueo de usuario", "Intento de acceso por 3 veces", $ippublica, $usuario);
					(new UsuariosModel())->cambiar_estado(3, $usuario);
					(new EmailModel())->ApiEmail($_petitions);
				}
				
				if($intentos == 0) {
					throw new \Exception("El usuario se encuentra bloqueado. Revise su buzón de correo electrónico.");
				}else {
					throw new \Exception("El nombre de usuario y/o contraseña es incorrecta. Número de intentos disponibles $intentos");
				}
			}
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Auth", "El nombre de usuario y/o contraseña es incorrecta.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "El nombre de usuario y/o contraseña es incorrecta.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }
	}

    public function logout() {
        try {
			$json = $this->request->getJSON();

			$usuario 		= $json->usuario;
			$ippublica 		= $json->ip;

            (new ErrorModel())->insert_loguser("Cerrar sesión", "usuario cerro sesión", $ippublica, $usuario);
			$response = (new ApiModel())->ClientResponse([], [] ,200, "Cerrar sesión.");
			return $this->respond($response, 200);
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Cerrar sesión", "Fallo cerrar sesión.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Cerrar sesión", "Fallo cerrar sesión.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }
    }

	public function roles_accesos(){
		try {
			$idusuario 		= $this->username = $this->verifyTokenAuthorization()->data->idusuario;

			return $this->respond($this->auth->roles_accesos_by_idusuario($idusuario), 200);
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Roles de Usuario", "Roles de Usuario.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "Roles de Usuario.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }
	}

	public function correo(){
		try {
			$json = $this->request->getJSON();

			$_petitions = (array) $json->_petitions;
			$_codigo = $this->genera_codigo(6);
			$fecha_actual = date("Y-m-d G:i:s");

			$url = (new ParametroModel())->obtenerParametro("RUTA_CLIENTE", false);

			$_correo   = "";
			$_password = "";
			$_usuario = "";

			foreach($_petitions as $item){
				switch ($item->name) {
					case "_correo":
						$_correo = $item->value;
						break;
					case "_usuario":
						$_usuario = $item->value;
						break;
					case "_password":
						$_password = $item->value;
						break;
				}
			}

			$_data = [
				"correo" => $_correo,
				"codigo" => $_codigo,
				"fechaingreso" => date("Y-m-d G:i:s", strtotime($fecha_actual."+ 1 days"))
			];

			$_petitions[] = (object)[
				"name"  => "_subject",
				"value" =>  "MENSAJE DE BIENVENIDA: CATASTRO",
				"type"  => "string"
			];
	
			$_petitions[] = (object)[
				"name"  => "_body",
				"value" =>  $this->mensaje_bienvenida($_usuario, $_password, $_codigo, $_correo, $url),
				"type"  => "string"
			];
	
			$_petitions[] = (object)[
				"name"  => "_altBody",
				"value" =>  "",
				"type"  => "string"
			];

			(new CodigoCorreoModel())->insertar_correo($_data);
			return $this->respond((new EmailModel())->ApiEmail($_petitions), 200);
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Auth", "Envio de correo.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "Envio de correo.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }
	}

	public function verifyTokenAuthorization(){
		
		if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
			(new ErrorModel())->insert_error("Token usuario", "Token Authorization.", "Token not found in request HTTP/1.0 400 Bad Request","");
            header('HTTP/1.0 400 Bad Request');
            echo 'Token not found in request';
            exit;
        }
		
        $jwt = $matches[1];
        if (! $jwt) {
			(new ErrorModel())->insert_error("Token usuario", "Token Authorization.", "Error en el token HTTP/1.0 400 Bad Request","");
            header('HTTP/1.0 400 Bad Request');
            exit;
        }

        if(!$this->validateToken($jwt)){
			(new ErrorModel())->insert_error("Token usuario", "Token Authorization.", "Token no válido HTTP/1.0 403 Forbidden","");
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        return $this->validateToken($jwt);
	}

	public function validateToken($token){
		try {
			$secret_key = $this->privateKey();
			return JWT::decode($token, $secret_key, array('HS256'));
		} catch(\Exception $e){
			(new ErrorModel())->insert_error("Auth", "validate Token.", $e->getMessage(),"");
			return false;
		}
	}

	public function restablecer_contrasenia(){
		try {
			$json = $this->request->getJSON();

			$url = (new ParametroModel())->obtenerParametro("RUTA_CLIENTE", false);
			
			$_petitions = (array) $json->_petitions;
			$_codigo = $this->genera_codigo(6);

			$_time_expired = $_petitions[1]->value;
			$fecha_actual = date("Y-m-d G:i:s");

			if($_petitions[0]->name == "_correo") {

				if($_time_expired == "1") {
					$_data = [
						"correo" => $_petitions[0]->value,
						"codigo" => $_codigo,
						"fechaingreso" => date("Y-m-d G:i:s",strtotime($fecha_actual."+ 1 days"))
					];
				}else {
					$_data = [
						"correo" => $_petitions[0]->value,
						"codigo" => $_codigo,
						"fechaingreso" => $fecha_actual
					];
				}

				(new UsuariosModel())->validar_email($_data["correo"]);

				$_petitions[] = (object)[
					"name"  => "_subject",
					"value" =>  "RESTABLECER CONTRASEÑA: CATASTRO",
					"type"  => "string"
				];
		
				$_petitions[] = (object)[
					"name"  => "_body",
					"value" =>  $this->mensaje_correo($_codigo, $this->_time, $_time_expired, $url),
					"type"  => "string"
				];
		
				$_petitions[] = (object)[
					"name"  => "_altBody",
					"value" =>  "",
					"type"  => "string"
				];
				
				(new CodigoCorreoModel())->insertar_correo($_data);
				return $this->respond((new EmailModel())->ApiEmail($_petitions), 200);

			} else throw new \Exception("No se envió el correo electrónico.");
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Auth", "Restablecer contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "Restablecer contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }
	}

	public function cambiar_contra(){
		try {
			$usuario = $this->verifyTokenAuthorization()->data->usuario;
			$correo = $this->verifyTokenAuthorization()->data->correo;
			$json = $this->request->getJSON();
			
			$contra_new 	= $json->contra_new;
			$contra_old 	= $json->contra_old;

			if($usuario == null || $contra_old == null || strlen($usuario) == 0 || strlen($contra_old) == 0 )
				throw new \Exception("El campo no puede estar vacío.");

			$cek_login = $this->auth->cek_login($usuario);

			if(password_verify($contra_old, $cek_login['constrasenia'])){
				$password_hash = password_hash($contra_new, PASSWORD_BCRYPT);

				$query = (new UsuariosModel())->cambiar_contrasenia($password_hash, $correo);
				return $this->respond((new ApiModel())->ClientResponse(["_status" => $query], [] ,200, "Registros actualizados."), 200);
			}

			throw new \Exception("contraseña inválida.");
		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Auth", "Cambio de contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "Cambio de contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,401, "");
			return $this->respond($response, 401);
        }

	}

	public function recover_password(){
		try {
			$json = $this->request->getJSON();
			
			$_petitions = (array) $json->_petitions;

			$_correo   = "";
			$_codigo   = "";
			$_password = "";

			foreach($_petitions as $item){
				switch ($item->name) {
					case "_correo":
						$_correo = $item->value;
						break;
					case "_codigo":
						$_codigo = $item->value;
						break;
					case "_password":
						$_password = $item->value;
						break;
				}
			}

			$_data = [
				"_correo" 	=> $_correo,
				"_codigo" 	=> $_codigo,
				"_password" => $_password
			];

			(new CodigoCorreoModel())->validar_recover_password($_data, $this->_time);

			$password_hash = password_hash($_password, PASSWORD_BCRYPT);

			$query = (new UsuariosModel())->cambiar_contrasenia($password_hash, $_correo);
			return $this->respond((new ApiModel())->ClientResponse(["_status" => $query], [] ,200, "Registros actualizados."), 200);

		} catch (\Error $e) {
			$output = [ 'mensaje' => $e->getMessage() ];
			
			(new ErrorModel())->insert_error("Auth", "Restablecer contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,500, "");
			return $this->respond($response, 500);
        } catch (\Exception $e) {
			$output = [ 'mensaje' => $e->getMessage() ];

			(new ErrorModel())->insert_error("Auth", "Restablecer contraseña.", $e->getMessage(), $usuario?? "");
			$response = (new ApiModel())->ClientResponse([], [$output] ,500, "");
			return $this->respond($response, 500);
        }
	}
}