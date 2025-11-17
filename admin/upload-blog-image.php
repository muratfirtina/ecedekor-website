<?php
require_once 'includes/config.php';
requireAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Dosya yükleme hatası']);
    exit;
}

$uploadDir = UPLOAD_DIR . 'blogs/content/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz dosya formatı. Sadece JPG, PNG, WebP ve GIF dosyaları yüklenebilir.']);
    exit;
}

// Dosya boyutu kontrolü (maksimum 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($_FILES['image']['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 5MB olabilir.']);
    exit;
}

$fileName = uniqid() . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $imageUrl = IMAGES_URL . '/blogs/content/' . $fileName;
    echo json_encode(['success' => true, 'url' => $imageUrl]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu']);
}
