<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Blog.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $blogManager = new Blog();

    echo $response->responseSuccessValidation($blogManager->getAllPublicPostsApp()); 

} else {
    echo $response->responseError();
}
?>