<?php

namespace App\Models;

use CodeIgniter\Model;

class SycaDataModel extends Model
{
    private $segu;
    private $data;
    private $geoserver;

    public function __construct()
    { 
        $this->segu = \Config\Database::connect();
        $this->data = \Config\Database::connect('syca_data');
    }

    public function Apivalidate($json) {
        $_petitions = (array) $json->_petitions;
        foreach($_petitions as $item){
            $types = explode("|", $item->type);

            if(count($types) > 1){
                if(strcmp($types[1], "unique") == 0){
                    // $result = (new Model())->uniqueregistro_sivi_data($json->_table, $item->name, $item->value, $types[0]);
                    $value = $item->value;

                    if($types[0] == "string") $value = "'$item->value'";

                    $query = $this->data->query("SELECT * FROM $json->_table WHERE $item->name = $value AND eliminado = 0");
                    $response = $query->getResult('array');
                    
                    $total = count($response);
                    if($total > 0) throw new \Exception("$item->name ya se encuentra registrado");
                }
            }
        }
        // echo "Apivalidate";
        // die();
    }

    public function ApiFoto($path, $idficha, $tipo = "2") {
        try {
            $path = str_replace("../public", "", $path);
            $query = $this->data->query("INSERT INTO cat_archivo(denominacion, idtipoarchivo, idficha) VALUES('$path', '$tipo' , '$idficha')"); 
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

            $query = $this->data->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
            if($this->data->insertID() == 0) throw new \Exception($this->data->error()["message"]);

            return $this->ClientResponse([["id" => $this->data->insertID(), "status" => $query]], [] ,200, "Registros creados.");
          
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

            $query = $this->data->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 

            return $this->ClientResponse([["id" => 0, "status" => $query]], [] ,200, "Registros creados.");
          
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
                    $_update_rows = $_update_rows . $item->name . '='. $this->data->escape($item->value) . ',' ;
                } else if($types[0] == "query"){
                    $_update_rows = $_update_rows . $item->name . '='. "'" . $item->value . "'" . ',' ;
                } else {
                    $_update_rows = $_update_rows . $item->name . '='. $item->value . ',';
                }
            }

            $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";
            $id_table = explode("_", $json->_table)[1];

            if($json->_table == "ads_actividadeconomica"){

                $query = $this->data->query("UPDATE $json->_table SET $_update_rows WHERE idactividadeconomica = " . "'" . $id . "';"); 
            }
            else {
                $query = $this->data->query("UPDATE $json->_table SET $_update_rows WHERE id$id_table = $id;"); 
            } 
            
