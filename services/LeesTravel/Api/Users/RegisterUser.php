<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../Config/AuthMiddleware.php');
require_once(__DIR__ . '/../../Core/Usuario.php'); 
require_once(__DIR__ . '/../../Config/Setting.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$usuarioLogueado = verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $idTraveler = $data["idTraveler"] ?? null;
    $email      = $data["email"] ?? null;
    $password   = $data["password"] ?? null;
    $accessRole = $data["accessRole"] ?? 'Pasajero';

    $validaParametros = array($idTraveler, $email, $password);

    if (validationParametros($validaParametros)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            $db = (new Setting())->getConnection();
            
            $sqlCheckTraveler = "SELECT 1 FROM travelers WHERE Id_Traveler = :idT LIMIT 1";
            $stmtCheckT = $db->prepare($sqlCheckTraveler);
            $stmtCheckT->execute([':idT' => $idTraveler]);
            if (!$stmtCheckT->fetchColumn()) {
                echo $response->responseErrorMessage("Error crítico: El ID de Viajero (" . $idTraveler . ") no existe en el sistema. Primero debe registrar el perfil físico del viajero.");
                exit();
            }

            $sqlCheckUser = "SELECT 1 FROM system_users WHERE Id_Traveler = :idT LIMIT 1";
            $stmtCheckU = $db->prepare($sqlCheckUser);
            $stmtCheckU->execute([':idT' => $idTraveler]);
            if ($stmtCheckU->fetchColumn()) {
                echo $response->responseErrorMessage("Acceso denegado: Este viajero ya cuenta con un usuario y contraseña asignados en el sistema.");
                exit();
            }

            $usuarioManager = new Usuario();
            
            $usuarioBE = new UsuarioBE(
                null, 
                $idTraveler, 
                $email, 
                $password, 
                'default_user.png', 
                $accessRole, 
                1
            );

            echo $response->responseSuccessValidation($usuarioManager->createUser($usuarioBE));

        } else {
            echo $response->responseErrorMessage("El formato del correo electrónico no es válido.");
        }
    } else {
        echo $response->responseErrorMessage("Faltan campos obligatorios para el registro (idTraveler, email, password).");
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