<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->post('/api/auth/login', 'AuthController::login');
$routes->post('/api/auth/logout', 'AuthController::logout');
$routes->post('/api/auth/roles', 'AuthController::roles_accesos');
$routes->post('/api/auth/restablecer', 'AuthController::restablecer_contrasenia');
$routes->post('/api/auth/recover', 'AuthController::recover_password');
$routes->post('/api/auth/correo', 'AuthController::correo');
$routes->post('/api/auth/cambiar_contra', 'AuthController::cambiar_contra');

$routes->post('/api/ServiceArray/V1', 'ApiController::create_many');
$routes->post('/api/Service/V1', 'ApiController::create');
$routes->post('/api/Service/V2', 'ApiController::create_sin_id');

$routes->post('/api/Service/V1/upload/(:num)', 'ApiController::upload/$1');
$routes->post('/api/Service/V1/query', 'ApiController::query');
$routes->put('/api/Service/V1/(:num)', 'ApiController::update/$1');

$routes->put('/api/Service/ArrayByRows/V1', 'ApiController::update_many_by_rows');

$routes->post('/api/Service/V1/reporte', 'ApiController::reporte');
$routes->post('/api/ServiceData/V1/reporte', 'ApiController::reporte');

$routes->post('/api/ServiceData/V1/upload/(:num)', 'ApiController::upload/$1');
$routes->post('/api/ServiceData/ServiceArray/V1', 'ApiController::create_many');
$routes->post('/api/ServiceData/V1/query', 'ApiController::query');
$routes->post('/api/ServiceData/V1', 'ApiController::create');
$routes->post('/api/ServiceData/V2', 'ApiController::create_sin_id');
$routes->put('/api/ServiceData/V1/(:num)', 'ApiController::update/$1');
$routes->put('/api/ServiceData/ID/(:num)', 'ApiController::updateId/$1');

/**
 * DEBUG CONSULTAS
 */
$routes->post('/api/ServiceData/ServiceArray/V1/debug', 'ApiController::create_many_debug');
$routes->post('/api/ServiceData/V1/query/debug', 'ApiController::query_debug');
$routes->post('/api/ServiceData/V1/debug', 'ApiController::create_debug');
$routes->put('/api/ServiceData/V1/debug/(:num)', 'ApiController::update_debug/$1');
$routes->put('/api/ServiceData/ID/debug/(:num)', 'ApiController::updateId_debug/$1');
$routes->post('/api/ServiceData/V1/procesar', 'ApiController::procesar');

/**
 * END DEBUG CONSULTAS
 */

$routes->get('/api/ServiceGeoserver/V1/(:any)',  'GeoserverController::get_jgson/$1');
$routes->post('/api/ServiceGeoserver/V1/procesar/(:any)', 'GeoserverController::procesarUTM/$1');
$routes->post('/api/ServiceGeoserver/V1/(:any)', 'GeoserverController::get_jgson/$1');
$routes->post('/api/ServiceGeoserver/info',   'GeoserverController::get_info');
$routes->post('/api/ServiceGeoserver/idficha',   'GeoserverController::get_idficha');
$routes->post('/api/ServiceGeoserver/utm',   'GeoserverController::get_utm');
$routes->post('/api/ServiceGeoserver/converterutm',   'GeoserverController::converterutm');
$routes->post('/api/ServiceGeoserver/buscarutm',   'GeoserverController::buscarutm');

/** BACKUP */

$routes->post('/api/Service/backup_data', 'ApiController::backup_data');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