            if(!$query) throw new \Exception($this->data->error()["message"]);
            return $this->ClientResponse(["status" => $query], [] ,200, "Registros actualizados.");
            
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiProcesar($json, $username){
        try {
            $this->geoserver  = \Config\Database::connect('geoserver');
            $idficha = $json->idficha;
            
            $query = $this->data->query("SELECT este, norte FROM cat_coordenada WHERE idficha = $idficha and eliminado = 0");
            $response = $query->getResult('array');

            $adicional = $response[0];
            $response[] = $adicional;

            $poly = "SRID=32719;POLYGON((";

            foreach ($response as $key => $value) {
                // $coordenadas["lat"] , $coordenadas["lon"]
                $poly .= $value["este"] . " " . $value["norte"] . ",";
            }

            $poly = substr($poly, 0, -1);
            $poly .= "))";

            $query2 = $this->geoserver->query('SELECT * FROM public."utm_84_lotes" WHERE idficha = ' . $idficha)->getResult('array');
            $total = count($query2);

            if($total > 0) {
                // ACTUALIZAR
                $response2 = $this->geoserver->query('UPDATE public."utm_84_lotes" SET geom = '. "'$poly'::geometry" .' WHERE idficha = ' . $idficha); 
            } else {
                // CREAR
                $response2 = $this->geoserver->query('INSERT INTO public."utm_84_lotes" (geom, idficha) VALUES ('. "'$poly'::geometry" .' , '.$idficha.' )'); 
            }

            return $this->ClientResponse(["status" => $response2], [] , 200, "Registros actualizados.");

        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiUpdateId($json, $id, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_update_rows = "";

            foreach($_petitions as $item){
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_update_rows = $_update_rows . $item->name . '='. $this->data->escape($item->value) . ',' ;
                } else if($types[0] == "query"){
                    $_update_rows = $_update_rows . $item->name . '='. "'" . $item->value . "'" . ',' ;
                } else {
                    $_update_rows = $_update_rows . $item->name . '='. $item->value . ',';
                }
            }

            $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";
            $id_table = $json->_id;

            $query = $this->data->query("UPDATE $json->_table SET $_update_rows WHERE $id_table = $id;"); 
            
            if(!$query) throw new \Exception($this->data->error()["message"]);
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
            $tipo_OR = [];

            $offset = 0;
            $adicional = "";
            $order = "";
            $sort = "";

            if(count($_petitions) > 0){

                foreach($_petitions as $item){
                    if($item->name == "_script"){
                        $_query = $this->segu->query("SELECT * FROM query WHERE denominacion = '$item->value' LIMIT 1")->getResult('array')[0]["descripcion"]; 
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

                        if($item->name == "@ORDER_BY") {
                            $sort = ($item->type == "string" )? "DESC" : $item->type;
                            $order = $item->value;
                        }
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
                                ? " LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))"
                                : " OR LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";

                        $total = count($this->data->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";

                        $_query .= " LIMIT $_limit OFFSET $offset";

                    }else {
                        $total = count($this->data->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";
                        $_query .= " LIMIT $_limit OFFSET $offset";
                    }

                    $resultado = $this->data->query($_query)->getResult('array');
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
                                ? " LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))"
                                : " OR LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";
                    }       
                
                    if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";
                    
                    $resultado = $this->data->query($_query)->getResult('array');

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

                $query = $this->data->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
                if($this->data->insertID() == 0) throw new \Exception($this->data->error()["message"]);
            }
            return $this->ClientResponse([["_id" => $this->data->insertID(), "_status" => $query]], [] ,200, "Registros creados.");

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

    /**
     * API DEBUG
     */

    public function ApiCreateArrayDebug($json, $username){
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

                echo "INSERT INTO $json->_table ($_create_rows) VALUES ($_values);";
                die();

                $query = $this->data->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
                if($this->data->insertID() == 0) throw new \Exception($this->data->error()["message"]);
            }
            return $this->ClientResponse([["_id" => $this->data->insertID(), "_status" => $query]], [] ,200, "Registros creados.");

        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiqueryDebug($json){
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
            $tipo_OR = [];

            $offset = 0;
            $adicional = "";
            $order = "";
            $sort = "";

            if(count($_petitions) > 0){

                foreach($_petitions as $item){
                    if($item->name == "_script"){
                        $_query = $this->segu->query("SELECT * FROM query WHERE denominacion = '$item->value' LIMIT 1")->getResult('array')[0]["descripcion"]; 
                    }else {
                        
                        if($item->type != "string"){
                            $_query = str_replace($item->name, $item->value, $_query);
                        }else {
                            $_query = str_replace($item->name, "'$item->value'", $_query);
                        }

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

                        if($item->name == "@ORDER_BY") {
                            $sort = ($item->type == "string" )? "DESC" : $item->type;
                            $order = $item->value;
                        }
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
                                ? " LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))"
                                : " OR LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";

                        $total = count($this->data->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";

                        $_query .= " LIMIT $_limit OFFSET $offset";

                    }else {
                        $total = count($this->data->query($_query)->getResult('array'));
                        if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";
                        $_query .= " LIMIT $_limit OFFSET $offset";
                    }

                    echo $_query;
                    die();

                    $resultado = $this->data->query($_query)->getResult('array');
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
                                ? " LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))"
                                : " OR LOWER(unaccent($filtros_OR[$i])) LIKE LOWER(unaccent('$buscar_OR[$i]'))";
                        }

                        if(count($buscar_OR) > 0) $_query .= " AND ( " . $adicional . " ) ";
                    }       
                
                    if(strlen($order) > 0) $_query .= " ORDER BY " . $order . " $sort";
                    
                    echo $_query;
                    die();

                    $resultado = $this->data->query($_query)->getResult('array');

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

    public function ApiCreateDebug($json, $username){
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

            echo "INSERT INTO $json->_table ($_create_rows) VALUES ($_values);";
            die();

            $query = $this->data->query("INSERT INTO $json->_table ($_create_rows) VALUES ($_values);"); 
            if($this->data->insertID() == 0) throw new \Exception($this->data->error()["message"]);

            return $this->ClientResponse([["id" => $this->data->insertID(), "status" => $query]], [] ,200, "Registros creados.");
          
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiUpdateDebug($json, $id, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_update_rows = "";

            foreach($_petitions as $item){
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_update_rows = $_update_rows . $item->name . '='. $this->data->escape($item->value) . ',' ;
                } else if($types[0] == "query"){
                    $_update_rows = $_update_rows . $item->name . '='. "'" . $item->value . "'" . ',' ;
                } else {
                    $_update_rows = $_update_rows . $item->name . '='. $item->value . ',';
                }
            }

            $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";
            $id_table = explode("_", $json->_table)[1];

            echo "UPDATE $json->_table SET $_update_rows WHERE id$id_table = $id;";
            die();

            if($json->_table == "ads_actividadeconomica"){

                $query = $this->db->query("UPDATE $json->_table SET $_update_rows WHERE id$json->_table = " . "'" . $id . "';"); 
            } else {
                $query = $this->db->query("UPDATE $json->_table SET $_update_rows WHERE id$json->_table = $id;"); 
            } 
            
            if(!$query) throw new \Exception($this->data->error()["message"]);
            return $this->ClientResponse(["status" => $query], [] ,200, "Registros actualizados.");
            
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function ApiUpdateIdDebug($json, $id, $username){
        try {
            $_petitions = (array) $json->_petitions;
            $_update_rows = "";

            foreach($_petitions as $item){
                $types = explode("|", $item->type);

                if($types[0] == "string"){
                    $_update_rows = $_update_rows . $item->name . '='. $this->data->escape($item->value) . ',' ;
                } else if($types[0] == "query"){
                    $_update_rows = $_update_rows . $item->name . '='. "'" . $item->value . "'" . ',' ;
                } else {
                    $_update_rows = $_update_rows . $item->name . '='. $item->value . ',';
                }
            }

            $_update_rows = $_update_rows . "usuarioactualizo = " . "'" . $username . "'" . ",fechaactualizo=" . "'" .date('Y-m-d H:i:s') . "'";
            $id_table = $json->_id;

            echo "UPDATE $json->_table SET $_update_rows WHERE $id_table = $id;";
            die();

            $query = $this->data->query("UPDATE $json->_table SET $_update_rows WHERE $id_table = $id;"); 
            
            if(!$query) throw new \Exception($this->data->error()["message"]);
            return $this->ClientResponse(["status" => $query], [] ,200, "Registros actualizados.");
            
        } catch (\Error $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

     /**
      * END API DEBUG
      */
}
