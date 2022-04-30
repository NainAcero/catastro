<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ApiModel;

class GeoserverModel extends Model {
    private $geoserver;
    private $connection;

    public function __construct()
    { 
        $this->geoserver  = \Config\Database::connect('geoserver');
        $this->connection = \Config\Database::connect('syca_data');
    }

    public function getcoordenadaCodigoCatastro($codigocatastro) 
    {   
    	$query = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)) FROM public."utm_84_lotes" WHERE idficha = '."'$codigocatastro';")->getResult('array')[0]["st_asgeojson"];
        return $query;
    }

    public function ApiIdficha($idficha) {

        $query = $this->connection->query("SELECT tf2.denominacion as tipoentidad,tf3.denominacion as estadocivil, tf4.denominacion as tipodocumentoregistro, tf5.denominacion as clasificacionprevio,
        fi.idficha, fi.numeroficha, co.idcodigounico, co.denominacion codigoreferencial, 
        en.documentoregistro, CONCAT(apellidopaterno, ' ', apellidomaterno, ' ', nombre) as entidad,
        tf.denominacion tipoficha,sc.denominacion as sector,mz.denominacion as manzana,lt.denominacion as lote,
        fi.activo, fx.frentemedidacampo, fx.derechamedidacampo, fx.izquierdamedidacampo,
        fx.fondomedidacampo, fx.fondocolinda, fx.derechacolinda, fx.frentecolinda,
        fx.izquierdacolinda, fx.evaluacioncolidante, fx.evaluacionjardin, fx.evaluacionareapublica,
        fx.evaluacionintangible, fx.areaterrenoadquirido
        FROM cat_ficha fi 
        LEFT JOIN cat_fichaindividual fx ON fx.idficha = fi.idficha
        LEFT JOIN ope_entidad en ON en.identidad = fx.identidadtitular 
        LEFT JOIN cat_codigounico co ON co.idcodigounico = fi.idcodigounico
        LEFT JOIN ads_catalogo tf ON tf.idcatalogo = fi.idtipoficha
        LEFT JOIN ads_lote lt ON lt.idlote = co.idlote
        LEFT JOIN ads_manzana mz ON mz.idmanzana = lt.idmanzana
        LEFT JOIN ads_sector sc ON sc.idsector = mz.idsector
        LEFT JOIN ads_catalogo tf2 ON tf2.idcatalogo = en.idtipoentidad
        LEFT JOIN ads_catalogo tf3 ON tf3.idcatalogo = en.idestadocivil
        LEFT JOIN ads_catalogo tf4 ON tf4.idcatalogo = en.idtipodocumentoregistro
        LEFT JOIN ads_catalogo tf5 ON tf5.idcatalogo = fx.idclasificacionpredio
        WHERE fi.eliminado = 0 AND  fi.idficha = '$idficha' LIMIT 1")->getResult('array')[0];

        $query2 = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)), ST_AsGeoJSON(ST_Transform(geom,32719)) as utm FROM public."utm_84_lotes" WHERE idficha = '."'$idficha';")->getResult('array')[0];

        $info = json_decode($query2["st_asgeojson"], true);
        $info2 = json_decode($query2["utm"], true);

        $utml = [];
        $coordinates = $info["coordinates"][0];
        array_pop($coordinates);
        $count = count($coordinates);

        foreach ($coordinates as $key => $value) {
            $datainfo = ll2utm($value[1], $value[0]);

            $data = array(
                "x"     => truncar($datainfo["x"] - 0.0001, 4),
                "y"     => truncar($datainfo["y"] - 0.0001, 4),
                "zone"  => 19,
                'vertice'       => "P".($key + 1),
                'distancia'     => "",
                'lado'          => (($key + 1) != $count) ? "P".($key + 1)." - "."P".($key + 2) : "P".($key + 1)." - "."P1"
            );

            if(($key + 1) != $count) {
                $data["distancia"] = truncar(harvestine($info["coordinates"][0][$key][0], $info["coordinates"][0][$key][1], $info["coordinates"][0][$key + 1][0], $info["coordinates"][0][$key + 1][1]), 2);
            } else {
                $data["distancia"] = truncar(harvestine($info["coordinates"][0][$key][0], $info["coordinates"][0][$key][1], $info["coordinates"][0][0][0], $info["coordinates"][0][0][1]), 2);
            }
            
            $utml[] = ($data);
        }

        return (new ApiModel())->ClientResponse([
            "informacion" => $query,
            "geoserver" => $query2["st_asgeojson"],
            "utml" => $utml
        ], [] ,200, "Información GEOSERVER.");
    }

    public function ApiInfo($json) {
        $search = $json->buscar;

        $query = $this->connection->query("SELECT tf2.denominacion as tipoentidad,tf3.denominacion as estadocivil, tf4.denominacion as tipodocumentoregistro, tf5.denominacion as clasificacionprevio,
        fi.idficha, fi.numeroficha, co.idcodigounico, co.denominacion codigoreferencial, 
        en.documentoregistro, CONCAT(apellidopaterno, ' ', apellidomaterno, ' ', nombre) as entidad,
        tf.denominacion tipoficha,sc.denominacion as sector,mz.denominacion as manzana,lt.denominacion as lote,
        fi.activo, fx.frentemedidacampo, fx.derechamedidacampo, fx.izquierdamedidacampo,
        fx.fondomedidacampo, fx.fondocolinda, fx.derechacolinda, fx.frentecolinda,
        fx.izquierdacolinda, fx.evaluacioncolidante, fx.evaluacionjardin, fx.evaluacionareapublica,
        fx.evaluacionintangible, fx.areaterrenoadquirido
        FROM cat_ficha fi 
        LEFT JOIN cat_fichaindividual fx ON fx.idficha = fi.idficha
        LEFT JOIN ope_entidad en ON en.identidad = fx.identidadtitular 
        LEFT JOIN cat_codigounico co ON co.idcodigounico = fi.idcodigounico
        LEFT JOIN ads_catalogo tf ON tf.idcatalogo = fi.idtipoficha
        LEFT JOIN ads_lote lt ON lt.idlote = co.idlote
        LEFT JOIN ads_manzana mz ON mz.idmanzana = lt.idmanzana
        LEFT JOIN ads_sector sc ON sc.idsector = mz.idsector
        LEFT JOIN ads_catalogo tf2 ON tf2.idcatalogo = en.idtipoentidad
        LEFT JOIN ads_catalogo tf3 ON tf3.idcatalogo = en.idestadocivil
        LEFT JOIN ads_catalogo tf4 ON tf4.idcatalogo = en.idtipodocumentoregistro
        LEFT JOIN ads_catalogo tf5 ON tf5.idcatalogo = fx.idclasificacionpredio
        WHERE fi.eliminado = 0 
            AND ( LOWER(unaccent(CONCAT(apellidopaterno, ' ', apellidomaterno, ' ', nombre))) LIKE LOWER(unaccent('%$search%'))
            OR en.documentoregistro LIKE '%$search%')
        LIMIT 1")->getResult('array')[0];

        $idficha = $query["idficha"];

        $query2 = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)), ST_AsGeoJSON(ST_Transform(geom,32719)) as utm FROM public."utm_84_lotes" WHERE idficha = '."'$idficha';")->getResult('array')[0];

        $info = json_decode($query2["st_asgeojson"], true);
        $info2 = json_decode($query2["utm"], true);

        $utml = [];
        $coordinates = $info["coordinates"][0];
        array_pop($coordinates);
        $count = count($coordinates);

        foreach ($coordinates as $key => $value) {
            $datainfo = ll2utm($value[1], $value[0]);

            $data = array(
                "x"     => truncar($datainfo["x"] - 0.0001, 4),
                "y"     => truncar($datainfo["y"] - 0.0001, 4),
                "zone"  => 19,
                'vertice'       => "P".($key + 1),
                'distancia'     => "",
                'lado'          => (($key + 1) != $count) ? "P".($key + 1)." - "."P".($key + 2) : "P".($key + 1)." - "."P1"
            );

            if(($key + 1) != $count) {
                $data["distancia"] = truncar(harvestine($info["coordinates"][0][$key][0], $info["coordinates"][0][$key][1], $info["coordinates"][0][$key + 1][0], $info["coordinates"][0][$key + 1][1]), 2);
            } else {
                $data["distancia"] = truncar(harvestine($info["coordinates"][0][$key][0], $info["coordinates"][0][$key][1], $info["coordinates"][0][0][0], $info["coordinates"][0][0][1]), 2);
            }

            $utml[] = ($data);
        }

        return (new ApiModel())->ClientResponse([
            "informacion" => $query,
            "geoserver" => $query2["st_asgeojson"],
            "utml" => $utml
        ], [] ,200, "Información GEOSERVER.");
    }
    
    public function ApiUtm($json) {
        $este = $json->este;
        $norte = $json->norte;

        $info = utm2ll($norte, $este, 19, false);
        $latitud = $info["lat"];
        $longitud = $info["lon"];

        $query2 = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)), idficha FROM public."utm_84_lotes"')->getResult('array');
        
        foreach ($query2 as $key => $value) {
            $info = json_decode($value["st_asgeojson"], true);
            $coordinates = $info["coordinates"][0];
            foreach ($coordinates as $key => $value2) { 
                if($key == 0) {
                    if($value2[0] == $latitud && $value2[1] == $longitud) {
                        $idficha = $value["idficha"];

                        return $this->ApiIdficha($idficha);
                    }
                }
            }
        }
    }

    public function reprocesarGeoserver($idficha) {
        $query2 = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)), ST_AsGeoJSON(ST_Transform(geom,32719)) as utm FROM public."utm_84_lotes" WHERE idficha = '."'$idficha';")->getResult('array')[0];
        $info = json_decode($query2["utm"], true);

        $coordinates = $info["coordinates"][0];
        
        $query = $this->connection->query("UPDATE cat_coordenada SET eliminado = 1 WHERE idficha = $idficha");
        
        foreach ($coordinates as $key => $value) {
            if($key != count($coordinates) - 1) {
                $query = $this->connection->query("INSERT INTO cat_coordenada(este, norte, idficha) VALUES('$value[0]', '$value[1]', '$idficha')");
            }
        }

        return (new ApiModel())->ClientResponse([], [] ,200, "Información PROCESADA.");
    }

    public function ApiConverterUtm($json) {
        $este = $json->este;
        $norte = $json->norte;

        $info = utm2ll($este, $norte, 19, false);

        return (new ApiModel())->ClientResponse($info, [] ,200, "Información GEOSERVER.");
    }

    public function ApiBuscarutm($json) {
        $coordenadas  = (array) $json->_petitions;
        $query2 = $this->geoserver->query('SELECT ST_AsGeoJSON(ST_Transform(geom,4326)), idficha, id FROM public."utm_84_lotes"')->getResult('array');

        foreach ($query2 as $key => $value) {
            $info = json_decode($value["st_asgeojson"], true);
            $coordenadasUTM = $info["coordinates"][0];
            $con = 0;

            for ($i=0; $i < count($coordenadas); $i++) { 
                $utm = $coordenadasUTM[$i];

                if (round($utm[1], 8) == round($coordenadas[$i]->lat, 8) && round($utm[0], 8) == round($coordenadas[$i]->lon, 8)) {
                    $con ++;
                }
            }

            if($con == count($coordenadas)) {
                $idficha = $value["idficha"];
                if($idficha != null && $idficha != "") {
                    return $this->ApiIdficha($idficha);
                } else {
                    return [];
                }
            }

        }
    }
    
}

