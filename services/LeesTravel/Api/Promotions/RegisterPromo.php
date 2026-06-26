<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Promotion.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        isset($_FILES['imageBanner']) &&
        $_FILES['imageBanner']['error'] === UPLOAD_ERR_OK
    ) {

        $upload = new Upload();
        $uploadResult = $upload->saveImage(
            $_FILES['imageBanner'],
            'Promos'
        );

        if ($uploadResult['status'] == 200) {

            $promo = new PromoBE(
                null,
                $_POST["idTrip"] ?? null,
                $_POST["titleOffer"] ?? null,
                $_POST["description"] ?? "",
                $uploadResult['data']['url'],
                $_POST["actionLink"] ?? "",
                $_POST["specialPrice"] ?? 0,
                $_POST["startDate"] ?? null,
                $_POST["expirationDate"] ?? null,
                isset($_POST["isActive"])
                    ? (int)$_POST["isActive"]
                    : 1
            );

            $promoManager = new MarketingPromo();

            echo $response->responseSuccessValidation(
                $promoManager->createPromo($promo)
            );

        } else {
            echo $response->responseSuccessValidation(
                $uploadResult
            );
        }

    } else {
        echo $response->responseError();
    }

} else {
    echo $response->responseError();
}
?>