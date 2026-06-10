<?php
require_once __DIR__ . '/db.php';

echo "Миграция изображений из папки в БД...\n\n";

$img_dir = __DIR__ . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR;
$result = $conn->query("SELECT id, image FROM products WHERE image IS NOT NULL AND image != ''");
if (!$result) {
    die("Ошибка запроса: " . $conn->error . "\n");
}

$count = 0;
$mime_map = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
];

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $filename = $row['image'];
    $filepath = $img_dir . $filename;

    if (!file_exists($filepath)) {
        echo "  [SKIP] #{$id}: файл {$filename} не найден\n";
        continue;
    }

    $data = file_get_contents($filepath);
    if ($data === false) {
        echo "  [ERROR] #{$id}: не удалось прочитать {$filename}\n";
        continue;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mime = $mime_map[$ext] ?? 'image/webp';

    $null = null;
    $stmt = $conn->prepare("UPDATE products SET image_data = ?, image_mime = ? WHERE id = ?");
    $stmt->bind_param('bsi', $null, $mime, $id);
    $stmt->send_long_data(0, $data);
    if ($stmt->execute()) {
        echo "  [OK] #{$id}: {$filename} -> БД (" . strlen($data) . " байт, {$mime})\n";
        $count++;
    } else {
        echo "  [ERROR] #{$id}: {$filename} -> " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\nГотово! Перенесено {$count} изображений.\n";