function truncar($numero, $digitos)
{
    $truncar = 10**$digitos;
    return intval($numero * $truncar) / $truncar;
}

// LATITUD Y LONGITUD -> UMTL
function utm2ll($x,$y,$zone,$aboveEquator){
    if(!is_numeric($x) or !is_numeric($y) or !is_numeric($zone)) return json_encode(array('success'=>false,'msg'=>"Wrong input parameters"));
    
    $southhemi = false;
    if($aboveEquator!=true) $southhemi = true;
    
    $latlon = UTMXYToLatLon ($x, $y, $zone, $southhemi);
    return array(
        'lat'   =>  round(radian2degree($latlon[0]), 9),
        'lon'   =>  round(radian2degree($latlon[1]), 9)
    );
}

function ll2utm($lat,$lon){
    if(!is_numeric($lon)){
        return json_encode(array('success'=>false,'msg'=>"Wrong longitude value"));
    }
    if($lon<-180.0 or $lon>=180.0){
        return json_encode(array('success'=>false,'msg'=>"The longitude is out of range"));
    }
    if(!is_numeric($lat)){
        return json_encode(array('success'=>false,'msg'=>"Wrong latitude value"));
    }
    if($lat<-90.0 or $lat>90.0){
        return json_encode(array('success'=>false,'msg'=>"The longitude is out of range"));
    }
    $zone = floor(($lon + 180.0) / 6) + 1;
    //compute values
    $result = LatLonToUTMXY(degree2radian($lat),degree2radian($lon),$zone);
    $aboveEquator = false;
    if($lat >0){
        $aboveEquator = true;
    }
    return array('x'=>$result[0],'y'=>$result[1]);
}

