<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class Dashboard {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }
    public function getWebMetrics() {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {

            $sqlPendingQuotes = "
                SELECT COUNT(*) as total
                FROM quotes
                WHERE Status = 'Pendiente'
            ";
            $stmtPendingQuotes = $this->connection->query($sqlPendingQuotes);
            $pendingQuotes = $stmtPendingQuotes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlPromos = "
                SELECT COUNT(*) as total
                FROM marketing_promos
                WHERE Expiration_Date >= CURDATE()
            ";
            $stmtPromos = $this->connection->query($sqlPromos);
            $activePromotions = $stmtPromos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlItinerary = "
                SELECT COUNT(*) as total
                FROM daily_itinerary
            ";
            $stmtItinerary = $this->connection->query($sqlItinerary);
            $dailyItineraries = $stmtItinerary->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlAttended = "
                SELECT COUNT(*) as total
                FROM quotes
                WHERE Status = 'Atendido'
            ";
            $stmtAttended = $this->connection->query($sqlAttended);
            $attendedLeads = $stmtAttended->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $metrics = [
                "pending_quotes"    => (int)$pendingQuotes,
                "active_promotions" => (int)$activePromotions,
                "daily_itineraries" => (int)$dailyItineraries,
                "attended_leads"    => (int)$attendedLeads,
                "server_time"       => date("d-m-Y H:i:s")
            ];

            return $response->responseMessageArray(
                true,
                "Métricas WEB obtenidas correctamente",
                $metrics
            );

        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getAppMetrics() {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {

            $sqlPendingPosts = "
                SELECT COUNT(*) as total
                FROM blog_post
                WHERE Moderation_Status = 'Pendiente'
                AND Is_Deleted = 0
            ";
            $stmtPendingPosts = $this->connection->query($sqlPendingPosts);
            $pendingPosts = $stmtPendingPosts->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlPassengers = "
                SELECT COUNT(*) as total
                FROM system_users
                WHERE Access_Role = 'Pasajero'
            ";
            $stmtPassengers = $this->connection->query($sqlPassengers);
            $totalPassengers = $stmtPassengers->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;


            $healthAlerts = 0;

            try {
                $sqlAlerts = "
                    SELECT COUNT(*) as total
                    FROM medical_records
                    WHERE Observations LIKE '%crítico%'
                ";

                $stmtAlerts = $this->connection->query($sqlAlerts);
                $healthAlerts = $stmtAlerts->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            } catch (Exception $e) {
                $healthAlerts = 0;
            }


            $sqlBookings = "
                SELECT COUNT(*) as total
                FROM bookings
            ";
            $stmtBookings = $this->connection->query($sqlBookings);
            $totalBookings = $stmtBookings->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $metrics = [
                "pending_posts"          => (int)$pendingPosts,
                "total_passengers"       => (int)$totalPassengers,
                "critical_health_alerts" => (int)$healthAlerts,
                "total_bookings"         => (int)$totalBookings,
                "server_time"            => date("d-m-Y H:i:s")
            ];

            return $response->responseMessageArray(
                true,
                "Métricas APP obtenidas correctamente",
                $metrics
            );

        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getQuoteCharts($startDate, $endDate) {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {

            $sqlDest = "
                SELECT Destination as label, COUNT(*) as qty
                FROM quotes
                WHERE DATE(Created_At) BETWEEN :start AND :end
                GROUP BY Destination
                ORDER BY qty DESC
                LIMIT 5
            ";

            $stmtDest = $this->connection->prepare($sqlDest);
            $stmtDest->execute([
                ':start' => $startDate,
                ':end'   => $endDate
            ]);

            $destData = $stmtDest->fetchAll(PDO::FETCH_ASSOC);

            $sqlStatus = "
                SELECT Status as label, COUNT(*) as qty
                FROM quotes
                WHERE DATE(Created_At) BETWEEN :start AND :end
                GROUP BY Status
            ";

            $stmtStatus = $this->connection->prepare($sqlStatus);
            $stmtStatus->execute([
                ':start' => $startDate,
                ':end'   => $endDate
            ]);

            $statusData = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

            $sqlMonth = "
                SELECT DATE_FORMAT(Travel_Date, '%Y-%m') as label,
                       COUNT(*) as qty
                FROM quotes
                WHERE DATE(Created_At) BETWEEN :start AND :end
                AND Travel_Date IS NOT NULL
                GROUP BY DATE_FORMAT(Travel_Date, '%Y-%m')
                ORDER BY label ASC
                LIMIT 12
            ";

            $stmtMonth = $this->connection->prepare($sqlMonth);
            $stmtMonth->execute([
                ':start' => $startDate,
                ':end'   => $endDate
            ]);

            $monthData = $stmtMonth->fetchAll(PDO::FETCH_ASSOC);

            $charts = [
                "period"           => [
                    "start" => $startDate,
                    "end"   => $endDate
                ],
                "top_destinations" => $destData,
                "quote_status"     => $statusData,
                "monthly_demand"   => $monthData
            ];

            return $response->responseMessageArray(
                true,
                "Métricas filtradas obtenidas",
                $charts
            );

        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>