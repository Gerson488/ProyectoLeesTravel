<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $idTraveler = $data["idTraveler"] ?? null;

    if ($idTraveler) {
        if (is_numeric($idTraveler) && $idTraveler > 0) {
            $travelerManager = new Traveler();
            echo $response->responseSuccessValidation($travelerManager->getTravelerById($idTraveler));
        } else {
            echo $response->responseErrorMessage("El ID del viajero debe ser un número válido.");
        }
    } else {
        echo $response->responseErrorMessage("Falta el ID del viajero (idTraveler).");
    }
} else {
    echo $response->responseError();
}
?>