function radian2degree($rad){
    $pi = 3.14159265358979;	
    return ($rad / $pi * 180.0);
}

function degree2radian($deg){
    $pi = 3.14159265358979;
    return ($deg/180.0*$pi);
}

function UTMCentralMeridian($zone){
    $cmeridian = degree2radian(-183.0 + ($zone * 6.0));
    return $cmeridian;
}

function LatLonToUTMXY ($lat, $lon, $zone){
    $xy = MapLatLonToXY ($lat, $lon, UTMCentralMeridian($zone));
    /* Adjust easting and northing for UTM system. */
    $UTMScaleFactor = 0.9996;
    $xy[0] = $xy[0] * $UTMScaleFactor + 500000.0;
    $xy[1] = $xy[1] * $UTMScaleFactor;
    if ($xy[1] < 0.0)
        $xy[1] = $xy[1] + 10000000.0;
    return $xy;
}

function UTMXYToLatLon ($x, $y, $zone, $southhemi){
    $latlon = array();
    $UTMScaleFactor = 0.9996;
    $x -= 500000.0;
    $x /= $UTMScaleFactor;
    /* If in southern hemisphere, adjust y accordingly. */
    if ($southhemi)
        $y -= 10000000.0;
    $y /= $UTMScaleFactor;
    $cmeridian = UTMCentralMeridian ($zone);
    $latlon = MapXYToLatLon ($x, $y, $cmeridian);	
    return $latlon;
}

