<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class BookingBE {
    public $idBooking;
    public $idPurchaserUser;
    public $bookingDate;
    public $bookingStatus;

    public function __construct($idB = null, $idU = null, $date = null, $status = 'Confirmada') {
        $this->idBooking = $idB;
        $this->idPurchaserUser = $idU;
        $this->bookingDate = $date;
        $this->bookingStatus = $status;
    }
}

class Booking {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function getIdByUserDni($dni) {
        if (!$this->connection) return null;
        try {
            $sql = "SELECT u.Id_User 
                    FROM system_users u
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE t.Id_Card_Passport = :dni LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':dni' => $dni]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['Id_User'] : null;
        } catch (PDOException $e) {
            error_log("Error en getIdByUserDni: " . $e->getMessage());
            return null;
        }
    }
    public function createBooking(BookingBE $b) {
        if (!$this->connection) return false;
        try {
            $sql = "INSERT INTO bookings (Id_Purchaser_User, Booking_Status) VALUES (:idU, :status)";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idU'    => $b->idPurchaserUser,
                ':status' => $b->bookingStatus
            ]);
            
            return $success ? intval($this->connection->lastInsertId()) : false;
        } catch (PDOException $e) {
            error_log("Error en createBooking: " . $e->getMessage());
            return false;
        }
    }
    public function registerPassengerInBooking($idTraveler, $idBooking, $idTrip = null) {
        if (!$this->connection) return false;
        try {
            if (!$idTrip) {
                $sqlFirstTrip = "SELECT Id_Trip FROM trips LIMIT 1";
                $stmtFirstTrip = $this->connection->query($sqlFirstTrip);
                $firstTripRow = $stmtFirstTrip->fetch(PDO::FETCH_ASSOC);
                $idTrip = $firstTripRow ? $firstTripRow['Id_Trip'] : 1;
            }

            $sql = "INSERT INTO passenger (Id_Traveler, Id_Booking, Id_Trip) VALUES (:idT, :idB, :idTrip)";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':idT'    => $idTraveler,
                ':idB'    => $idBooking,
                ':idTrip' => $idTrip
            ]);
        } catch (PDOException $e) {
            error_log("Error en registerPassengerInBooking: " . $e->getMessage());
            return false;
        }
    }
    public function updateBookingStatus($idBooking, $status, $passengers = null) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $hasGroupData = is_array($passengers);
            
            if ($hasGroupData) {
                $this->connection->beginTransaction();
            }
            $sql = "UPDATE bookings SET Booking_Status = :status WHERE Id_Booking = :idB";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([':status' => $status, ':idB' => $idBooking]);

            if ($success && $hasGroupData) {
                $sqlTrip = "SELECT Id_Trip FROM passenger WHERE Id_Booking = :idB LIMIT 1";
                $stmtTrip = $this->connection->prepare($sqlTrip);
                $stmtTrip->execute([':idB' => $idBooking]);
                $tripRow = $stmtTrip->fetch(PDO::FETCH_ASSOC);
                $idTrip = $tripRow ? $tripRow['Id_Trip'] : null;
                if (!$idTrip) {
                    $sqlFirstTrip = "SELECT Id_Trip FROM trips LIMIT 1";
                    $stmtFirstTrip = $this->connection->query($sqlFirstTrip);
                    $firstTripRow = $stmtFirstTrip->fetch(PDO::FETCH_ASSOC);
                    $idTrip = $firstTripRow ? $firstTripRow['Id_Trip'] : 1;
                }
                $sqlDelete = "DELETE FROM passenger WHERE Id_Booking = :idB";
                $stmtDelete = $this->connection->prepare($sqlDelete);
                $stmtDelete->execute([':idB' => $idBooking]);
                $sqlInsert = "INSERT INTO passenger (Id_Booking, Id_Traveler, Id_Trip) VALUES (:idB, :idT, :idTrip)";
                $stmtInsert = $this->connection->prepare($sqlInsert);

                foreach ($passengers as $passenger) {
                    $idTraveler = $passenger['idTraveler'] ?? $passenger['Id_Traveler'] ?? null;
                    if (!empty($idTraveler)) {
                        $stmtInsert->execute([
                            ':idB'    => $idBooking,
                            ':idT'    => $idTraveler,
                            ':idTrip' => $idTrip
                        ]);
                    }
                }
            }

            if ($hasGroupData) {
                $this->connection->commit();
            }
            
            return $response->responseMessageArray($success, "Estado de reserva y acompañantes actualizados", null);
        } catch (PDOException $e) {
            if (is_array($passengers) && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deleteBooking($idBooking) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "DELETE FROM bookings WHERE Id_Booking = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idBooking);
            
            return $response->responseMessageArray($stmt->execute(), "Reserva eliminada correctamente", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getBookingsByUser($idUser) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT b.*, t.Id_Card_Passport AS User_Dni,
                           CONCAT(t.First_Name, ' ', t.Last_Name) AS Full_Name
                    FROM bookings b
                    INNER JOIN system_users u ON b.Id_Purchaser_User = u.Id_User
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE b.Id_Purchaser_User = :id 
                    ORDER BY b.Booking_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idUser);
            $stmt->execute();
            
            return $response->responseMessageArray(true, "Reservas obtenidas", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getAllBookings() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT b.*, t.Id_Card_Passport AS User_Dni,
                           CONCAT(t.First_Name, ' ', t.Last_Name) AS Full_Name
                    FROM bookings b
                    INNER JOIN system_users u ON b.Id_Purchaser_User = u.Id_User
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    ORDER BY b.Booking_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $response->responseMessageArray(true, "Listado completo de reservas obtenido", $data);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPassengersByBooking($idBooking) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT p.Id_Passenger, t.Id_Traveler, t.Id_Card_Passport AS User_Dni, p.Id_Trip,
                           CONCAT(t.First_Name, ' ', t.Last_Name) AS Full_Name
                    FROM passenger p
                    INNER JOIN travelers t ON p.Id_Traveler = t.Id_Traveler
                    WHERE p.Id_Booking = :idBooking";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':idBooking' => $idBooking]);
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $response->responseMessageArray(true, "Pasajeros de la reserva obtenidos con éxito", $data);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
} 
?>