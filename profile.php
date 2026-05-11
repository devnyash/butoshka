<?php
session_start();
require_once('db.php');
require_once('kor.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: avtoris.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Обработка отмены заказа
if (isset($_GET['cancel_order']) && is_numeric($_GET['cancel_order'])) {
    $order_id = (int)$_GET['cancel_order'];
    
    // Проверяем, что заказ принадлежит пользователю и имеет статус 'new'
    $check_sql = "SELECT status FROM orders WHERE id_order = $order_id AND user_id = $user_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $order = $check_result->fetch_assoc();
        if ($order['status'] === 'new') {
            $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
            $conn->query("DELETE FROM orders WHERE id_order = $order_id");
            $message = "Заказ #$order_id успешно отменен.";
        } else {
            $error = "Нельзя отменить заказ в статусе '" . getStatusLabel($order['status']) . "'.";
        }
    } else {
        $error = "Заказ не найден.";
    }
    
    header('Location: profile.php?msg=' . urlencode($message ?: $error) . '&type=' . ($message ? 'success' : 'error'));
    exit;
}

// Отображение flash-сообщений
if (isset($_GET['msg'])) {
    if ($_GET['type'] === 'success') $message = $_GET['msg'];
    else $error = $_GET['msg'];
}

$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $login = mysqli_real_escape_string($conn, $_POST['login']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Неверный формат email";
    } else {
        $check_sql = "SELECT id FROM users WHERE (login = '$login' OR email = '$email') AND id != $user_id";
        $check_result = $conn->query($check_sql);
        if ($check_result->num_rows > 0) {
            $error = "Пользователь с таким логином или email уже существует";
        } else {
            $update_sql = "UPDATE users SET login='$login', phone='$phone', email='$email' WHERE id=$user_id";
            if ($conn->query($update_sql)) {
                $_SESSION['user_name'] = $login;
                $message = "Данные успешно обновлены";
                $user['login'] = $login;
                $user['phone'] = $phone;
                $user['email'] = $email;
            } else {
                $error = "Ошибка при обновлении";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    $check_sql = "SELECT * FROM users WHERE id = $user_id AND pass = '$current_pass'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows == 0) {
        $error = "Текущий пароль введен неверно";
    } elseif (strlen($new_pass) < 4) {
        $error = "Новый пароль должен содержать минимум 4 символа";
    } elseif ($new_pass != $confirm_pass) {
        $error = "Новый пароль и подтверждение не совпадают";
    } else {
        $update_sql = "UPDATE users SET pass='$new_pass' WHERE id=$user_id";
        if ($conn->query($update_sql)) {
            $message = "Пароль успешно изменен";
        } else {
            $error = "Ошибка при смене пароля";
        }
    }
}

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
    <title>Профиль - Butoshka</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .profile-card h3 {
            color: #2e2a21;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #677964;
        }
        
        .profile-info {
            margin-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            width: 100px;
            font-weight: 600;
            color: #677964;
        }
        
        .info-value {
            flex: 1;
            color: #2e2a21;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2e2a21;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #677964;
        }
        
        /* ЕДИНЫЙ СТИЛЬ ДЛЯ ВСЕХ КНОПОК В ПРОФИЛЕ */
        .btn {
            background: #677964;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-weight: normal;
        }
        
        .btn:hover {
            background: #556652;
            transform: translateY(-2px);
        }
        
        /* Кнопка смены пароля - тот же цвет */
        .btn-secondary {
            background: #677964;
        }
        
        .btn-secondary:hover {
            background: #556652;
        }
        
        /* Кнопка отмены заказа - красная, но с теми же отступами */
        .btn-cancel {
            background: #dc3545;
            margin-left: 10px;
        }
        
        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .orders-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .orders-section h3 {
            color: #2e2a21;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #677964;
        }
        
        .order-card {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
            position: relative;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
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
        
        .order-info {
            margin-bottom: 15px;
        }
        
        .order-info p {
            margin: 8px 0;
        }
        
        .products-list {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 14px;
        }
        
        .order-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #677964;
            margin-top: 15px;
        }
        
        .order-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
            gap: 10px;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-orders .btn {
            background: #677964;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        .empty-orders .btn:hover {
            background: #556652;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .profile-container {
                margin: 20px auto;
            }
            
            .profile-card {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .order-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .order-total {
                text-align: center;
            }
            
            .order-actions {
                justify-content: center;
            }
            
            .btn, .btn-secondary, .btn-cancel {
                width: 100%;
                text-align: center;
                margin: 5px 0;
            }
            
            .btn-cancel {
                margin-left: 0;
            }
        }
        
        @media (max-width: 480px) {
            .profile-container {
                padding: 0 15px;
            }
            
            .profile-card h3,
            .orders-section h3 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="profile-container">
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <div class="profile-card">
                <h3>Личная информация</h3>
                <div class="profile-info">
                    <div class="info-row">
                        <span class="info-label">Логин:</span>
                        <span class="info-value"><?= htmlspecialchars($user['login']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Телефон:</span>
                        <span class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="profile-card">
                <h3>Редактировать профиль</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Логин</label>
                        <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn">Сохранить изменения</button>
                </form>
            </div>
        </div>
        
        <div class="profile-card" style="margin-bottom: 30px;">
            <h3>Смена пароля</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Подтверждение нового пароля</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn">Сменить пароль</button>
            </form>
        </div>
        
        <div class="orders-section">
            <h3>Мои заказы</h3>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <p>У вас пока нет заказов</p>
                    <a href="index.php#catalog" class="btn">Перейти к покупкам</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-date">Заказ №<?= $order['id_order'] ?> от <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                            <span class="status-badge <?= getStatusClass($order['status'] ?? 'new') ?>">
                                <?= getStatusLabel($order['status'] ?? 'new') ?>
                            </span>
                        </div>
                        
                        <div class="order-info">
                            <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['address']) ?></p>
                            <p><strong>Дата доставки:</strong> <?= date('d.m.Y', strtotime($order['delivery_date'])) ?></p>
                        </div>
                        
                        <div class="products-list">
                            <strong>Состав заказа:</strong><br>
                            <?= htmlspecialchars($order['products_list']) ?>
                        </div>
                        
                        <div class="order-total">
                            Итого: <?= number_format($order['order_price'], 0, '', ' ') ?> ₽
                        </div>
                        
                        <?php if (($order['status'] ?? 'new') === 'new'): ?>
                        <div class="order-actions">
                            <a href="?cancel_order=<?= $order['id_order'] ?>" 
                               class="btn btn-cancel" 
                               onclick="return confirm('Вы уверены, что хотите отменить заказ #<?= $order['id_order'] ?>?');">
                                Отменить заказ
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>