function MapXYToLatLon ($x, $y, $lambda0){
    $philambda = array();
    $sm_b = 6356752.314;
    $sm_a = 6378137.0;
    $UTMScaleFactor = 0.9996;
    $sm_EccSquared = .00669437999013;
    $phif = FootpointLatitude ($y);
    $ep2 = (pow ($sm_a, 2.0) - pow ($sm_b, 2.0)) / pow ($sm_b, 2.0);
    $cf = cos ($phif);
    $nuf2 = $ep2 * pow ($cf, 2.0);
    $Nf = pow ($sm_a, 2.0) / ($sm_b * sqrt (1 + $nuf2));
    $Nfpow = $Nf;
    $tf = tan ($phif);
    $tf2 = $tf * $tf;
    $tf4 = $tf2 * $tf2;
    $x1frac = 1.0 / ($Nfpow * $cf);
    $Nfpow *= $Nf;   
    $x2frac = $tf / (2.0 * $Nfpow);
    $Nfpow *= $Nf;   
    $x3frac = 1.0 / (6.0 * $Nfpow * $cf);
    $Nfpow *= $Nf;   
    $x4frac = $tf / (24.0 * $Nfpow);
    $Nfpow *= $Nf;   
    $x5frac = 1.0 / (120.0 * $Nfpow * $cf);
    $Nfpow *= $Nf;   
    $x6frac = $tf / (720.0 * $Nfpow);
    $Nfpow *= $Nf;   
    $x7frac = 1.0 / (5040.0 * $Nfpow * $cf);
    $Nfpow *= $Nf;   
    $x8frac = $tf / (40320.0 * $Nfpow);
    $x2poly = -1.0 - $nuf2;
    $x3poly = -1.0 - 2 * $tf2 - $nuf2;
    $x4poly = 5.0 + 3.0 * $tf2 + 6.0 * $nuf2 - 6.0 * $tf2 * $nuf2- 3.0 * ($nuf2 *$nuf2) - 9.0 * $tf2 * ($nuf2 * $nuf2);
    $x5poly = 5.0 + 28.0 * $tf2 + 24.0 * $tf4 + 6.0 * $nuf2 + 8.0 * $tf2 * $nuf2;
    $x6poly = -61.0 - 90.0 * $tf2 - 45.0 * $tf4 - 107.0 * $nuf2	+ 162.0 * $tf2 * $nuf2;
    $x7poly = -61.0 - 662.0 * $tf2 - 1320.0 * $tf4 - 720.0 * ($tf4 * $tf2);
    $x8poly = 1385.0 + 3633.0 * $tf2 + 4095.0 * $tf4 + 1575 * ($tf4 * $tf2);
    $philambda[0] = $phif + $x2frac * $x2poly * ($x * $x)
        + $x4frac * $x4poly * pow ($x, 4.0)
        + $x6frac * $x6poly * pow ($x, 6.0)
        + $x8frac * $x8poly * pow ($x, 8.0);
    
    $philambda[1] = $lambda0 + $x1frac * $x
        + $x3frac * $x3poly * pow ($x, 3.0)
        + $x5frac * $x5poly * pow ($x, 5.0)
        + $x7frac * $x7poly * pow ($x, 7.0);
    
    return $philambda;
}

