<?php
require_once('db.php');
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: avtoris.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$error = '';

function uploadImage($file, $product_id) {
    $target_dir = "img/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($imageFileType, $allowed_types)) {
        error_log("uploadImage: неподдерживаемый тип файла: " . $imageFileType);
        return false;
    }
    
    $new_filename = $product_id . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    error_log("uploadImage: пробуем загрузить в " . $target_file);
    error_log("uploadImage: tmp_name = " . $file["tmp_name"]);
    error_log("uploadImage: size = " . $file["size"]);
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        error_log("uploadImage: успешно загружено");
        return $new_filename;
    }
    
    error_log("uploadImage: ошибка загрузки, код ошибки = " . $file["error"]);
    return false;
}

if ($action == 'add' && isset($_POST['submit_add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    
    error_log("ADD PRODUCT: name=$name, price=$price");
    error_log("ADD PRODUCT: FILES = " . print_r($_FILES, true));
    
    $sql = "INSERT INTO products (name, description, price) VALUES ('$name', '$description', '$price')";
    
    if ($conn->query($sql) === TRUE) {
        $product_id = $conn->insert_id;
        error_log("ADD PRODUCT: created with id = $product_id");
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
            error_log("ADD PRODUCT: пробуем загрузить изображение");
            $image_name = uploadImage($_FILES['image'], $product_id);
            if ($image_name) {
                $conn->query("UPDATE products SET image = '$image_name' WHERE id = $product_id");
                error_log("ADD PRODUCT: изображение сохранено как $image_name");
            } else {
                error_log("ADD PRODUCT: загрузка изображения не удалась");
            }
        } else {
            error_log("ADD PRODUCT: изображение не передано, error=" . ($_FILES['image']['error'] ?? 'не массив'));
        }
        
        header('Location: tovar_uprav.php?success=added');
        exit;
    } else {
        $error = "Ошибка при добавлении: " . $conn->error;
    }
}

if ($action == 'edit' && isset($_POST['submit_edit'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    
    $sql = "UPDATE products SET name='$name', description='$description', price='$price' WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
            $old_result = $conn->query("SELECT image FROM products WHERE id = $id");
            $old_row = $old_result->fetch_assoc();
            
            if ($old_row['image'] && file_exists("img/" . $old_row['image'])) {
                unlink("img/" . $old_row['image']);
            }
            
            $image_name = uploadImage($_FILES['image'], $id);
            if ($image_name) {
                $conn->query("UPDATE products SET image = '$image_name' WHERE id = $id");
            }
        }
        
        header('Location: tovar_uprav.php?success=edited');
        exit;
    } else {
        $error = "Ошибка при редактировании: " . $conn->error;
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $result = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        if ($row['image'] && file_exists("img/" . $row['image'])) {
            unlink("img/" . $row['image']);
        }
    }
    
    $conn->query("DELETE FROM products WHERE id=$id");
    header('Location: tovar_uprav.php?success=deleted');
    exit;
}

$product = null;
if ($action == 'edit' && isset($_GET['id']) && !isset($_POST['submit_edit'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM products WHERE id=$id");
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header('Location: tovar_uprav.php');
        exit;
    }
}

$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'added') $message = "Товар успешно добавлен!";
    if ($_GET['success'] == 'edited') $message = "Товар успешно обновлен!";
    if ($_GET['success'] == 'deleted') $message = "Товар успешно удален!";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
        }

        main {
            flex: 1 0 auto;
            width: 100%;
        }

        .footer {
            flex-shrink: 0;
            width: 100%;
        }

        .admin-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .admin-header {
            background: #677964;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .admin-header h2 {
            color: white;
            margin: 0;
        }
        
        .admin-menu {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .admin-menu a {
            background: #f5f5f5;
            color: #2e2a21;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .admin-menu a:hover {
            background: #677964;
            color: white;
        }
        
        .admin-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            overflow-x: auto;
        }
        
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2e2a21;
            font-weight: bold;
            font-size: 15px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-group input[type="file"] {
            padding: 10px;
            background: #f5f5f5;
        }
        
        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .current-image img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .current-image span {
            font-size: 14px;
            color: #666;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            background: #2e2a21;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-add {
            background: #677964;
        }
        
        .btn-edit {
            background: #677964;
        }
        
        .btn-delete {
            background: #677964;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 14px;
            margin: 0 2px;
        }
        
        .cancel-btn {
            background: #6c757d;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            min-width: 500px;
        }
        
        th {
            background: #677964;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .price {
            color: #677964;
            font-weight: bold;
            white-space: nowrap;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .section-title {
            margin: 0 0 25px 0;
            color: #2e2a21;
            border-bottom: 2px solid #677964;
            padding-bottom: 10px;
            font-size: 22px;
        }

        .form-wrapper {
            margin-top: 30px;
        }
        
        .form-wrapper form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state p {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                padding: 0 15px;
                margin: 20px auto;
            }
            
            .admin-header {
                padding: 15px;
                flex-direction: column;
                text-align: center;
            }
            
            .admin-header h2 {
                font-size: 20px;
            }
            
            .admin-menu {
                padding: 12px;
                justify-content: center;
            }
            
            .admin-menu a {
                padding: 8px 15px;
                font-size: 14px;
            }
            
            .admin-content {
                padding: 15px;
            }
            
            .section-title {
                font-size: 18px;
            }
            
            .form-wrapper form {
                padding: 20px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .btn-small {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-wrapper {
                padding: 0 10px;
                margin: 15px auto;
            }
            
            .admin-header {
                padding: 12px;
            }
            
            .admin-header h2 {
                font-size: 18px;
            }
            
            .admin-menu a {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .admin-content {
                padding: 12px;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .form-group input,
            .form-group textarea {
                padding: 10px;
                font-size: 14px;
            }
            
            .form-wrapper form {
                padding: 15px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            th, td {
                padding: 6px;
                font-size: 12px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn-small {
                width: 100%;
                text-align: center;
                margin: 0;
            }
            
            .product-image {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="admin-wrapper">
            <div class="admin-header">
                <h2>Управление товарами</h2>
                <a href="admin.php" class="btn">Назад в админ панель</a>
            </div>

            <div class="admin-menu">
                <a href="tovar_uprav.php">Список товаров</a>
                <a href="tovar_uprav.php?action=add">Добавить товар</a>
            </div>

            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="success-message">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($action == 'add'): ?>
                    <div class="form-wrapper">
                        <h3 class="section-title">Добавить новый товар</h3>
                        
                        <div class="form-container">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Название товара:</label>
                                    <input type="text" name="name" required placeholder="Например: Букет Нежность">
                                </div>
                                
                                <div class="form-group">
                                    <label>Описание:</label>
                                    <textarea name="description" required placeholder="Подробное описание товара..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Цена (₽):</label>
                                    <input type="number" name="price" step="0.01" required placeholder="0.00">
                                </div>
                                
                                <div class="form-group">
                                    <label>Фото товара:</label>
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small style="color: #666; display: block; margin-top: 5px;">Поддерживаемые форматы: JPG, PNG, GIF, WEBP</small>
                                </div>
                                
                                <div class="button-group">
                                    <button type="submit" name="submit_add" class="btn btn-add">Сохранить товар</button>
                                    <a href="tovar_uprav.php" class="btn cancel-btn">Отмена</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                <?php elseif ($action == 'edit' && $product): ?>
                    <div class="form-wrapper">
                        <h3 class="section-title">Редактировать товар: <?= htmlspecialchars($product['name']) ?></h3>
                        
                        <div class="form-container">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                
                                <div class="form-group">
                                    <label>Название товара:</label>
                                    <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Описание:</label>
                                    <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Цена (₽):</label>
                                    <input type="number" name="price" step="0.01" required value="<?= $product['price'] ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Текущее фото:</label>
                                    <div class="current-image">
                                        <?php 
                                        $current_img = $product['image'];
                                        $img_src = !empty($current_img) ? "img/" . htmlspecialchars($current_img) : "img/placeholder.jpg";
                                        ?>
                                        <img src="<?= $img_src ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>">
                                        <span><?= htmlspecialchars($current_img) ?></span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Заменить фото (оставьте пустым, если не хотите менять):</label>
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small style="color: #666; display: block; margin-top: 5px;">Поддерживаемые форматы: JPG, PNG, GIF, WEBP</small>
                                </div>
                                
                                <div class="button-group">
                                    <button type="submit" name="submit_edit" class="btn btn-edit">Сохранить изменения</button>
                                    <a href="tovar_uprav.php" class="btn cancel-btn">Отмена</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <h3 class="section-title">Список товаров (<?= count($products) ?> шт.)</h3>
                    
                    <?php if (empty($products)): ?>
                        <div class="empty-state">
                            <p>Товаров пока нет</p>
                            <a href="tovar_uprav.php?action=add" class="btn btn-add">Добавить первый товар</a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Фото</th>
                                        <th>Название</th>
                                        <th>Описание</th>
                                        <th>Цена</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $item): ?>
                                        <tr>
                                            <td><strong>#<?= $item['id'] ?></strong></td>
                                            <td>
                                                <?php 
                                                $img = $item['image'];
                                                $img_src = !empty($img) ? "img/" . htmlspecialchars($img) : "img/placeholder.jpg";
                                                ?>
                                                <img src="<?= $img_src ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     class="product-image">
                                            </td>
                                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                            <td><?= htmlspecialchars(mb_substr($item['description'], 0, 50)) ?><?= strlen($item['description']) > 50 ? '...' : '' ?></td>
                                            <td class="price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</td>
                                            <td class="action-buttons">
                                                <a href="tovar_uprav.php?action=edit&id=<?= $item['id'] ?>" 
                                                   class="btn btn-small btn-edit">Изменить</a>
                                                <a href="tovar_uprav.php?action=delete&id=<?= $item['id'] ?>" 
                                                   class="btn btn-small btn-delete"
                                                   onclick="return confirm('Удалить товар &quot;<?= htmlspecialchars($item['name']) ?>&quot;?')">Удалить</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>