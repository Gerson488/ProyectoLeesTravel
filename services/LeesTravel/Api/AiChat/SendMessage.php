<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Response/Response.php');
require_once(__DIR__ . '/../../Core/AiChat.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['history']) && is_array($data['history'])) {

        try {

            $aiChat = new AiChat();
            $reply = $aiChat->getResponse($data['history']);

            echo $response->responseSuccess([
                "reply" => $reply
            ]);

        } catch (Exception $e) {

            echo $response->responseErrorMessage(
                $e->getMessage()
            );
        }

    } else {

        echo $response->responseError();
    }

} else {

    echo $response->responseError();
}
?>