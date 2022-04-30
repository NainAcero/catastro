<?php

namespace App\Models;

use CodeIgniter\Model;


class CodigoCorreoModel extends Model
{
    public function validar_recover_password($_data, $_minutos){
        $_correo =  $_data["_correo"];
        $_codigo =  $_data["_codigo"];
        $_time   =  '-5 minutes';
        $_date   =  date('Y-m-d H:i:s', strtotime($_time, strtotime(date("Y-m-d H:i:s"))));

        $hash = $this->db->query("SELECT *  FROM codigocorreo WHERE correo = $_correo AND codigo = $_codigo AND eliminado = 0 AND activo = 1 AND fechaingreso >= $_date")->getResult('array');

        if($hasil == null) throw new \Exception("El código es inválido o ya expiró, solicite un nuevo código.");

        $this->db->query("UPDATE codigocorreo SET activo = 0 WHERE correo = '$_correo' AND codigo = $_codigo;");
        return $hasil;
    }
}