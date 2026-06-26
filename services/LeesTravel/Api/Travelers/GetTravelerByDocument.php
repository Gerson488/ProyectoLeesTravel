<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $docType   = $data["documentType"] ?? null; 
    $docNumber = $data["docNumber"] ?? $data["idCardPassport"] ?? null;

    if ($docType && $docNumber) {
        if (validationDocument($docType, $docNumber)) {
            $travelerManager = new Traveler();
            echo $response->responseSuccessValidation($travelerManager->getTravelerByDocument($docNumber));
        } else {
            echo $response->responseErrorMessage("El formato del documento no coincide con el tipo seleccionado ($docType).");
        }
    } else {
        echo $response->responseErrorMessage("Falta el tipo de documento o el número de documento.");
    }
} else {
    echo $response->responseError();
}

function validationDocument($type, $number) {
    $cleanNumber = trim($number);
    $length = strlen($cleanNumber);

    switch ($type) {
        case 'DNI':
            return ($length === 8 && ctype_digit($cleanNumber));
        case 'PAS':
            return ($length >= 9 && $length <= 15);
        case 'CE':
            return ($length >= 9 && $length <= 12);
        default:
            return false;
    }
}
?>