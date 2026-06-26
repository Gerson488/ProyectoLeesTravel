<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $pubManager = new Publication();
    echo $response->responseSuccessValidation($pubManager->getAllPublicationsAdmin());
} else {
    echo $response->responseError();
}
?>