function FootpointLatitude ($y){
    $sm_b = 6356752.314;
    $sm_a = 6378137.0;
    $UTMScaleFactor = 0.9996;
    $sm_EccSquared = .00669437999013;
    $n = ($sm_a - $sm_b) / ($sm_a + $sm_b);
    $alpha_ = (($sm_a + $sm_b) / 2.0)* (1 + (pow ($n, 2.0) / 4) + (pow ($n, 4.0) / 64));
    $y_ = $y / $alpha_;
    $beta_ = (3.0 * $n / 2.0) + (-27.0 * pow ($n, 3.0) / 32.0)+ (269.0 * pow ($n, 5.0) / 512.0);
    $gamma_ = (21.0 * pow ($n, 2.0) / 16.0)+ (-55.0 * pow ($n, 4.0) / 32.0);
    $delta_ = (151.0 * pow ($n, 3.0) / 96.0)+ (-417.0 * pow ($n, 5.0) / 128.0);
    $epsilon_ = (1097.0 * pow ($n, 4.0) / 512.0);
    $result = $y_ + ($beta_ * sin (2.0 * $y_))
        + ($gamma_ * sin (4.0 * $y_))
        + ($delta_ * sin (6.0 * $y_))
        + ($epsilon_ * sin (8.0 * $y_));
    return $result;
}

