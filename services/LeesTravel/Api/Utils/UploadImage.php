<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php'); 

$response = new Response(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image'])) {
        $uploader = new Upload();
        $category = $_POST['category'] ?? 'general';
        
        echo json_encode($uploader->saveImage($_FILES['image'], $category));
    } else {
        echo $response->responseErrorMessage("No se detectó ningún archivo en la petición.");
    }
} else {
    echo $response->responseError(); 
}
?>