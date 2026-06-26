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

    $idItinerary = $data["idItinerary"] ?? null;

    if (
        !empty($idItinerary) &&
        is_numeric($idItinerary)
    ) {

        $itinerarioManager = new Itinerario();

        echo $response->responseSuccessValidation(
            $itinerarioManager
                ->deleteActivity($idItinerary)
        );

    } else {

        echo $response->responseError();
    }

} else {

    echo $response->responseError();
}
?>