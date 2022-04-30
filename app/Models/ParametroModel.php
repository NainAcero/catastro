<?php

namespace App\Models;

use CodeIgniter\Model;

class ParametroModel extends Model
{    
    public function __construct()
    { 
        $this->db = \Config\Database::connect();
    }
    
    public function obtenerParametro($nombreParametro, $isEntero = true){
        $hasil = $this->db->query("SELECT * FROM parametro WHERE denominacion = '$nombreParametro' AND eliminado = 0 LIMIT 1")->getResult('array')[0]; 
        
        if($hasil == null) return 0;
        return  ($isEntero)? intval($hasil["valor"]) : $hasil["valor"];
    }
}