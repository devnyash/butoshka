<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $fallback = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'placeholder.jpg';
    if (file_exists($fallback)) {
        $ext = strtolower(pathinfo($fallback, PATHINFO_EXTENSION));
        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
        header('Content-Type: ' . $mime);
        readfile($fallback);
    } else {
        header('HTTP/1.0 404 Not Found');
    }
    exit;
}

$stmt = $conn->prepare("SELECT image_data, image_mime, image FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($image_data, $image_mime, $image_name);
$stmt->fetch();
$stmt->close();

if ($image_data) {
    $mime = $image_mime ?: 'image/webp';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($image_data));
    echo $image_data;
    exit;
}

$fallback_dir = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
if ($image_name && file_exists($fallback_dir . $image_name)) {
    $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : (($ext === 'gif') ? 'image/gif' : 'image/jpeg'));
    header('Content-Type: ' . $mime);
    readfile($fallback_dir . $image_name);
    exit;
}

$fallback = $fallback_dir . 'placeholder.jpg';
if (file_exists($fallback)) {
    header('Content-Type: image/jpeg');
    readfile($fallback);
} else {
    header('HTTP/1.0 404 Not Found');
}
