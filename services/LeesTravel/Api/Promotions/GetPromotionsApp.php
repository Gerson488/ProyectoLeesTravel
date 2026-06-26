<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Promotion.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $promoManager = new MarketingPromo();
    echo $response->responseSuccessValidation($promoManager->getPromotionsApp());
} else {
    echo $response->responseError();
}
?>