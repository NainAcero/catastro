<?php

namespace App\Models;

use CodeIgniter\Model;


class CodigoCorreoModel extends Model
{
    public function validar_recover_password($_data, $_minutos){
		date_default_timezone_set('America/Lima');

        $_correo =  $_data["_correo"];
        $_codigo =  $_data["_codigo"];
        $_time   =  '-5 minutes';
        $_date   =  date('Y-m-d G:i:s', strtotime($_time, strtotime(date("Y-m-d G:i:s"))));

        $hasil = $this->db->query("SELECT * FROM codigocorreo WHERE correo = '$_correo' AND codigo = '$_codigo' AND eliminado = 0 AND activo = 1 AND fechaingreso >= '$_date'")->getResult('array');

        if($hasil == null) throw new \Exception("El código es inválido o ya expiró, solicite un nuevo código.");

        $this->db->query("UPDATE codigocorreo SET activo = 0 WHERE correo = '$_correo' AND codigo = $_codigo;");
        return $hasil;
    }

    public function insertar_correo($data) {
        $correo = $data["correo"];
        $codigo = $data["codigo"];
        $fechaingreso = $data["fechaingreso"];

        $result = $this->db->query("INSERT INTO codigocorreo(correo, codigo, fechaingreso) VALUES('$correo', '$codigo', '$fechaingreso')");
        return $result;
    }
}