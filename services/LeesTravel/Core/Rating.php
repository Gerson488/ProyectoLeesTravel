<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class ReviewBE {
    public $idReview;
    public $idTrip;
    public $idUser;
    public $rating;
    public $comment;
    public $reviewDate;

    public function __construct($idR=null, $idT=null, $idU=null, $rating=null, $comment=null, $date=null) {
        $this->idReview = $idR;
        $this->idTrip = $idT;
        $this->idUser = $idU;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->reviewDate = $date;
    }
}

class Rating {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function createReview(ReviewBE $r) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "INSERT INTO trip_reviews (Id_Trip, Id_User, Rating, Comment) 
                    VALUES (:idT, :idU, :rating, :comment)";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idT'     => $r->idTrip,
                ':idU'     => $r->idUser,
                ':rating'  => $r->rating,
                ':comment' => $r->comment
            ]);
            return $response->responseMessageArray($success, "Reseña registrada. ¡Gracias por tu opinión!", ["id" => $this->connection->lastInsertId()]);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getReviewsByTrip($idTrip) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT r.*, t.First_Name, t.Last_Name 
                    FROM trip_reviews r 
                    INNER JOIN system_users u ON r.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE r.Id_Trip = :id ORDER BY r.Review_Date DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTrip);
            $stmt->execute();
            return $response->responseMessageArray(true, "Reseñas obtenidas", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getAverageRating($idTrip) {
        $response = new Response();
        try {
            $sql = "SELECT AVG(Rating) as average, COUNT(Id_Review) as total 
                    FROM trip_reviews WHERE Id_Trip = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTrip);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $result['average'] = $result['average'] ? round($result['average'], 1) : 0;
            return $response->responseMessageArray(true, "Cálculo de calificación finalizado", $result);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
    public function updateReview(ReviewBE $r) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "UPDATE trip_reviews SET Rating = :rating, Comment = :comment 
                    WHERE Id_Review = :idR AND Id_User = :idU";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':rating'  => $r->rating,
                ':comment' => $r->comment,
                ':idR'     => $r->idReview,
                ':idU'     => $r->idUser
            ]);
            
            return $response->responseMessageArray($success, "Reseña actualizada correctamente", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deleteReview($idReview, $idUser) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "DELETE FROM trip_reviews WHERE Id_Review = :idR AND Id_User = :idU";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idR' => $idReview,
                ':idU' => $idUser
            ]);
            
            return $response->responseMessageArray($success, "Reseña eliminada", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>