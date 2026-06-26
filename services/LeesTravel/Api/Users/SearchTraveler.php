<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Travelers.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $term = isset($data["term"]) ? trim($data["term"]) : null; 

    if ($term !== null && $term !== "") {
        $travelerManager = new Traveler();
        $res = null;
        if (is_numeric($term) && strlen($term) < 6) {
            $res = $travelerManager->getTravelerById(intval($term));
        }
        if (!$res || !isset($res['status']) || $res['status'] !== 200 || empty($res['data'])) {
            $res = $travelerManager->getTravelerByDocument($term);
        }
        if (!$res || empty($res['data'])) {
            echo json_encode([
                "status" => 202,
                "message" => "No se encontró ningún viajero con la identificación: " . $term,
                "data" => null
            ]);
        } else {
            echo json_encode($res);
        }

    } else {
        echo json_encode([
            "status" => 400, 
            "message" => "Debe ingresar un ID o DNI para realizar la búsqueda."
        ]);
    }
} else {
    echo $response->responseError();
}
?>