<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Passenger.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CAMBIO CLAVE: Leer el cuerpo de la petición enviado por Axios
    $data = json_decode(file_get_contents("php://input"), true);

    $idPassenger       = $data["idPassenger"] ?? null;
    $idBooking         = $data["idBooking"] ?? null;
    $idTraveler        = $data["idTraveler"] ?? null;
    $idTrip            = $data["idTrip"] ?? null;
    $cabinNumber       = $data["cabinNumber"] ?? null;
    $specialAssistance = $data["specialAssistance"] ?? null;
    // Agregamos boardingStatus por si acaso
    $boardingStatus    = $data["boardingStatus"] ?? 0;

    // Validación corregida (asegúrate de que todos estos campos se envíen)
    if (!empty($idPassenger) && !empty($idTraveler) && !empty($idTrip)) {
        
        $passengerManager = new Pasajero();
        
        $pBE = new PasajeroBE(
            $idPassenger, 
            $idBooking, 
            $idTraveler, 
            $idTrip, 
            $cabinNumber, 
            $boardingStatus, 
            $specialAssistance
        );

        echo $response->responseSuccessValidation($passengerManager->updatePassenger($pBE));

    } else {
        echo $response->responseErrorMessage("Faltan parámetros obligatorios: ID, Viajero y Trip son necesarios.");
    }
} else {
    echo $response->responseError();
}
?>