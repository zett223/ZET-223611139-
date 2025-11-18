<?php
namespace Src\Controllers;

use Src\Helpers\Response;

class UploadController extends BaseController
{
    public function store()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return $this->error(415, 'Use multipart/form-data for upload');
        }
        if (!str_contains($contentType, 'multipart/form-data')) {
            return $this->error(415, 'Content-Type must be multipart/form-data');
        }

        if (empty($_FILES['file'])) return $this->error(422, 'file is required');

        $f = $_FILES['file'];
        $errorMessages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        if ($f['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->ok(['message' => 'No file uploaded'], 200);
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $message = isset($errorMessages[$f['error']]) ? $errorMessages[$f['error']] : 'Unknown upload error';
            return $this->error(400, $message);
        }
        if ($f['size'] > 2 * 1024 * 1024) return $this->error(422, 'Max 2MB');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);

        $allowed = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf'
        ];

        if (!isset($allowed[$mime])) return $this->error(422, 'Invalid mime');

        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        $dest = __DIR__ . '/../../uploads/' . $name;

        $uploadDir = dirname($dest);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Debug logging
        error_log("Upload attempt: " . print_r([
            'dest' => $dest,
            'uploadDir' => $uploadDir,
            'tmp_name' => $f['tmp_name'],
            'file_exists' => file_exists($f['tmp_name']),
            'is_dir_upload' => is_dir($uploadDir),
            'is_writable' => is_writable($uploadDir)
        ], true));

        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            error_log("Upload failed: " . print_r(error_get_last(), true));
            return $this->error(500, 'Save failed');
        }

        error_log("Upload successful: $dest");
        $this->ok(['path' => '/uploads/' . $name], 201);
    }
}