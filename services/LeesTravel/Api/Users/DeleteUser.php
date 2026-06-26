<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../Config/AuthMiddleware.php');
require_once(__DIR__ . '/../../Core/Usuario.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$usuarioLogueado = verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idUser = $data["idUser"] ?? null;

    if ($idUser && is_numeric($idUser)) {
        $usuarioManager = new Usuario();
        echo $response->responseSuccessValidation($usuarioManager->deleteUser($idUser));
    } else {
        echo $response->responseErrorMessage("ID de usuario no válido para eliminación.");
    }
} else {
    echo $response->responseError();
}
?>