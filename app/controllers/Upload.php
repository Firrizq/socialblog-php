<?php declare(strict_types=1);

class Upload extends Controller
{
    public function image(): void
    {
        $this->handleUpload();
    }

    public function video(): void
    {
        $this->handleUpload();
    }

    public function media(): void
    {
        $this->handleUpload();
    }

    private function handleUpload(): void
    {
        ob_start(); // Prevent PHP warnings from leaking into JSON output

        // Handle potential post_max_size overflow gracefully
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            ob_end_clean();
            header('Content-Type: application/json');
            $postMax = ini_get('post_max_size') ?: 'unknown';
            echo json_encode([
                'success' => false,
                'message' => "Uploaded file exceeds the server post limit (post_max_size: {$postMax}). Please choose a smaller file."
            ]);
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($_SESSION['user_id'])) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Find file under standard field names
        $fileKey = null;
        foreach (['media', 'video', 'image', 'file'] as $key) {
            if (isset($_FILES[$key])) {
                $fileKey = $key;
                break;
            }
        }

        if (!$fileKey || !isset($_FILES[$fileKey])) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No file received or upload error.']);
            exit;
        }

        $file = $_FILES[$fileKey];
        $errorCode = $file['error'];

        if ($errorCode !== UPLOAD_ERR_OK) {
            ob_end_clean();
            header('Content-Type: application/json');
            $message = match ($errorCode) {
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini (' . (ini_get('upload_max_filesize') ?: 'unknown') . ').',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE specified in the form.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by a PHP extension.',
                default => 'Upload error code: ' . $errorCode
            };
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // If pasted from clipboard without an extension, fallback to png
        if (empty($ext)) {
            $ext = 'png';
        }

        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedVideoExts = ['mp4', 'webm', 'ogg'];
        $allowedExts = array_merge($allowedImageExts, $allowedVideoExts);

        if (!in_array($ext, $allowedExts, true)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file extension: .' . $ext . '. Allowed: JPG, PNG, GIF, WEBP, MP4, WEBM, OGG.'
            ]);
            exit;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : mime_content_type($file['tmp_name']);
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
        $allowedMimes = array_merge($allowedImageMimes, $allowedVideoMimes);

        if (!in_array($mimeType, $allowedMimes, true)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid MIME type: ' . $mimeType . '. Please upload an accepted image or video.'
            ]);
            exit;
        }

        $isVideo = in_array($ext, $allowedVideoExts, true) || in_array($mimeType, $allowedVideoMimes, true);
        $maxSizeBytes = $isVideo ? (50 * 1024 * 1024) : (10 * 1024 * 1024); // 50MB for video, 10MB for image

        if ($file['size'] > $maxSizeBytes) {
            ob_end_clean();
            header('Content-Type: application/json');
            $maxMb = $isVideo ? '50MB' : '10MB';
            echo json_encode([
                'success' => false,
                'message' => "File size exceeds the allowed limit of {$maxMb}."
            ]);
            exit;
        }

        $subFolder = $isVideo ? 'videos' : 'images';
        $prefix = $isVideo ? 'vid_' : 'img_';
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/' . $subFolder;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to save media file. Check folder permissions.']);
            exit;
        }

        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'url' => '/uploads/' . $subFolder . '/' . $filename,
            'type' => $isVideo ? 'video' : 'image',
            'mime' => $mimeType,
            'ext' => $ext
        ]);
        exit;
    }
}