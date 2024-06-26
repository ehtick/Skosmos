<?php

/**
 * Includes the side wide settings.
 */
require_once '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *"); // enable CORS

try {
    $model = new Model();
    $controller = new EntityController($model);
    $request = new Request($model);


    $controller->redirect($request);

} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo('ERROR: ' . $e->getMessage());
}
