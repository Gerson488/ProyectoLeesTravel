<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Trips.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idTrip = $data["idTrip"] ?? null;

    if ($idTrip && is_numeric($idTrip)) {
        $tripsManager = new Trips();
        echo $response->responseSuccessValidation($tripsManager->getTripById($idTrip));
    } else {
        echo $response->responseErrorMessage("Se requiere un ID de viaje válido para la búsqueda.");
    }
} else {
    echo $response->responseError();
}
?>