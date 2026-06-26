<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Promotion.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $imageUrl = $_POST["oldImageBanner"] ?? "";
    $deleteOldFile = false;

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
            $imageUrl = $uploadResult['data']['url'];
            $deleteOldFile = true;
        } else {
            echo $response->responseSuccessValidation(
                $uploadResult
            );
            exit;
        }
    }

    $promo = new PromoBE(
        $_POST["idPromo"] ?? null,
        $_POST["idTrip"] ?? null,
        $_POST["titleOffer"] ?? null,
        $_POST["description"] ?? "",
        $imageUrl,
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
        $promoManager->updatePromo(
            $promo,
            $deleteOldFile,
            $_POST["oldImageBanner"] ?? ""
        )
    );

} else {
    echo $response->responseError();
}
?>