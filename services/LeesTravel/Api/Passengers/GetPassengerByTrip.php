<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Passenger.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTrip = $data["Id_Trip"] ?? null;

    try {
        $pasajeroManager = new Pasajero();
        if (empty($idTrip) || $idTrip === 'ALL') {
            $resultado = $pasajeroManager->getAllPassengers();
            echo $response->responseSuccessValidation($resultado);
        } else {
            $resultado = $pasajeroManager->getPassengersByTrip($idTrip);
            echo $response->responseSuccessValidation($resultado);
        }

    } catch (Exception $e) {
        echo $response->responseErrorMessage("Error en el servidor: " . $e->getMessage());
    }
} else {
    echo $response->responseError();
}
?>