function MapLatLonToXY ($phi, $lambda, $lambda0){
    $xy=array();
    $sm_b = 6356752.314;
    $sm_a = 6378137.0;
    $UTMScaleFactor = 0.9996;
    $sm_EccSquared = .00669437999013;
    $ep2 = (pow ($sm_a, 2.0) - pow ($sm_b, 2.0)) / pow ($sm_b, 2.0);
    $nu2 = $ep2 * pow (cos ($phi), 2.0);
    $N = pow ($sm_a, 2.0) / ($sm_b * sqrt (1 + $nu2));
    $t = tan ($phi);
    $t2 = $t * $t;
    $tmp = ($t2 * $t2 * $t2) - pow ($t, 6.0);
    $l = $lambda - $lambda0;
    $l3coef = 1.0 - $t2 + $nu2;
    $l4coef = 5.0 - $t2 + 9 * $nu2 + 4.0 * ($nu2 * $nu2);
    $l5coef = 5.0 - 18.0 * $t2 + ($t2 * $t2) + 14.0 * $nu2- 58.0 * $t2 * $nu2;
    $l6coef = 61.0 - 58.0 * $t2 + ($t2 * $t2) + 270.0 * $nu2- 330.0 * $t2 * $nu2;
    $l7coef = 61.0 - 479.0 * $t2 + 179.0 * ($t2 * $t2) - ($t2 * $t2 * $t2);
    $l8coef = 1385.0 - 3111.0 * $t2 + 543.0 * ($t2 * $t2) - ($t2 * $t2 * $t2);
    $xy[0] = $N * cos ($phi) * $l
            + ($N / 6.0 * pow (cos ($phi), 3.0) * $l3coef * pow ($l, 3.0))
            + ($N / 120.0 * pow (cos ($phi), 5.0) * $l5coef * pow ($l, 5.0))
            + ($N / 5040.0 * pow (cos ($phi), 7.0) * $l7coef * pow ($l, 7.0));
    $xy[1] = ArcLengthOfMeridian ($phi)
            + ($t / 2.0 * $N * pow (cos ($phi), 2.0) * pow ($l, 2.0))
            + ($t / 24.0 * $N * pow (cos ($phi), 4.0) * $l4coef * pow ($l, 4.0))
            + ($t / 720.0 * $N * pow (cos ($phi), 6.0) * $l6coef * pow ($l, 6.0))
            + ($t / 40320.0 * $N * pow (cos ($phi), 8.0) * $l8coef * pow ($l, 8.0));
    return $xy;
}

function ArcLengthOfMeridian($phi){
    $sm_b = 6356752.314;
    $sm_a = 6378137.0;
    $UTMScaleFactor = 0.9996;
    $sm_EccSquared = .00669437999013;
    $n = ($sm_a - $sm_b) / ($sm_a + $sm_b);
    $alpha = (($sm_a + $sm_b) / 2.0)
        * (1.0 + (pow ($n, 2.0) / 4.0) + (pow ($n, 4.0) / 64.0));
    $beta = (-3.0 * $n / 2.0) + (9.0 * pow ($n, 3.0) / 16.0)
            + (-3.0 * pow ($n, 5.0) / 32.0);
    $gamma = (15.0 * pow ($n, 2.0) / 16.0)
            + (-15.0 * pow ($n, 4.0) / 32.0);
    $delta = (-35.0 * pow ($n, 3.0) / 48.0)
            + (105.0 * pow ($n, 5.0) / 256.0);
    $epsilon = (315.0 * pow ($n, 4.0) / 512.0);
    $result = $alpha* ($phi + ($beta * sin (2.0 * $phi))
            + ($gamma * sin (4.0 * $phi))
            + ($delta * sin (6.0 * $phi))
        + ($epsilon * sin (8.0 * $phi)));
    return $result;
}

function harvestine($lat1, $long1, $lat2, $long2){ 

    //Distancia en kilometros en 1 grado distancia.
    //Distancia en millas nauticas en 1 grado distancia: $mn = 60.098;
    //Distancia en millas en 1 grado distancia: 69.174;
    //Solo aplicable a la tierra, es decir es una constante que cambiaria en la luna, marte... etc.
    $km = 111.302 * 1000;
    
    //1 Grado = 0.01745329 Radianes    
    $degtorad = 0.01745329;
    
    //1 Radian = 57.29577951 Grados
    $radtodeg = 57.29577951; 

    //La formula que calcula la distancia en grados en una esfera, llamada formula de Harvestine. Para mas informacion hay que mirar en Wikipedia
    //http://es.wikipedia.org/wiki/F%C3%B3rmula_del_Haversine
    $dlong = ($long1 - $long2); 
    $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad)) + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad) * cos($dlong * $degtorad)); 
    $dd = acos($dvalue) * $radtodeg; 

    return round(($dd * $km), 5);
}
