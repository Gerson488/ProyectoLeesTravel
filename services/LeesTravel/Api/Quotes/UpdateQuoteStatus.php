<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Quote.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $idQuote = $data["idQuote"] ?? null;
    $status = $data["status"] ?? null;

    if ($idQuote && is_numeric($idQuote) && in_array($status, ['Pendiente', 'Atendido'])) {
        $quoteManager = new Quote();
        echo $response->responseSuccessValidation($quoteManager->updateQuoteStatus($idQuote, $status));
    } else {
        echo $response->responseErrorMessage("Faltan datos obligatorios o el estado no es válido.");
    }
} else {
    echo $response->responseError();
}
?>