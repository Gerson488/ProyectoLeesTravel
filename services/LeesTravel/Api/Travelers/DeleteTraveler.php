<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idTraveler = $data["idTraveler"] ?? null;

    if ($idTraveler && is_numeric($idTraveler)) {
        $travelerManager = new Traveler();
        echo $response->responseSuccessValidation($travelerManager->deleteTraveler($idTraveler));
    } else {
        echo $response->responseErrorMessage("ID de viajero no válido para eliminación.");
    }
} else {
    echo $response->responseError();
}
?>