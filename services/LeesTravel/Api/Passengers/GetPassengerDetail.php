<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Passenger.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $idPassengerToView    = $data["idPassengerToView"] ?? null; 
    $idTravelerRequesting = $data["idTravelerRequesting"] ?? null;
    $roleRequesting       = $data["roleRequesting"] ?? null;

    if ($idPassengerToView && $idTravelerRequesting && $roleRequesting) {
        $passengerManager = new Pasajero(); 
        
        echo $response->responseSuccessValidation(
            $passengerManager->getPassengerDetail($idPassengerToView, $idTravelerRequesting, $roleRequesting)
        );
    } else {
        echo $response->responseErrorMessage("Faltan parámetros de seguridad (idPassengerToView, idTravelerRequesting, roleRequesting).");
    }
} else {
    echo $response->responseError();
}
?>