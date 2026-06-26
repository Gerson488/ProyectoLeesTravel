<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Trips.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idTraveler = $data["idTraveler"] ?? null; 
    if ($idTraveler && is_numeric($idTraveler) && $idTraveler > 0) {
        $tripsManager = new Trips(); 
        echo $response->responseSuccessValidation($tripsManager->getMyTripsByTraveler($idTraveler));
    } else {
        echo $response->responseErrorMessage("ID de viajero (idTraveler) requerido y debe ser un número válido.");
    }
} else {
    echo $response->responseError();
}
?>