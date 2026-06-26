<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Trips.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $tripsManager = new Trips();
    echo $response->responseSuccessValidation($tripsManager->getTripsWeb());
} else {
    echo $response->responseError();
}
?>