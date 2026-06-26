<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $idTraveler   = $data["idTraveler"] ?? null;
    $firstName    = $data["firstName"] ?? null;
    $lastName     = $data["lastName"] ?? null;
    $birthDate    = $data["birthDate"] ?? null;
    $gender       = $data["gender"] ?? null;
    $nationality  = $data["nationality"] ?? null;
    $docType      = $data["documentType"] ?? null;
    $docNumber    = $data["idCardPassport"] ?? null;
    $eContact     = $data["emergencyContact"] ?? null;
    $ePhone       = $data["emergencyPhone"] ?? null;

    $validaParametros = array($idTraveler, $firstName, $lastName, $birthDate, $gender, $nationality, $docType, $docNumber);

    if (validationParametros($validaParametros)) {
        $travelerManager = new Traveler();
        
        $travelerBE = new TravelerBE(
            $idTraveler, 
            $firstName, 
            $lastName, 
            $birthDate, 
            $gender, 
            $nationality, 
            $docType, 
            $docNumber, 
            $eContact, 
            $ePhone
        );

        echo $response->responseSuccessValidation($travelerManager->updateTraveler($travelerBE));
    } else {
        echo $response->responseErrorMessage("ID de viajero o campos faltantes para la actualización.");
    }
} else {
    echo $response->responseError();
}

function validationParametros($parametros) {
    foreach ($parametros as $parametro) {
        if (!isset($parametro) || trim($parametro) === "") return false;
    }
    return true;
}
?>