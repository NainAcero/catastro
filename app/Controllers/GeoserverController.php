<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

use App\Controllers\BaseController;
use App\Controllers\AuthController;
use App\Models\GeoserverModel;

class GeoserverController extends BaseController
{
    use ResponseTrait;

    protected $api;

    public function __construct()
    {  
        // $this->api = new ApiModelPublica();
    }

    public function get_jgson($codigocatastro){
        return (new GeoserverModel())->getcoordenadaCodigoCatastro($codigocatastro);
    }

    public function procesarUTM($idficha) {
        return $this->respond((new GeoserverModel())->reprocesarGeoserver($idficha), 201);
    }

    public function get_idficha() {
        try {
            $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;
            $json = $this->request->getJSON();
            return $this->respond((new GeoserverModel())->ApiIdficha($json->buscar, $this->username), 201);
       } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Geoserver", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function get_utm() {
        try {
            $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;
            $json = $this->request->getJSON();
            return $this->respond((new GeoserverModel())->ApiUtm($json, $this->username), 201);
       } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Geoserver", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function get_info() {
        try {
            $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;
            $json = $this->request->getJSON();
            return $this->respond((new GeoserverModel())->ApiInfo($json, $this->username), 201);
       } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Geoserver", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function converterutm() {
        try {
            $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;
            $json = $this->request->getJSON();
            return $this->respond((new GeoserverModel())->ApiConverterUtm($json, $this->username), 201);
       } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Geoserver", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }

    public function buscarutm() {
        try {
            $this->username = (new AuthController())->verifyTokenAuthorization()->data->usuario;
            $json = $this->request->getJSON();
            return $this->respond((new GeoserverModel())->ApiBuscarutm($json, $this->username), 201);
       } catch (\Exception $e) {
            (new ErrorModel())->insert_error("tabla: " . $json->_table?? "", "Geoserver", $e->getMessage(), $this->username);
            return $this->respond($this->api->ClientResponse([], [["mensaje" => $e->getMessage()]] ,401), 401);
        }
    }
}
