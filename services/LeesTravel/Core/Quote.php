<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class QuoteBE {
    public $fullName;
    public $email;
    public $countryCode;
    public $phone;
    public $destination;
    public $date;
    public $passengers;
    public $cabinType;
    public $comments;

    public function __construct(
        $fullName = null, $email = null, $countryCode = null, $phone = null, 
        $destination = null, $date = null, $passengers = null, $cabinType = null, $comments = null
    ) {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->countryCode = $countryCode;
        $this->phone = $phone;
        $this->destination = $destination;
        $this->date = $date;
        $this->passengers = $passengers;
        $this->cabinType = $cabinType;
        $this->comments = $comments;
    }
}

class Quote {
    private $connection;

    public function __construct() {

        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }


    public function saveQuoteToDB(QuoteBE $quote) {
        if (!$this->connection) return false;

        try {
            $sql = "INSERT INTO quotes (Full_Name, Email, Phone, Destination, Travel_Date, Passengers, Cabin_Type, Comments) 
                    VALUES (:name, :email, :phone, :dest, :date, :pass, :cabin, :comments)";
            $stmt = $this->connection->prepare($sql);
            
            $fullPhone = $quote->countryCode . " " . $quote->phone;

            $stmt->bindParam(':name', $quote->fullName);
            $stmt->bindParam(':email', $quote->email);
            $stmt->bindParam(':phone', $fullPhone);
            $stmt->bindParam(':dest', $quote->destination);
            $stmt->bindParam(':date', $quote->date);
            $stmt->bindParam(':pass', $quote->passengers);
            $stmt->bindParam(':cabin', $quote->cabinType);
            $stmt->bindParam(':comments', $quote->comments);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al guardar la cotización en BD: " . $e->getMessage());
            return false;
        }
    }

    public function sendQuoteEmail(QuoteBE $quote) {
        $response = new Response();
        $mail = new PHPMailer(true);

        $safeName = htmlspecialchars(strip_tags($quote->fullName), ENT_QUOTES, 'UTF-8');
        $safeEmail = filter_var($quote->email, FILTER_SANITIZE_EMAIL);
        $safeDestination = htmlspecialchars(strip_tags($quote->destination), ENT_QUOTES, 'UTF-8');
        $safeComments = htmlspecialchars(strip_tags($quote->comments), ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars(strip_tags($quote->phone), ENT_QUOTES, 'UTF-8');
        $safeCountryCode = htmlspecialchars(strip_tags($quote->countryCode), ENT_QUOTES, 'UTF-8');

        try {
            $templatePath = __DIR__ . '/Templates/QuoteEmail.html';
            if (!file_exists($templatePath)) {
                return $response->structMessageErrorCustom("Error interno: Plantilla no encontrada.");
            }

            $message = file_get_contents($templatePath);
            
            $message = str_replace('{{name}}', $safeName, $message);
            $message = str_replace('{{email}}', $safeEmail, $message);
            $message = str_replace('{{phone}}', $safeCountryCode . " " . $safePhone, $message);
            $message = str_replace('{{destination}}', $safeDestination, $message);
            $message = str_replace('{{date}}', htmlspecialchars($quote->date), $message);
            $message = str_replace('{{passengers}}', htmlspecialchars($quote->passengers), $message);
            $message = str_replace('{{cabinType}}', htmlspecialchars($quote->cabinType), $message);
            $message = str_replace('{{comments}}', $safeComments, $message);

            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] == 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addReplyTo($safeEmail, $safeName);
            $mail->addAddress($_ENV['MAIL_RECEIVER']);
            $mail->isHTML(true);
            $mail->Subject = "Nueva Cotización: " . $safeDestination;
            $mail->Body    = $message;

            $mail->send();
            return $response->responseMessageSuccess("¡Solicitud enviada! Pronto contactaremos a " . $safeName);

        } catch (Exception $e) {
            error_log($mail->ErrorInfo); 
            return $response->structMessageErrorCustom("Hubo un problema al procesar el envío.");
        }
    }
    public function getAllQuotes() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT * FROM quotes ORDER BY Created_At DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $response->responseMessageArray(true, "Cotizaciones obtenidas", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPendingCount() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT COUNT(*) as count FROM quotes WHERE Status = 'Pendiente'";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->responseMessageArray(true, "Conteo obtenido", ["count" => (int)$result['count']]);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updateQuoteStatus($idQuote, $status) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "UPDATE quotes SET Status = :status WHERE Id_Quote = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':status' => $status,
                ':id' => $idQuote
            ]);
            return $response->responseMessageArray($success, "Estado actualizado correctamente", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>