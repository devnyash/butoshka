<?php
require_once('db.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: avtoris.php');
    exit;
}

if (isset($_GET['delete'])) {
    $order_id = $_GET['delete'];
    $conn->query("DELETE FROM orders WHERE id_order = $order_id");
    header('Location: orders.php');
    exit;
}

if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $conn->query("UPDATE orders SET status = '$status' WHERE id_order = $order_id");
    header('Location: orders.php');
    exit;
}

$orders = [];
$sql = "SELECT o.*, u.login as user_login, u.phone as user_phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

function getStatusLabel($status) {
    $labels = [
        'new' => 'Оформляется',
        'assembling' => 'В сборке',
        'shipped' => 'Передан в доставку',
        'delivered' => 'Доставлен'
    ];
    return $labels[$status] ?? $status;
}

function getStatusClass($status) {
    $classes = [
        'new' => 'status-new',
        'assembling' => 'status-assembling',
        'shipped' => 'status-shipped',
        'delivered' => 'status-delivered'
    ];
    return $classes[$status] ?? '';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
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
            align-items: center;
            gap: 20px;
            flex-direction: column;
        }
        
        .admin-menu {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .admin-menu a {
            background: #f5f5f5;
            color: #2e2a21;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-right: 10px;
            display: inline-block;
        }
        
        .orders-list {
            background: white;
            padding: 25px;
            border-radius: 10px;
        }
        
        .order-card {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-number {
            font-weight: bold;
            color: #677964;
        }
        
        .order-items {
            margin: 15px 0;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #677964;
            margin-top: 15px;
        }
        
        .btn {
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            color: white;
        }
        
        .btn-delete {
            background: #677964;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-assembling { background: #fff3e0; color: #f57c00; }
        .status-shipped { background: #f3e5f5; color: #7b1fa2; }
        .status-delivered { background: #e8f5e9; color: #388e3c; }
        
        .status-form {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .status-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .status-form button {
            padding: 8px 16px;
            background: #677964;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .admin-wrapper {
                margin: 20px auto;
                padding: 0 15px;
            }
            
            .admin-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .admin-menu a {
                display: block;
                margin: 5px 0;
                text-align: center;
            }
            
            .orders-list {
                padding: 15px;
            }
            
            .order-card {
                padding: 15px;
            }
            
            .order-header {
                flex-direction: column;
                text-align: center;
            }
            
            .item-row {
                flex-direction: column;
                text-align: center;
            }
            
            .total {
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .order-card p {
                word-break: break-word;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-wrapper">
        <div class="admin-header">
            <h2>Управление заказами</h2>
            <a href="admin.php" style="background: #2e2a21; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">Назад</a>
        </div>

        <div class="orders-list">
            <?php foreach ($orders as $order): 
                $items = [];
                $items_sql = "SELECT oi.*, p.name 
                             FROM order_items oi 
                             LEFT JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = " . $order['id_order'];
                $items_result = $conn->query($items_sql);
                $total = 0;
            ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-number">Заказ #<?= $order['id_order'] ?></span>
                    <span>от <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                
                <p><strong>Пользователь:</strong> <?= htmlspecialchars($order['user_login']) ?> (ID: <?= $order['user_id'] ?>)</p>
                <p><strong>Адрес:</strong> <?= htmlspecialchars($order['address']) ?></p>
                
                <p><strong>Статус:</strong> 
                    <span class="status-badge <?= getStatusClass($order['status'] ?? 'new') ?>">
                        <?= getStatusLabel($order['status'] ?? 'new') ?>
                    </span>
                </p>
                
                <div class="order-items">
                    <strong>Состав заказа:</strong>
                    <?php 
                    if ($items_result && $items_result->num_rows > 0) {
                        while ($item = $items_result->fetch_assoc()) {
                            $sum = $item['quantity'] * $item['price'];
                            $total += $sum;
                    ?>
                    <div class="item-row">
                        <span><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                        <span><?= number_format($sum, 0, '', ' ') ?> ₽</span>
                    </div>
                    <?php 
                        }
                    } 
                    ?>
                </div>
                
                <div class="total">
                    Итого: <?= number_format($total, 0, '', ' ') ?> ₽
                </div>
                
                <div style="text-align: right; margin-top: 10px;">
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
                        <select name="status">
                            <option value="new" <?= ($order['status'] ?? 'new') == 'new' ? 'selected' : '' ?>>Новый</option>
                            <option value="assembling" <?= ($order['status'] ?? 'new') == 'assembling' ? 'selected' : '' ?>>В сборке</option>
                            <option value="shipped" <?= ($order['status'] ?? 'new') == 'shipped' ? 'selected' : '' ?>>Передан в доставку</option>
                            <option value="delivered" <?= ($order['status'] ?? 'new') == 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                        </select>
                        <button type="submit" name="update_status">Изменить</button>
                    </form>
                    <a href="?delete=<?= $order['id_order'] ?>" 
                       class="btn btn-delete"
                       onclick="return confirm('Удалить заказ?')">Удалить</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>