<?php 
    namespace App\Models;

	use CodeIgniter\Model;

	class UsuariosModel extends Model
	{
        public function __construct()
        { 
            $this->db = \Config\Database::connect();
        }
        
        public function validar_email($email){

            $hasil = $this->db->query("SELECT * FROM usuario WHERE correo = '$email' LIMIT 1")->getResult('array')[0];
            
            if($hasil == null) throw new \Exception("El correo electrónico no esta disponible.");

            if($hasil["idestado"] == 2) throw new \Exception("El usuario está inactivo. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 3) throw new \Exception("El usuario se encuentra bloqueado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 4) throw new \Exception("El usuario se encuentra deshabilitado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 5) throw new \Exception("Contraseña expirada. Comuníquese con el administrador del sistema.");

            return $hasil;
        }

        public function cambiar_contrasenia($constrasenia, $correo){
            $hasil = $this->db->query("SELECT * FROM usuario WHERE correo = '$correo' LIMIT 1")->getResult('array')[0];

            if($hasil["idestado"] == 2) throw new \Exception("El usuario está inactivo. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 3) throw new \Exception("El usuario se encuentra bloqueado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 4) throw new \Exception("El usuario se encuentra deshabilitado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 5) throw new \Exception("Contraseña expirada. Comuníquese con el administrador del sistema.");
            
            return $this->db->query("UPDATE usuario SET constrasenia = '$constrasenia'  WHERE correo = '$correo';");
        }

        public function incrementar_bloquear($username){
            $usuario = $this->table($this->table)
                            ->select('intento')
                            ->where('usuario', $username)
                            ->limit(1)
                            ->get()
                            ->getRowArray();

            $numeros = 3;
            $intento = intval($usuario["intento"]) + 1;

            if($intento < 3) $this->db->query("UPDATE usuario SET intento = '$intento', usuarioactualizo = '$username' WHERE usuario = '$username';");
            return $numeros - $intento;
        }

        public function cambiar_intento($intento, $username){
            $result = $this->db->query("UPDATE usuario SET intento = '$intento', usuarioactualizo = '$username' WHERE usuario = '$username';");
            return $result;
        }

        public function cambiar_estado($estado, $username){
            date_default_timezone_set('America/Lima');
            $Object = date("Y-m-d G:i:s");  

            $result = $this->db->query("UPDATE usuario SET idestado = '$estado', fechabloqueado =  '$Object' , usuarioactualizo = '$username' WHERE usuario = '$username';");
            return $result;
        }

        public function desbloquear($username) {
            $result = $this->db->query("UPDATE usuario SET idestado = 1,intento = 1, fechabloqueado =  NULL, usuarioactualizo = '$username' WHERE usuario = '$username';");
            return $result;
        }

        public function cambiar_estado_new($estado, $username){ 
            $result = $this->db->query("UPDATE usuario SET idestado = '$estado' , usuarioactualizo = '$username' WHERE usuario = '$username';");
            return $result;
        }
        
        public function DeleteRolesByUsuario($json, $username){
            try {
                $_petitions = (array) $json->_petitions;
                $_idrol = $_petitions[0]->value;
                $actualziados = 0;
                
                $this->db->query("UPDATE rol SET eliminado = 1, usuarioactualizar = '$username' WHERE idrol = $_idrol;");
                $usuarios = $this->db->query("SELECT idusuario FROM usuario WHERE idrol = $_idrol AND eliminado = 0")->getResult('array');

                foreach($usuarios as $usuario) {
                    $idusuario = $usuario["idusuario"];
                    $result = $this->db->query("UPDATE usuario SET activo = 0, usuarioactualizar = '$username' WHERE idusuario = $idusuario;");
                    $result2 = $this->db->query("UPDATE accesosistemausuario SET eliminado = 0, usuarioactualizar = '$username' WHERE idusuario = $idusuario;");
                    
                    if(!$result || !$result2) throw new \Exception($this->db->error()["message"]);
                    else $actualziados += 1;
                }

                return (new ApiModel())->ClientResponse(["_status" =>  $actualziados], [] ,200, "Registros actualizados.");

            } catch (\Error $e) {
                throw new \Exception($e->getMessage());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        public function ApiValidarRoles($json){
            try {
                $_petitions = (array) $json->_petitions;
                $_idrol = $_petitions[0]->value;

                $campo = $_petitions[1]->name;
                $valor = $_petitions[1]->value;
                $actualizar = $_petitions[2]->value;

                $result2 = $this->db->query("SELECT count(idusuario) as total , idusuario FROM usuariorol WHERE eliminado = 0 AND idrol = $_idrol GROUP BY idusuario")->getResult('array');

                foreach($result2 as $info) {
                    if($info["total"] == 1 ){
                        $idusuario = $info["idusuario"];

                       $nrorolesxusuario = $this->db->query("SELECT count(*) as nroroles FROM usuariorol WHERE eliminado = 0 AND idusuario = $idusuario LIMIT 1")->getResult('array')[0];
                       
                       if($nrorolesxusuario["nroroles"] == 1){
                            $result3 = $this->db->query("UPDATE usuario SET idestado = $actualizar WHERE eliminado = 0 AND idusuario=$idusuario");
                        }
                   }
                }

                $result = $this->db->query("UPDATE usuariorol SET $campo=$valor WHERE idrol=$_idrol");
                return (new ApiModel())->ClientResponse(["_status" =>  $result], [] , 200 , "Registros actualizados.");

            } catch (\Error $e) {
                throw new \Exception($e->getMessage());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }