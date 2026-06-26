<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../Config/AuthMiddleware.php');
require_once(__DIR__ . '/../../Core/Usuario.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$usuarioLogueado = verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $idUser     = $data["idUser"] ?? null;
    $email      = $data["email"] ?? null;
    $accessRole = $data["accessRole"] ?? null;
    $userStatus = $data["userStatus"] ?? 1;
    $password   = $data["password"] ?? null;

    $validaParametros = array($idUser, $email, $accessRole);

    if (validationParametros($validaParametros)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $usuarioManager = new Usuario();
            
            $usuarioBE = new UsuarioBE(
                $idUser, 
                null, 
                $email, 
                $password, 
                'default_user.png', 
                $accessRole, 
                $userStatus
            );
            
            echo $response->responseSuccessValidation($usuarioManager->updateUser($usuarioBE));
        } else {
            echo $response->responseErrorMessage("El formato del correo electrónico no es válido.");
        }
    } else {
        echo $response->responseErrorMessage("Faltan campos obligatorios para actualizar (idUser, email, accessRole).");
    }
} else {
    echo $response->responseError();
}

function validationParametros($parametros) {
    foreach ($parametros as $parametro) {
        if ($parametro === null || (is_string($parametro) && trim($parametro) === "")) return false;
    }
    return true;
}
?>