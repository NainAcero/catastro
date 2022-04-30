<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\AuthController;

use App\Models\ApiModel;
use App\Models\ErrorModel;
use App\Models\SycaDataModel;

class ApiController extends BaseController
{
    use ResponseTrait;
    protected $api;
    protected $username;

    public function __construct()
    { 
        //ServiceData
        $uri = $_SERVER['REQUEST_URI'];
        $info = explode('/', $uri)[4];
        $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;

        if($info == "ServiceData") $this->api = new SycaDataModel();
        else $this->api = new ApiModel();
    }

    public function create()
    {
        try {
            $json = $this->request->getJSON();
            $this->api->Apivalidate($json);

            return $this->respond($this->api->ApiCreate($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function create_sin_id()
    {          
        try {
            $json = $this->request->getJSON();

            $this->api->Apivalidate($json);

            return $this->respond($this->api->ApiCreateSinId($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }


    public function upload($idficha)
    {
        $pdfMines = array('application/pdf');
        $imgMines = array('image/bmp', 'image/png', 'image/jpeg', 'image/gif');
        if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $pdfMines)){
            $nombre_carpeta = PATH_IMPORTACION_IMAGES . $idficha . "/";
            if (!file_exists($nombre_carpeta)) mkdir($nombre_carpeta, 0777, true);
            chmod($nombre_carpeta, 0777);
            
            $extension  =  explode(".", $_FILES["file"]["name"]);
            $nombre_archivo  = $extension[0];

            $path =  $nombre_carpeta . $nombre_archivo .'.'. $extension[1];

            $mover = move_uploaded_file($_FILES["file"]["tmp_name"], $path);
            $this->api->ApiFoto($path, $idficha, "1");
            return $this->respond(null, 201);

        } else if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $imgMines)) {
            if($idficha != null ){
                $nombre_carpeta = PATH_IMPORTACION_IMAGES . $idficha . "/";
                if (!file_exists($nombre_carpeta)) mkdir($nombre_carpeta, 0777, true);
                chmod($nombre_carpeta, 0777);
                
                $extension  =  explode(".", $_FILES["file"]["name"]);
                $nombre_archivo  = $extension[0];
    
                $path =  $nombre_carpeta . $nombre_archivo .'.'. $extension[1];
    
                $mover = move_uploaded_file($_FILES["file"]["tmp_name"], $path);
                $this->api->ApiFoto($path, $idficha);
                return $this->respond(null, 201);
            }
        } else throw new \Exception("Ingresó un archivo inválido.");
    }

    public function update($id = NULL)
    {
        try {
            $json = $this->request->getJSON();
            $this->api->Apivalidate($json);

            if($id != null) return $this->respond($this->api->ApiUpdate($json, $id, $this->username), 201);
            else throw new \Exception("Id no puede ser nulo.");

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Editar registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    public function updateId($id = NULL)
    {
        try {
            $json = $this->request->getJSON();
            $this->api->Apivalidate($json);

            if($id != null) return $this->respond($this->api->ApiUpdateId($json, $id, $this->username), 201);
            else throw new \Exception("Id no puede ser nulo.");

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Editar registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    public function procesar()
    {
        try {
            $json = $this->request->getJSON();
            $this->api->Apivalidate($json);

            return $this->respond($this->api->ApiProcesar($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }
    
    public function create_many()
    {
        try {
            $json = $this->request->getJSON();
            $this->api->Apivalidate($json);
            
            return $this->respond($this->api->ApiCreateArray($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function reporte(){
        try {
            $json = $this->request->getJSON();
            $this->api->ApiReporte($json);
            
        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Descargar reporte", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    public function query()
    {
        try {
            $json = $this->request->getJSON();
            return $this->respond($this->api->Apiquery($json), 200);
            
        } catch (\Exception $e) {
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    /**
     * MODO DEBUG
     */

    public function create_many_debug()
    {
        try {
            $json = $this->request->getJSON();
            
            return $this->respond($this->api->ApiCreateArrayDebug($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function create_debug()
    {
        try {
            $json = $this->request->getJSON();
            return $this->respond($this->api->ApiCreateDebug($json, $this->username), 201);

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Crear registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function query_debug()
    {
        try {
            $json = $this->request->getJSON();
            return $this->respond($this->api->ApiqueryDebug($json), 200);
            
        } catch (\Exception $e) {
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    public function update_debug($id = NULL)
    {
        try {
            $json = $this->request->getJSON();
            // $this->Apivalidate($json, $this->db);

            if($id != null) return $this->respond($this->api->ApiUpdateDebug($json, $id, $this->username), 201);
            else throw new \Exception("Id no puede ser nulo.");

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Editar registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }

    public function updateId_debug($id = NULL)
    {
        try {
            $json = $this->request->getJSON();
            // $this->Apivalidate($json, $this->db);

            if($id != null) return $this->respond($this->api->ApiUpdateIdDebug($json, $id, $this->username), 201);
            else throw new \Exception("Id no puede ser nulo.");

        } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Editar registros en la base de datos", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }
     /**
      * END MODO DEBUG
      */

    /**
       * BACKUP
       */
    public function backup_data()
    {
        try {
            $json = $this->request->getJSON();
            return $this->respond($this->api->ApiBackupData($json), 200);
            
        } catch (\Exception $e) {
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,500), 500);
        }
    }
}
