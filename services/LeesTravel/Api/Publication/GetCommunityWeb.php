<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Publication.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $pubManager = new Publication();
    $result = $pubManager->getPublicationsWeb();

    echo $response->responseSuccessValidation($result); 

} else {
    echo $response->responseError();
}
?>