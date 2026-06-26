<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Passenger.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $inputData = json_decode(file_get_contents("php://input"), true);

    $idPassenger    = $inputData["idPassenger"] ?? null;
    $boardingStatus = $inputData["boardingStatus"] ?? null;

    if (!empty($idPassenger) && is_numeric($idPassenger) && !empty($boardingStatus)) {
        
        $passengerManager = new Pasajero();
        echo json_encode($passengerManager->updateBoardingStatusApp((int)$idPassenger, $boardingStatus));

    } else {
        echo $response->responseErrorMessage("Faltan parámetros obligatorios en el JSON.");
    }
} else {
    echo $response->responseError();
}
?>