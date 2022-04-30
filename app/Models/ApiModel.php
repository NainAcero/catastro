<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiModel extends Model
{
    public function __construct()
    { 
        $this->db = \Config\Database::connect();
    }
    
    public function Apivalidate($json) {
        $_petitions = (array) $json->_petitions;
        if(count($_petitions) > 0){
            foreach($_petitions as $item){
                $types = explode("|", $item->type);

                if(count($types) > 1){
                    if(strcmp($types[1], "unique") == 0){
                        // $result = (new Model())->uniqueregistro_sivi_data($json->_table, $item->name, $item->value, $types[0]);
                        $value = $item->value;

                        if($types[0] == "string") $value = "'$item->value'";

                        $query = $this->db->query("SELECT * FROM $json->_table WHERE $item->name = $value AND eliminado = 0");
                        $response = $query->getResult('array');
                        
                        $total = count($response);
                        if($total > 0) throw new \Exception("$item->name ya se encuentra registrado");
                    }
                }
            }
        } else throw new \Exception("Incluir parametros en la petición.");
    }

    public function ApiUpdaterrayByRows($json, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_update_rows = "";
            
            $campos = explode("|", $_petitions[0]->name);
            $values = explode("|", $_petitions[0]->value);

            $nombre = $_petitions[1]->name;
            $_data = explode("|", $_petitions[1]->value);

            for($i = 0; $i < count($_data); $i++) {
                $_filtros = "";
                $values_filter = explode(",", $values[$i]);

                $con = 0;
                foreach($values_filter as $filter){
                    if($con > 0) $_filtros .= " AND ";
                    $_filtros .= $campos[$con] . "=" . $filter;
                    $con ++;
                }
                
                $_update_rows = $nombre . '='. "'" . $_data[$i] . "'" . ',';

                $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";
                
                $query = $this->db->query("UPDATE $json->_table SET $_update_rows WHERE $_filtros;"); 
            }

            if(!$query) throw new \Exception($this->db->error()["message"]);
            return $this->ClientResponse(["status" => $query], [] ,200, "Registros actualizados.");
          
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    public function ApiCreate($json, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_create_rows = "";
            $_values = "";

            foreach($_petitions as $item){
                $_create_rows = $_create_rows . $item->name . ',';
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_values = $_values . "'" . $item->value . "'" . ',';
                }else if($types[0] == "password"){
                    $password_hash = password_hash($item->value, PASSWORD_BCRYPT);
                    $_values = $_values . "'" . $password_hash . "'" . ',';
                }else {
                    $_values = $_values = $_values . $item->value . ',';
                }
            }

            $_create_rows = $_create_rows . "usuarioingreso,usuarioactualizo";
            $_values = $_values . "'$username','$username'";

            $query = $this->db->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
            if($this->db->insertID() == 0) throw new \Exception($this->db->error()["message"]);

            return $this->ClientResponse([["id" => $this->db->insertID(), "status" => $query]], [] ,200, "Registros creados.");
          
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiCreateSinId($json, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_create_rows = "";
            $_values = "";

            foreach($_petitions as $item){
                $_create_rows = $_create_rows . $item->name . ',';
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_values = $_values . "'" . $item->value . "'" . ',';
                }else if($types[0] == "password"){
                    $password_hash = password_hash($item->value, PASSWORD_BCRYPT);
                    $_values = $_values . "'" . $password_hash . "'" . ',';
                }else {
                    $_values = $_values = $_values . $item->value . ',';
                }
            }

            $_create_rows = $_create_rows . "usuarioingreso,usuarioactualizo";
            $_values = $_values . "'$username','$username'";

            $query = $this->db->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 

            return $this->ClientResponse([["id" => 0, "status" => true]], [] ,200, "Registros creados.");
          
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiUpdate($json, $id, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_update_rows = "";

            foreach($_petitions as $item){
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_update_rows = $_update_rows . $item->name . '='. "'" . $item->value . "'" . ',' ;
                } else if($types[0] == "query"){
                    $datos = explode("'", $item->value);
                    $result = "";
                    foreach($datos as $data) $result .= "'" . $data . "'";
                    $_update_rows = $_update_rows . $item->name . '='. strval($result) . ',' ;
                } else {
                    $_update_rows = $_update_rows . $item->name . '='. $item->value . ',';
                }
            }

            $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";

            if($json->_table == "ads_actividadeconomica"){

                $query = $this->db->query("UPDATE $json->_table SET $_update_rows WHERE id$json->_table = " . "'" . $id . "';"); 
            } else {
                $query = $this->db->query("UPDATE $json->_table SET $_update_rows WHERE id$json->_table = $id;"); 
            }
            
            if(!$query) throw new \Exception($this->db->error()["message"]);
            return $this->ClientResponse(["status" => $query], [] ,200, "Registros actualizados.");
            
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function Apiquery($json){
        try {
            $_petitions = (array) $json->_petitions;
            $_query = "";
            $_page = -1;
            $_limit = 10;

            $buscar_AND = [];
            $filtros_AND = [];
            $tipo_AND = [];

            $buscar_OR = [];
            $filtros_OR = [];

            $offset = 0;
            $adicional = "";
            $order = "";

            if(count($_petitions) > 0){

                foreach($_petitions as $item){
                    if($item->name == "_script"){
                        $_query = $this->db->query("SELECT * FROM query WHERE denominacion = '$item->value' LIMIT 1")->getResult('array')[0]["descripcion"]; 
                    }else {
                        
                        if($item->type != "string"){
                            $_query = str_replace($item->name, $item->value, $_query);
                        }else {
                            $_query = str_replace($item->name, "'$item->value'", $_query);
                        }

                        if($item->name == "@limit") $_limit = $item->value;

                        if($item->name == "@page"){
                            if($item->value < 1)  throw new \Exception("Paginador no encontrado.");
                            $item->value = $item->value - 1;
                            $_page = $item->value;
                            $offset = $_page * $_limit;
                        }

                        if($item->name == "@filtros_AND") $filtros_AND = explode("|", $item->value);
                        if($item->name == "@buscar_AND") $buscar_AND = explode("|", $item->value);
                        if($item->name == "@buscar_AND") $tipo_AND = explode("|", $item->type);

                        if($item->name == "@filtros_OR") $filtros_OR = explode("|", $item->value);
                        if($item->name == "@buscar_OR") $buscar_OR = explode("|", $item->value);
                        if($item->name == "@ORDER_BY") $order = $item->value;
                    }
                }
                
                if($_page != -1) {
                    $total = 0;

                    if($filtros_AND != null && $buscar_AND != null) {
                        if(count($buscar_AND) != count($filtros_AND)) throw new \Exception("No coinciden los parametros.");

                        for($i = 0; $i < count($buscar_AND); $i++) {
                            if($tipo_AND[$i] == "notlike") {
                                $adicional .= " AND $filtros_AND[$i] = '$buscar_AND[$i]'";
                            }else {
                                $adicional .= " AND $filtros_AND[$i] LIKE '$buscar_AND[$i]'";
                            }
                        }

                        $_query .= $adicional;
                    }
                    
                    if($filtros_OR != null && $buscar_OR != null){
                        if(count($buscar_OR) != count($filtros_OR)) throw new \Exception("No coinciden los parametros.");

                        $adicional = "";

                        for($i = 0; $i < count($buscar_OR); $i++) {
                            $adicional .= ($i == 0)
                                ? " $filtros_OR[$i] LIKE '$buscar_OR[$i]'"
                                : " OR $filtros_OR[$i] LIKE '$buscar_OR[$i]'";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";

                        $total = count($this->db->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " DESC";

                        $_query .= " LIMIT $_limit OFFSET $offset";

                    }else {
                        $total = count($this->db->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " DESC";
                        $_query .= " LIMIT $_limit OFFSET $offset";
                    }
                    
                    $resultado = $this->db->query($_query)->getResult('array');
                    $result = [
                        "totalDocs" => $total,
                        "docs" => $resultado,
                        "limit" => $_limit,
                        "totalPages" => intval( ceil($total / $_limit )),
                        "page" => $_page + 1,
                    ];

                    return $this->ClientResponse($result, [] ,200, "Listado de query.");
                }else {
                    if($filtros_AND != null && $buscar_AND != null) {
                        if(count($buscar_AND) != count($filtros_AND)) throw new \Exception("No coinciden los parametros.");

                        for($i = 0; $i < count($buscar_AND); $i++) {
                            if($tipo_AND[$i] == "notlike") {
                                $adicional .= " AND $filtros_AND[$i] = '$buscar_AND[$i]'";
                            }else {
                                $adicional .= " AND $filtros_AND[$i] LIKE '$buscar_AND[$i]'";
                            }
                        }

                        $_query .= $adicional;
                    }
                    
                    if($filtros_OR != null && $buscar_OR != null){
                        if(count($buscar_OR) != count($filtros_OR)) throw new \Exception("No coinciden los parametros.");

                        $adicional = "";

                        for($i = 0; $i < count($buscar_OR); $i++) {
                            $adicional .= ($i == 0)
                                ? " $filtros_OR[$i] LIKE '$buscar_OR[$i]'"
                                : " OR $filtros_OR[$i] LIKE '$buscar_OR[$i]'";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";
                    }       
                
                    if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " DESC";
                    
                    $resultado = $this->db->query($_query)->getResult('array');

                    return ($json->_type == 1 ) 
                        ? $this->ClientResponse($resultado, [] ,200, "Listado de query.")
                        : $this->ClientResponse(["_status" => $resultado], [] ,200, "Registros enviados.");
                }

            }else {
                throw new \Exception("Incluir parametros en la petición.");
            }
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiCreateArray($json, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_create_rows = $_petitions[0]->value;
            $_create_rows = $_create_rows . ",usuarioingreso,usuarioactualizo";
            $_data = explode(";" , $_petitions[1]->value);

            for($i = 0; $i < count($_data); $i++){
                $_datos = explode(",", $_data[$i]);
                $_values = "";

                for($j = 0; $j < count($_datos); $j++){
                    $_values = $_values . "'" . $_datos[$j] . "'" . ',';
                }

                $_values = $_values . "'$username','$username'";

                $query = $this->db->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
                if($this->db->insertID() == 0) throw new \Exception($this->db->error()["message"]);
            }
            return $this->ClientResponse([["_id" => $this->db->insertID(), "_status" => $query]], [] ,200, "Registros creados.");

        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiReporte($json){
        $_petitions = (array) $json->_petitions;
        $columns = explode("|", $_petitions[1]->value);
        $align   = explode("|", $_petitions[1]->align);
        $filtros = explode("|", $_petitions[2]->value);
        
        $response = $this->Apiquery($json);
        $data = $response["data"];

        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=ventas.xls");
        header("Pragma: $json->_table.xls");
        header("Expires: 0");

    ?>

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php echo $json->_table ?></title>
            </head>
            <body>
                <table width="<?php echo ( count($columns) * 100 ) ?>" border="1">
                    <tr>
                        <?php foreach($columns as $column): ?>
                            <td style="background-color:#227093; text-align:center; color:#FFF;"><strong><?php echo "&nbsp;&nbsp;&nbsp;$column&nbsp;&nbsp;&nbsp;" ?></strong></td>
                        <?php endforeach ?>
                    </tr>
                    <?php for($i = 0; $i < count($data); $i ++): 
                        $info = $data[$i]; 
                    ?>
                        <tr>
                            <?php for($j = 0; $j < count($filtros); $j ++):
                                $filtro = $filtros[$j]; 
                                
                                if($align[$j] == "left") $class = "text-align:left; vertical-align: middle;";
                                else if($align[$j] == "center") $class = "text-align:center; vertical-align: middle;";
                                else if($align[$j] == "right") $class = "text-align:right; vertical-align: middle;";
                            ?>
                                <td><strong><?php echo "" . $info[$filtro] ?></strong></td>
                            <?php endfor ?>
                        </tr>  
                    <?php endfor ?>
                </table>
            </body>
        </html>
    <?php

    }

    /**
     * BACKUP
     */
    public function ApiBackupData($json){
        try {
            exec('pg_dump --dbname=postgresql://oliva:lospalos*2021@127.0.0.1:5432/syca_data > syca_data.sql',$output);
            print_r($output);

        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Formato Json para api rest
     * @return _status retorna el estado de la petición -> SELECT , CREATE, UPDATE
     * @return _messages objeto que retorna un mensaje satisfactorio en caso de que todo salga bien -> SELECT , CREATE, UPDATE
     * @return _errores arreglo de errores que retorna en casoa de que encuentre algún error en alguna petición -> SELECT , CREATE, UPDATE
     * @return _data rotorna la data en caso se requiera -> SELECT , CREATE, UPDATE
     */
    public function ClientResponse($data, $errores = [] ,$status = 200, $message = ""){
        $response = array(
			'status'       => $status,
			'mensaje'      => $message,
			'errores'      => $errores,
            'data'         => $data
		);

        return $response;
    }
}