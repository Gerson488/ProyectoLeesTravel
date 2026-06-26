<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $idPost = $_POST["idPost"] ?? null; 
    $idTrip = $_POST["idTrip"] ?? null;
    $idUser = $_POST["idUser"] ?? null;
    $title  = $_POST["title"] ?? "Sin título";
    $desc   = $_POST["description"] ?? "";
    $lat    = $_POST["latitude"] ?? null;
    $lng    = $_POST["longitude"] ?? null;

    if (!$idTrip || !$idUser) {
        echo $response->responseErrorMessage("Faltan IDs de viaje o usuario obligatorios.");
        exit;
    }

    $imageUrls = [];
    $errors = [];

    if (isset($_FILES['image']['name']) && is_array($_FILES['image']['name'])) {
        $uploader = new Upload();
        $files = $_FILES['image'];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue; 
            }

            $currentFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i]
            ];

            if ($currentFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $uploader->saveImage($currentFile, 'Publication');
                
                if ($uploadResult['status'] == 200) {
                    $imageUrls[] = $uploadResult['data']['url'];
                } else {
                    $errors[] = $currentFile['name'] . ": " . $uploadResult['message'];
                }
            } else {
                $errors[] = "Error de subida en: " . $currentFile['name'];
            }
        }
    }

    $publicationManager = new Publication();
    $be = new PublicationBE(
        $idPost, 
        $idTrip, 
        $idUser, 
        $title, 
        $desc, 
        $lat, 
        $lng, 
        $imageUrls
    );
    
    $result = $publicationManager->registerPublication($be);
    
    if ($result['status'] == 200 && !empty($errors)) {
        $result['warnings'] = $errors;
    }
    
    echo json_encode($result);

} else { 
    echo $response->responseError(); 
}
?>