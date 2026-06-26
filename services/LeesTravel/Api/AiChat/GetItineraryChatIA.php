<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Itinerary.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $itineraryManager = new Itinerario();

    echo $response->responseSuccessValidation(
        $itineraryManager->getItineraryChatIA()
    );

} else {
    echo $response->responseError();
}
?>