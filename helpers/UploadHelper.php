<?php
// app/helpers/UploadHelper.php

class UploadHelper {
    public static function upload(array $file, string $folder): ?string {
        if (empty($file['name'])) {
            return null;
        }

        $uploadDir = __DIR__ . '/../public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $folder . '_' . uniqid() . '.' . $ext;
        // Evitar que se genere un archivo llamado "default.png"
        if ($fileName === 'default.png') {
            $fileName = $folder . '_' . uniqid() . '_safe.' . $ext;
        }
        $targetFile = $uploadDir . $fileName;

        

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Guardar solo el nombre en BD
            return $fileName;
        }

        return null;
    }
}
