<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Response/Response.php');
require_once(__DIR__ . '/../../Core/Quote.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode($response->structMessageErrorCustom("Datos de solicitud inválidos."));
        exit();
    }

    $quoteBE = new QuoteBE(
        $data['fullName'] ?? null,
        $data['email'] ?? null,
        $data['countryCode'] ?? null,
        $data['phone'] ?? null,
        $data['destination'] ?? null,
        $data['date'] ?? null,
        $data['passengers'] ?? null,
        $data['cabinType'] ?? null,
        $data['comments'] ?? null
    );

    $quote = new Quote();
    
    $quote->saveQuoteToDB($quoteBE);
    
    $resultado = $quote->sendQuoteEmail($quoteBE);

    header('Content-Type: application/json');
    echo $resultado; 
} else {
    echo json_encode($response->responseError());
}
?>