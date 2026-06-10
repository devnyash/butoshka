<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT image_data, image_mime FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($image_data, $image_mime);
$stmt->fetch();
$stmt->close();

if ($image_data) {
    $mime = $image_mime ?: 'image/webp';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($image_data));
    echo $image_data;
    exit;
}

$fallback = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'placeholder.jpg';
if (file_exists($fallback)) {
    $ext = strtolower(pathinfo($fallback, PATHINFO_EXTENSION));
    $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
    header('Content-Type: ' . $mime);
    readfile($fallback);
} else {
    header('HTTP/1.0 404 Not Found');
}
