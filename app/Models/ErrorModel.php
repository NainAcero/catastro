<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrorModel extends Model
{    
    public function __construct()
    { 
        $this->db = \Config\Database::connect();
    }
    
    public function insert_error($modulo, $accion, $error, $usuario){
	
        $this->db->query("INSERT INTO errorlog(modulo, accion, error, usuarioingreso, usuarioactualizar) 
                            VALUES('$modulo', '$accion', '$error', '$usuario', '$usuario')");
    }

    public function insert_loguser($modulo, $submodulo, $ippublica, $usuario){

        $this->db->query("INSERT INTO errorlog(modulo, submodulo, ippublica, usuarioingreso, usuarioactualizar) 
                            VALUES('$modulo', '$submodulo', '$ippublica', '$usuario', '$usuario')");
    }
}