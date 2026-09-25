<?php declare(strict_types=1);

class Upload extends Controller
{
    public function image(): void
    {
        ob_start(); // Prevent PHP warnings from leaking into JSON output

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($_SESSION['user_id'])) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Upload error or no file received.']);
            exit;
        }

        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // If pasted from clipboard, it might just be a blob with no extension. Fallback to png.
        if (empty($ext)) {
            $ext = 'png';
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid extension: ' . $ext]);
            exit;
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/images';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = 'img_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to save image. Check folder permissions.']);
            exit;
        }

        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'url' => '/uploads/images/' . $filename
        ]);
        exit;
    }
}