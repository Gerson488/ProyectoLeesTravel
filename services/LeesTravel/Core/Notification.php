<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class NotificationBE {
    public $idNotification;
    public $idUser;
    public $title;
    public $message;
    public $isRead;
    public $createdAt;

    public function __construct($idN=null, $idU=null, $title=null, $msg=null, $read=0, $date=null) {
        $this->idNotification = $idN;
        $this->idUser = $idU;
        $this->title = $title;
        $this->message = $msg;
        $this->isRead = $read;
        $this->createdAt = $date;
    }
}

class Notification {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function createNotification(NotificationBE $n) {
        $response = new Response();
        try {
            $sql = "INSERT INTO notifications (Id_User, Title, Message) VALUES (:idU, :title, :msg)";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idU'   => $n->idUser,
                ':title' => $n->title,
                ':msg'   => $n->message
            ]);
            return $response->responseMessageArray($success, "Notificación enviada", ["id" => $this->connection->lastInsertId()]);
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }

    public function getNotificationsByUser($idUser) {
        $response = new Response();
        try {
            $sql = "SELECT * FROM notifications WHERE Id_User = :id ORDER BY Created_At DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $idUser]);
            return $response->responseMessageArray(true, "Lista de notificaciones", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }

    public function markAsRead($idNotification) {
        $response = new Response();
        try {
            $sql = "UPDATE notifications SET Is_Read = 1 WHERE Id_Notification = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([':id' => $idNotification]);
            return $response->responseMessageArray($success, "Notificación leída", null);
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }
    public function countUnread($idUser) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT COUNT(*) as unread FROM notifications WHERE Id_User = :id AND Is_Read = 0";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $idUser]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $response->responseMessageArray(true, "Conteo de no leídas obtenido", $result);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>