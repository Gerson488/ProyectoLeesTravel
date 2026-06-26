<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Passenger.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idPassenger = $data["idPassenger"] ?? null;

    if (!empty($idPassenger) && is_numeric($idPassenger)) {
        $pasajeroManager = new Pasajero();
        echo $response->responseSuccessValidation($pasajeroManager->deletePassenger($idPassenger));
    } else {
        echo $response->responseErrorMessage("ID de pasajero no válido.");
    }
} else {
    echo $response->responseError();
}
?>