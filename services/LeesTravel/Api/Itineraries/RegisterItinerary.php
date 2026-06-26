<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Itinerary.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );
    $idTrip = $data["idTrip"] ?? null;
    $dayNum = $data["dayNumber"] ?? null;
    $port   = $data["portOfCall"] ?? null;
    $desc   = $data["activityDescription"] ?? null;
    $arr    = $data["arrivalTime"] ?? null;
    $dep    = $data["departureTime"] ?? null;
    $params = [
        $idTrip,
        $dayNum,
        $port
    ];
    if (validationParametros($params)) {
        if (
            is_numeric($idTrip) &&
            is_numeric($dayNum)
        ) {
            $itinerarioManager = new Itinerario();
            $be = new ItinerarioBE(
                null,
                $idTrip,
                $dayNum,
                $port,
                $desc,
                $arr,
                $dep
            );
            echo $response->responseSuccessValidation(
                $itinerarioManager
                    ->createActivity($be)
            );
        } else {
            echo $response
                ->responseErrorMessage(
                    "Datos inválidos."
                );
        }
    } else {
        echo $response
            ->responseErrorMessage(
                "Faltan campos obligatorios."
            );
    }
} else {

    echo $response->responseError();
}
function validationParametros($parametros) {

    foreach ($parametros as $param) {

        if (
            !isset($param) ||
            trim($param) === ""
        ) {
            return false;
        }
    }

    return true;
}
?>