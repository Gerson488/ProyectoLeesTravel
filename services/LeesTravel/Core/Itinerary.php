<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class ItinerarioBE {

    public $idItinerary;
    public $idTrip;
    public $dayNumber;
    public $portOfCall;
    public $activityDescription;
    public $arrivalTime;
    public $departureTime;

    public function __construct(
        $idI = null,
        $idT = null,
        $day = null,
        $port = null,
        $desc = null,
        $arr = null,
        $dep = null
    ) {

        $this->idItinerary = $idI;
        $this->idTrip = $idT;
        $this->dayNumber = $day;
        $this->portOfCall = $port;
        $this->activityDescription = $desc;
        $this->arrivalTime = $arr;
        $this->departureTime = $dep;
    }
}

class Itinerario {
    private $connection;
    public function __construct() {

        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function createActivity(ItinerarioBE $i) {
        $response = new Response();
        if (!$this->connection) {
            return $response->structMessageErrorService();
        }try {
            $sql = "INSERT INTO daily_itinerary (
                        Id_Trip,
                        Day_Number,
                        Port_of_Call,
                        Activity_Description,
                        Arrival_Time,
                        Departure_Time
                    )
                    VALUES (
                        :idTrip,
                        :dayNum,
                        :port,
                        :descr,
                        :arr,
                        :dep
                    )";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idTrip' => $i->idTrip,
                ':dayNum' => $i->dayNumber,
                ':port'   => $i->portOfCall,
                ':descr'  => $i->activityDescription,
                ':arr'    => $i->arrivalTime,
                ':dep'    => $i->departureTime
            ]);
            return $response->responseMessageArray(
                $success,
                "Actividad agregada al itinerario",
                [
                    "id" => intval($this->connection->lastInsertId()
                    )
                ]
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom(
                $e->getMessage()
            );
        }
    }
    public function updateActivity(ItinerarioBE $i) {
        $response = new Response();
        if (!$this->connection) {
            return $response->structMessageErrorService();
        }try {
            $sql = "UPDATE daily_itinerary SET
                        Day_Number = :dayNum,
                        Port_of_Call = :port,
                        Activity_Description = :descr,
                        Arrival_Time = :arr,
                        Departure_Time = :dep
                    WHERE Id_Itinerary = :idI";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':dayNum' => $i->dayNumber,
                ':port'   => $i->portOfCall,
                ':descr'  => $i->activityDescription,
                ':arr'    => $i->arrivalTime,
                ':dep'    => $i->departureTime,
                ':idI'    => $i->idItinerary
            ]);
            return $response->responseMessageArray(
                $success,
                "Actividad actualizada",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom( $e->getMessage()
            );
        }
    }

    public function deleteActivity($idItinerary) {
        $response = new Response();
        if (!$this->connection) {
            return $response->structMessageErrorService();
        }try {
            $sql = "DELETE FROM daily_itinerary
                    WHERE Id_Itinerary = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(
                ':id',
                $idItinerary
            );
            return $response->responseMessageArray(
                $stmt->execute(),
                "Actividad eliminada",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom(
                $e->getMessage()
            );
        }
    }

    public function getItineraryByTrip($idTrip) {
    $response = new Response();
    if (!$this->connection) {
        return $response->structMessageErrorService();
    }
    try {
        $sql = "SELECT 
                    Id_Itinerary AS Id_Itinerary,
                    Id_Trip AS Id_Trip,
                    Day_Number AS Day_Number,
                    Port_of_Call AS Port_of_Call,
                    Activity_Description AS Activity_Description,
                    Arrival_Time AS Arrival_Time,
                    Departure_Time AS Departure_Time
                FROM daily_itinerary
                WHERE Id_Trip = :id
                ORDER BY Day_Number ASC, Arrival_Time ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $idTrip);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->responseMessageArray(
            true,
            "Itinerario del crucero obtenido",
            $results
        );
    } catch (PDOException $e) {
        return $response->structMessageErrorCustom($e->getMessage());
    }
    }
    public function getItineraryChatIA() {
        try {
            $sql = "SELECT
                        t.Destination_Name,
                        i.Day_Number,
                        i.Activity_Description
                    FROM daily_itinerary i
                    INNER JOIN trips t
                        ON i.Id_Trip = t.Id_Trip
                    WHERE t.Start_Date >= CURDATE()
                    ORDER BY t.Id_Trip,
                             i.Day_Number";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>