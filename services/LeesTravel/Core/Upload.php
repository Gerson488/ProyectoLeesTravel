<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class Upload {

    private $targetDir;
    private $allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
    private $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxSize = 5242880;

    public function __construct() {
        $this->targetDir = dirname(__DIR__) . "/Imag/Photos/";
    }

    public function saveImage($file, $category = "") {

        $response = new Response();

        $folderPath = $category ? $category . "/" : "";
        $finalDir = $this->targetDir . $folderPath;

        if (!file_exists($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $fileExtension = strtolower(
            pathinfo($file["name"], PATHINFO_EXTENSION)
        );

        $newName = uniqid("IMG_", true) . "." . $fileExtension;
        $targetFile = $finalDir . $newName;

        if (!in_array($fileExtension, $this->allowedTypes)) {
            return $response->structMessageErrorCustom(
                "Formato denegado. Tipos permitidos: jpg, png, jpeg, gif, webp."
            );
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (!in_array($mimeType, $this->allowedMimes)) {
            return $response->structMessageErrorCustom(
                "El contenido del archivo no es una imagen válida."
            );
        }

        if ($file["size"] > $this->maxSize) {
            return $response->structMessageErrorCustom(
                "El archivo excede los 5MB permitidos."
            );
        }

        if (
            $this->compressAndSave(
                $file["tmp_name"],
                $targetFile,
                $mimeType,
                70
            )
        ) {

            $dbPath = "Imag/Photos/" .
                $folderPath .
                $newName;

            return $response->responseMessageArray(
                true,
                "Carga y compresión exitosa",
                ["url" => $dbPath]
            );
        }

        return $response->structMessageErrorCustom(
            "Error al procesar y comprimir la imagen."
        );
    }

    public function deleteImage($imagePath) {

        if (
            empty($imagePath) ||
            $imagePath === 'default_trip.png' ||
            str_contains($imagePath, 'default')
        ) {
            return false;
        }

        $fullPath = dirname(__DIR__) . "/" . $imagePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    private function compressAndSave(
        $source,
        $destination,
        $mimeType,
        $quality
    ) {

        $success = false;

        switch ($mimeType) {

            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                $success = imagejpeg(
                    $image,
                    $destination,
                    $quality
                );
                break;

            case 'image/png':
                $image = imagecreatefrompng($source);
                imagealphablending($image, false);
                imagesavealpha($image, true);

                $pngQuality = 9 - round(
                    ($quality / 100) * 9
                );
                $success = imagepng(
                    $image,
                    $destination,
                    $pngQuality
                );
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($source);
                $success = imagewebp(
                    $image,
                    $destination,
                    $quality
                );
                break;
            case 'image/gif':
                $success = move_uploaded_file(
                    $source,
                    $destination
                );
                break;
        }
        return $success;
    }
}
?>