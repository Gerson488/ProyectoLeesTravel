<?php
require_once __DIR__ . '/../Config/Cors.php';
require_once __DIR__ . '/../../Response/Response.php';
require_once __DIR__ . '/../../Core/Dashboard.php';

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {

        $dashboard = new Dashboard();
        $metrics = $dashboard->getAppMetrics();

        echo json_encode($metrics);

    } catch (Exception $e) {

        echo json_encode(
            $response->structMessageErrorCustom(
                $e->getMessage()
            )
        );
    }

} else {

    echo json_encode(
        $response->structMessageErrorService()
    );
}
?>