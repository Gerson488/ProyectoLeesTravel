<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../Config/AuthMiddleware.php');
require_once(__DIR__ . '/../../Core/Usuario.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$usuarioLogueado = verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $usuarioManager = new Usuario();
    echo $response->responseSuccessValidation($usuarioManager->getAllUsers());
} else {
    echo $response->responseError();
}
?>