<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Travelers.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $travelerManager = new Traveler();
    echo $response->responseSuccessValidation($travelerManager->getAllTravelers());
} else {
    echo $response->responseError();
}
?>