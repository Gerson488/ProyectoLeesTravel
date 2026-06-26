<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Promotion.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $promoManager = new MarketingPromo();

    echo $response->responseSuccessValidation(
        $promoManager->deletePromo(
            $data["idPromo"] ?? null
        )
    );

} else {
    echo $response->responseError();
}
?>