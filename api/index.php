<?php
include_once '../database/database.php';
include_once './characterController.php';
include_once './powerController.php';

header("Content-Type: application/json; charset=UTF-8");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

/**
 * @OA\Info(version="1.0.0",
 *          title="My API")
 */
if ($uri[2] !== 'api' || $uri[3] !== 'characters' && $uri[3] !== 'powers') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// get keywords
$keywords=isset($_GET["s"]) ? $_GET["s"] : null;

// get page
$page=isset($_GET["page"]) ? $_GET["page"] : "";


// the user id is, of course, optional and must be a number:
$id = null;
if (isset($uri[4])) {
    $id = $uri[4];
}


$requestMethod = $_SERVER["REQUEST_METHOD"];

// instantiate database and product object
$database = new Database();
$dbConnection = $database->getConnection();

if ($uri[3] === 'characters') {
    // pass the request method and user ID to the PersonController and process the HTTP request:
    $controller = new CharacterController($dbConnection, $requestMethod, $id, $keywords, $page);
    if($uri[5]==='powers'){
        $powerId=$uri[6];
        $controller = new CharacterController($dbConnection, $requestMethod, $id,$powerId ,null);
    }
    $controller->processRequest();

}else if ($uri[3] === 'powers') {
    // pass the request method and user ID to the PersonController and process the HTTP request:
    $controller = new PowerController($dbConnection, $requestMethod, $id);
    $controller->processRequest();
}