<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $firstName    = $data["firstName"] ?? null;
    $lastName     = $data["lastName"] ?? null;
    $birthDate    = $data["birthDate"] ?? null;
    $gender       = $data["gender"] ?? null;
    $nationality  = $data["nationality"] ?? null;
    $docType      = $data["documentType"] ?? null;
    $docNumber    = $data["idCardPassport"] ?? null;
    $eContact     = $data["emergencyContact"] ?? null;
    $ePhone       = $data["emergencyPhone"] ?? null;

    $validaParametros = array($firstName, $lastName, $birthDate, $gender, $nationality, $docType, $docNumber);

    if (validationParametros($validaParametros)) {
        if (validationDate($birthDate)) {
            if (validationGender($gender)) {
                if (validationDocType($docType)) {
                    
                    $travelerManager = new Traveler();
                    $check = $travelerManager->getTravelerByDocument($docNumber);
                    
                    if ($check['status'] == 200) {
                        echo $response->responseErrorMessage("Este número de documento ya está registrado.");
                    } else {
                        $travelerBE = new TravelerBE(
                            null, $firstName, $lastName, $birthDate, $gender, 
                            $nationality, $docType, $docNumber, $eContact, $ePhone
                        );

                        echo $response->responseSuccessValidation($travelerManager->createTraveler($travelerBE));
                    }

                } else { echo $response->responseErrorMessage("Tipo de documento no válido (DNI, PAS, CE)."); }
            } else { echo $response->responseErrorMessage("Género no válido."); }
        } else { echo $response->responseErrorMessage("Formato de fecha de nacimiento incorrecto (YYYY-MM-DD)."); }
    } else { echo $response->responseErrorMessage("Faltan campos obligatorios."); }
} else { echo $response->responseError(); }

function validationGender($gender) {
    return in_array($gender, ['M', 'F', 'Otro']);
}

function validationDocType($type) {
    return in_array($type, ['DNI', 'PAS', 'CE']);
}

function validationDate($date) {
    return preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date);
}

function validationParametros($parametros) {
    foreach ($parametros as $parametro) {
        if (!isset($parametro) || trim($parametro) === "") return false;
    }
    return true;
}
?>