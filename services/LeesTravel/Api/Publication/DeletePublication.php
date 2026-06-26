<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $idP = $_GET["idPost"] ?? null;
    $idU = $_GET["idUser"] ?? null;

    if ($idP && $idU) {
        $pubManager = new Publication();
        $res = $pubManager->deletePublication($idP, $idU);

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

    } else {
        echo $response->responseErrorMessage("Faltan parámetros idPost o idUser en la URL.");
    }
} else { 
    echo $response->responseError(); 
}
?>