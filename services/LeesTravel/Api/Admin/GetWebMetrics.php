<?php
require_once __DIR__ . '/../Config/Cors.php';
require_once __DIR__ . '/../../Response/Response.php';
require_once __DIR__ . '/../../Core/Dashboard.php';

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {

        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $dashboard = new Dashboard();

        $metricsResponse = $dashboard->getWebMetrics();
        $chartsResponse = $dashboard->getQuoteCharts($startDate, $endDate);

        $result = [
            "metrics" => $metricsResponse['data'] ?? [],
            "charts" => $chartsResponse['data'] ?? []
        ];

        echo json_encode(
            $response->responseMessageArray(
                true,
                "Dashboard WEB obtenido correctamente",
                $result
            )
        );

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