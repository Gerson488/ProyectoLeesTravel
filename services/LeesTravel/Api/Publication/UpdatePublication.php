<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idP = $_POST["idPost"] ?? null;
    $idU = $_POST["idUser"] ?? null;
    
    if ($idP && $idU) {
        $pubManager = new Publication();
        $current = $pubManager->getPublicationById($idP);
        
        if ($current) {
            if ($current['Id_User'] != $idU) {
                echo $response->responseErrorMessage("No tienes permiso para editar esta publicación.");
                exit;
            }

            $imageUrls = [];
            if (isset($_POST['retainedImages']) && !empty($_POST['retainedImages'])) {
                $decoded = json_decode($_POST['retainedImages'], true);
                if (is_array($decoded)) $imageUrls = $decoded;
            }

            if (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
                $uploader = new Upload();
                $files = $_FILES['image'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $fileData = [
                            'name'     => $files['name'][$i], 
                            'type'     => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i], 
                            'error'    => $files['error'][$i], 
                            'size'     => $files['size'][$i]
                        ];
                        
                        $resUpload = $uploader->saveImage($fileData, 'Publication');
                        
                        if ($resUpload['status'] == 200) {
                            $imageUrls[] = $resUpload['data']['url'];
                        }
                    }
                }
            }

            $be = new PublicationBE(
                $idP, 
                null, 
                $idU, 
                $_POST["title"] ?? $current['Title'], 
                $_POST["description"] ?? $current['Description'], 
                $_POST["latitude"] ?? $current['Latitude'], 
                $_POST["longitude"] ?? $current['Longitude'], 
                $imageUrls
            );
            
            $res = $pubManager->updatePublication($be);

            if ($res['status'] == 200) {
                $basePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR; 
                if (isset($res['data']['deleteFiles']) && is_array($res['data']['deleteFiles'])) {
                    foreach ($res['data']['deleteFiles'] as $file) {
                        $fullPath = $basePath . $file;
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                }
            }
            
            echo json_encode($res);

        } else { echo $response->responseErrorMessage("La publicación no existe."); }
    } else { echo $response->responseErrorMessage("ID de publicación y usuario requeridos."); }
} else { echo $response->responseError(); }