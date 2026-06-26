<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Quote.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $quoteManager = new Quote();
    echo $response->responseSuccessValidation($quoteManager->getAllQuotes());
} else {
    echo $response->responseError();
}
?>