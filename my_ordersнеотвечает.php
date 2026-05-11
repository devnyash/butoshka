<?php
require_once('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: avtoris.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$orders = [];
$sql = "SELECT o.*, 
               GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' шт)') SEPARATOR ', ') as products_list
        FROM orders o 
        LEFT JOIN order_items oi ON o.id_order = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = $user_id
        GROUP BY o.id_order
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

function getStatusLabel($status) {
    $labels = [
        'new' => 'Новый',
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
    <title>Мои заказы</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .orders-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .orders-header {
            background: #677964;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-direction: column;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            width: 120px;
            color: #666;
        }
        
        .info-value {
            color: #2e2a21;
            font-weight: 500;
        }
        
        .products-list {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .product-item {
            padding: 5px 0;
            border-bottom: 1px dashed #ddd;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .order-total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #677964;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            background: #677964;
            color: white;
        }
        
        .btn:hover {
            background: #556652;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .empty-orders p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: #2e2a21;
            border-radius: 5px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-new {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .status-assembling {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .status-shipped {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .status-delivered {
            background: #e8f5e9;
            color: #388e3c;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="orders-container">
        <div class="orders-header">
            <h2>Мои заказы</h2>
            <a href="index.php" class="back-link">На главную</a>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>У вас пока нет заказов</p>
                <a href="index.php#catalog" class="btn">Перейти к покупкам</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-date"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                    <span class="status-badge <?= getStatusClass($order['status'] ?? 'new') ?>">
                        <?= getStatusLabel($order['status'] ?? 'new') ?>
                    </span>
                </div>
                
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Адрес:</span>
                        <span class="info-value"><?= htmlspecialchars($order['address']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Сумма:</span>
                        <span class="info-value"><?= number_format($order['order_price'], 0, '', ' ') ?> ₽</span>
                    </div>
                </div>
                
                <div class="products-list">
                    <strong>Состав заказа:</strong><br>
                    <?= htmlspecialchars($order['products_list']) ?>
                </div>
                
                <div class="order-total">
                    Итого: <?= number_format($order['order_price'], 0, '', ' ') ?> ₽
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>