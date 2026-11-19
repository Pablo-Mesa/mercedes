<?php

class UploadHelper
{
    private const MAX_FILE_SIZE = 5242880;

    public static function upload(array $file, string $folder): ?string
    {
        if (!in_array($folder, ['grupos', 'riders'], true)) {
            throw new InvalidArgumentException('El destino de la imagen no es válido.');
        }

        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
            return null;
        }

        if (!is_int($uploadError) || $uploadError !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo recibir la imagen. Inténtalo nuevamente.');
        }

        $temporaryPath = $file['tmp_name'] ?? '';
        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            throw new InvalidArgumentException('El archivo recibido no es una carga válida.');
        }

        $fileSize = filesize($temporaryPath);
        if ($fileSize === false || $fileSize < 1 || $fileSize > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('La imagen debe pesar como máximo 5 MB.');
        }

        if (!class_exists(finfo::class)) {
            throw new RuntimeException('El servidor no tiene habilitada la extensión PHP Fileinfo.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $image = @getimagesize($temporaryPath);
        if (!is_string($mimeType)
            || !isset($allowedTypes[$mimeType])
            || $image === false
            || ($image['mime'] ?? null) !== $mimeType
            || ($image[0] * $image[1]) > 20000000) {
            throw new InvalidArgumentException('Solo se permiten imágenes PNG, JPEG o WebP válidas.');
        }

        $uploadDirectory = __DIR__ . '/../public/uploads/' . $folder . '/';
        if (!is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0750, true)
            && !is_dir($uploadDirectory)) {
            throw new RuntimeException('No se pudo preparar el directorio para guardar la imagen.');
        }

        $fileName = $folder . '_' . bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
        $targetPath = $uploadDirectory . $fileName;

        if (!move_uploaded_file($temporaryPath, $targetPath)) {
            throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        return $fileName;
    }
}
