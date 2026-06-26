<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');
require_once(__DIR__ . '/Upload.php');

class PromoBE {
    public $idPromo;
    public $idTrip;
    public $titleOffer;
    public $description;
    public $imageBanner;
    public $actionLink;
    public $specialPriceUSD;
    public $startDate;
    public $expirationDate;
    public $isActive;

    public function __construct(
        $idP = null,
        $idT = null,
        $title = null,
        $desc = null,
        $img = null,
        $link = null,
        $price = null,
        $start = null,
        $exp = null,
        $active = 1
    ) {
        $this->idPromo = $idP;
        $this->idTrip = $idT;
        $this->titleOffer = $title;
        $this->description = $desc;
        $this->imageBanner = $img;
        $this->actionLink = $link;
        $this->specialPriceUSD = $price;
        $this->startDate = $start;
        $this->expirationDate = $exp;
        $this->isActive = $active;
    }
}

class MarketingPromo {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function getPromoById($idPromo) {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "SELECT * 
                    FROM marketing_promos 
                    WHERE Id_Promo = :idPromo";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':idPromo', $idPromo);
            $stmt->execute();

            return $response->responseMessageArray(
                true,
                "",
                $stmt->fetch(PDO::FETCH_ASSOC)
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function createPromo(PromoBE $promo) {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "INSERT INTO marketing_promos (
                        Id_Trip,
                        Title_Offer,
                        Description,
                        Image_Banner,
                        Action_Link,
                        Special_Price_USD,
                        Start_Date,
                        Expiration_Date,
                        Is_Active
                    ) VALUES (
                        :idTrip,
                        :title,
                        :description,
                        :imageBanner,
                        :actionLink,
                        :price,
                        :startDate,
                        :expirationDate,
                        :isActive
                    )";

            $stmt = $this->connection->prepare($sql);

            $success = $stmt->execute([
                ':idTrip' => $promo->idTrip,
                ':title' => $promo->titleOffer,
                ':description' => $promo->description,
                ':imageBanner' => $promo->imageBanner,
                ':actionLink' => $promo->actionLink,
                ':price' => $promo->specialPriceUSD,
                ':startDate' => $promo->startDate,
                ':expirationDate' => $promo->expirationDate,
                ':isActive' => $promo->isActive
            ]);

            return $response->responseMessageArray(
                $success,
                "",
                ['id' => intval($this->connection->lastInsertId())]
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updatePromo(PromoBE $promo, $deleteOldFile = false, $oldUrl = "") {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "UPDATE marketing_promos SET
                        Id_Trip = :idTrip,
                        Title_Offer = :title,
                        Description = :description,
                        Image_Banner = :imageBanner,
                        Action_Link = :actionLink,
                        Special_Price_USD = :price,
                        Start_Date = :startDate,
                        Expiration_Date = :expirationDate,
                        Is_Active = :isActive
                    WHERE Id_Promo = :idPromo";

            $stmt = $this->connection->prepare($sql);

            $success = $stmt->execute([
                ':idTrip' => $promo->idTrip,
                ':title' => $promo->titleOffer,
                ':description' => $promo->description,
                ':imageBanner' => $promo->imageBanner,
                ':actionLink' => $promo->actionLink,
                ':price' => $promo->specialPriceUSD,
                ':startDate' => $promo->startDate,
                ':expirationDate' => $promo->expirationDate,
                ':isActive' => $promo->isActive,
                ':idPromo' => $promo->idPromo
            ]);

            if ($success && $deleteOldFile && !empty($oldUrl)) {
                $upload = new Upload();
                $upload->deleteImage($oldUrl);
            }

            return $response->responseMessageArray(
                $success,
                "",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function togglePromoStatus($idPromo, $isActive) {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "UPDATE marketing_promos
                    SET Is_Active = :isActive
                    WHERE Id_Promo = :idPromo";

            $stmt = $this->connection->prepare($sql);

            $success = $stmt->execute([
                ':isActive' => $isActive,
                ':idPromo' => $idPromo
            ]);

            return $response->responseMessageArray(
                $success,
                "",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deletePromo($idPromo) {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $promo = $this->getPromoById($idPromo);

            if (!empty($promo['data']['Image_Banner'])) {
                $upload = new Upload();
                $upload->deleteImage($promo['data']['Image_Banner']);
            }

            $sql = "DELETE FROM marketing_promos
                    WHERE Id_Promo = :idPromo";

            $stmt = $this->connection->prepare($sql);

            return $response->responseMessageArray(
                $stmt->execute([':idPromo' => $idPromo]),
                "",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPromotionsWeb() {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "SELECT 
                        p.*,
                        t.Destination_Name,
                        t.Ship_Name,
                        t.Cruise_Line,
                        t.Departure_Port,
                        t.Price AS Original_Price,
                        t.Start_Date,
                        t.End_Date,
                        t.Requires_Visa,
                        t.Includes_Flight,
                        t.Duration_Nights
                    FROM marketing_promos p
                    INNER JOIN trips t
                        ON p.Id_Trip = t.Id_Trip
                    WHERE (p.Start_Date <= CURDATE() OR p.Start_Date IS NULL)
                    AND p.Expiration_Date >= CURDATE()
                    AND p.Is_Active = 1
                    ORDER BY p.Expiration_Date ASC
                    LIMIT 6";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($promos as &$promo) {
                $promo['Start_Date_Format'] = $this->formatDateSpanish($promo['Start_Date']);
                $promo['End_Date_Format'] = $this->formatDateSpanish($promo['End_Date']);

                if (empty($promo['Duration_Nights'])) {
                    $start = new DateTime($promo['Start_Date']);
                    $end = new DateTime($promo['End_Date']);
                    $promo['Nights'] = $start->diff($end)->days;
                } else {
                    $promo['Nights'] = $promo['Duration_Nights'];
                }

                $promo['Requires_Visa'] = (bool)$promo['Requires_Visa'];
                $promo['Includes_Flight'] = (bool)$promo['Includes_Flight'];
            }

            return $response->responseMessageArray(
                true,
                "",
                $promos
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getAllPromosAdmin() {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "SELECT
                        p.*,
                        t.Destination_Name,
                        t.Ship_Name
                    FROM marketing_promos p
                    INNER JOIN trips t
                        ON p.Id_Trip = t.Id_Trip
                    ORDER BY p.Id_Promo DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            return $response->responseMessageArray(
                true,
                "",
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPromotionsApp() {
        $response = new Response();

        if (!$this->connection) {
            return $response->structMessageErrorService();
        }

        try {
            $sql = "SELECT
                        p.*,
                        t.Destination_Name,
                        t.Ship_Name
                    FROM marketing_promos p
                    INNER JOIN trips t
                        ON p.Id_Trip = t.Id_Trip
                    WHERE (p.Start_Date <= CURDATE() OR p.Start_Date IS NULL)
                    AND (p.Expiration_Date >= CURDATE() OR p.Expiration_Date IS NULL)
                    AND p.Is_Active = 1
                    ORDER BY p.Expiration_Date ASC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            return $response->responseMessageArray(
                true,
                "",
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPromotionChatIA() {
        try {
            $sql = "SELECT
                        p.Title_Offer,
                        p.Special_Price_USD,
                        p.Expiration_Date,
                        p.Image_Banner,
                        t.Id_Trip,
                        t.Destination_Name,
                        t.Start_Date,
                        t.Ship_Name,
                        t.Price AS Normal_Price,
                        i.Day_Number,
                        i.Activity_Description
                    FROM marketing_promos p
                    INNER JOIN trips t
                        ON p.Id_Trip = t.Id_Trip
                    LEFT JOIN daily_itinerary i
                        ON t.Id_Trip = i.Id_Trip
                    WHERE p.Expiration_Date >= CURDATE()
                    AND p.Is_Active = 1
                    ORDER BY t.Id_Trip, i.Day_Number";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function formatDateSpanish($date) {
        if (!$date) {
            return "";
        }

        $timestamp = strtotime($date);

        $meses = [
            "Ene", "Feb", "Mar", "Abr",
            "May", "Jun", "Jul", "Ago",
            "Sep", "Oct", "Nov", "Dic"
        ];

        $dia = date("d", $timestamp);
        $mes = $meses[date("n", $timestamp) - 1];

        return "$dia de $mes";
    }
}
?>