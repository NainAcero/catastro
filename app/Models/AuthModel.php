<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{

    public function __construct()
    { 
        $this->db = \Config\Database::connect();
    }

    public function cek_login($usuario){
        $query = $this->db->query("SELECT count(*) as total FROM usuario WHERE usuario = '$usuario'")->getResult('array')[0]["total"];

        if($query >  0){
            $hasil = $this->db->query("SELECT u.idusuario, u.usuario, u.fechaingreso, u.correo, u.celular, u.nombre, u.apellido, u.constrasenia, u.intento, u.activo, u.eliminado, u.idestado, e.denominacion as estado 
                FROM usuario as u INNER JOIN estado as e ON u.idestado = e.idestado 
                WHERE u.usuario = '$usuario' AND u.eliminado = 0 LIMIT 1")->getResult('array')[0];
            
            if($hasil == null) throw new \Exception("El usuario no se encuentra registrado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 2) throw new \Exception("El usuario está inactivo. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 3) throw new \Exception("El usuario se encuentra bloqueado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 4) throw new \Exception("El usuario se encuentra deshabilitado. Comuníquese con el administrador del sistema.");
            if($hasil["idestado"] == 5) throw new \Exception("Contraseña expirada. Comuníquese con el administrador del sistema.");
            
            if($hasil["activo"] == 0) throw new \Exception("El usuario se encuentra desabilitado.");
            if($hasil["eliminado"] == 1) throw new \Exception("El usuario se encuentra eliminado.");

            if($hasil["intento"] == 3) {
                (new UsuariosModel())->cambiar_estado(3, $usuario);
                throw new \Exception("El usuario se encuentra bloqueado. Comuníquese con el administrador del sistema.");
            }
            
        }else $hasil = array(); 
        
        return $hasil;
    }

    public function roles($idusuario){

        $roles = [];
        $query =  $this->db->query("SELECT r.denominacion FROM usuariorol AS ur INNER JOIN rol AS r ON ur.idrol = r.idrol WHERE ur.idusuario = $idusuario AND ur.eliminado = '0' AND r.eliminado = '0'")->getResult('array');
        
        foreach($query as $rol) $roles[] = $rol["denominacion"];
        
        return $roles;
    }

    public function roles_accesos_usuario_array($idusuario){
        $accesos = [];
        $responsedata = [];

        $activo = $this->db->query("SELECT idestado FROM usuario WHERE idusuario = '$idusuario'")->getResult('array')[0];

        $resultados = $this->db->query("SELECT u.idusuario, r.idrol, r.denominacion 
            FROM usuariorol as ur 
            INNER JOIN usuario as u ON ur.idusuario = u.idusuario 
            INNER JOIN rol as r ON r.idrol = ur.idrol 
            WHERE ur.eliminado = 0 and ur.activo = 1 and r.eliminado = 0 and r.activo = 1 AND u.idusuario = '$idusuario'")
        ->getResult('array');

        foreach($resultados as $resultado) {
            $idrol = $resultado["idrol"];

            $resultados2 = $this->db->query("SELECT o.idopcion, m.idmodulo, o.denominacion as opcion, m.denominacion as modulo, m.codigo, o.ruta 
                FROM rolopcion as ro 
                INNER JOIN rol AS r ON ro.idrol = r.idrol 
                INNER JOIN opcion as o ON o.idopcion = ro.idopcion 
                INNER JOIN modulo AS m ON m.idmodulo = o.idmodulo 
                WHERE r.idrol = '$idrol' AND ro.eliminado = 0 AND r.eliminado = 0 AND m.eliminado = 0 AND o.eliminado = 0 AND o.activo = 1 ORDER BY m.orden, o.orden ASC")
            ->getResult('array'); 

            $accesos = array_merge($accesos, $resultados2);
        }

        $responsedata[] = $accesos;
        $responsedata[] = $activo;
        return $responsedata;
    }

    public function roles_accesos_by_idusuario($idusuario){
        $accesos = $this->roles_accesos_usuario_array($idusuario);

        return (new ApiModel())->ClientResponse($accesos, [] ,200, "Listado de accesos de usuario.");
    }

}