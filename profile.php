<?php
session_start();
require_once('db.php');
require_once('kor.php');
require_once('auth_helpers.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?auth=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$user_sql = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_sql->bind_param('i', $user_id);
$user_sql->execute();
$user_result = $user_sql->get_result();
$user = $user_result->fetch_assoc();

function redirectProfileMessage(string $tab, string $text, string $type): void {
    header('Location: profile.php?tab=' . urlencode($tab) . '&msg=' . urlencode($text) . '&type=' . urlencode($type));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $login = trim($_POST['login'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectProfileMessage('profile', "РќРµРІРµСЂРЅС‹Р№ С„РѕСЂРјР°С‚ email", 'error');
    }

    if (mb_strlen($login) < 3) {
        redirectProfileMessage('profile', "Р›РѕРіРёРЅ РґРѕР»Р¶РµРЅ СЃРѕРґРµСЂР¶Р°С‚СЊ РјРёРЅРёРјСѓРј 3 СЃРёРјРІРѕР»Р°", 'error');
    }

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE (login = ? OR email = ?) AND id != ?");
    $checkStmt->bind_param('ssi', $login, $email, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        redirectProfileMessage('profile', "РџРѕР»СЊР·РѕРІР°С‚РµР»СЊ СЃ С‚Р°РєРёРј Р»РѕРіРёРЅРѕРј РёР»Рё email СѓР¶Рµ СЃСѓС‰РµСЃС‚РІСѓРµС‚", 'error');
    }

    $updateStmt = $conn->prepare("UPDATE users SET login = ?, phone = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param('sssi', $login, $phone, $email, $user_id);

    if ($updateStmt->execute()) {
        $_SESSION['user_name'] = $login;
        $_SESSION['user_email'] = $email;
        redirectProfileMessage('profile', "Р”Р°РЅРЅС‹Рµ СѓСЃРїРµС€РЅРѕ РѕР±РЅРѕРІР»РµРЅС‹", 'success');
    }

    redirectProfileMessage('profile', "РћС€РёР±РєР° РїСЂРё РѕР±РЅРѕРІР»РµРЅРёРё: " . $conn->error, 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!verifyAndUpgradePassword($conn, $user, $current_pass)) {
        redirectProfileMessage('profile', "РўРµРєСѓС‰РёР№ РїР°СЂРѕР»СЊ РІРІРµРґРµРЅ РЅРµРІРµСЂРЅРѕ", 'error');
    }

    if (!isPasswordStrong($new_pass)) {
        redirectProfileMessage('profile', "РќРѕРІС‹Р№ РїР°СЂРѕР»СЊ РґРѕР»Р¶РµРЅ Р±С‹С‚СЊ РЅРµ РєРѕСЂРѕС‡Рµ 8 СЃРёРјРІРѕР»РѕРІ Рё СЃРѕРґРµСЂР¶Р°С‚СЊ Р±СѓРєРІС‹ Рё С†РёС„СЂС‹", 'error');
    }

    if ($new_pass !== $confirm_pass) {
        redirectProfileMessage('profile', "РќРѕРІС‹Р№ РїР°СЂРѕР»СЊ Рё РїРѕРґС‚РІРµСЂР¶РґРµРЅРёРµ РЅРµ СЃРѕРІРїР°РґР°СЋС‚", 'error');
    }

    $newPasswordHash = password_hash($new_pass, PASSWORD_DEFAULT);
    $passwordStmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
    $passwordStmt->bind_param('si', $newPasswordHash, $user_id);

    if ($passwordStmt->execute()) {
        redirectProfileMessage('profile', "РџР°СЂРѕР»СЊ СѓСЃРїРµС€РЅРѕ РёР·РјРµРЅРµРЅ", 'success');
    }

    redirectProfileMessage('profile', "РћС€РёР±РєР° РїСЂРё СЃРјРµРЅРµ РїР°СЂРѕР»СЏ", 'error');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $login = trim($_POST['login']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Неверный формат email";
    } elseif (strlen($login) < 3) {
        $error = "Логин должен содержать минимум 3 символа";
    } else {
        $check_sql = $conn->prepare("SELECT id FROM users WHERE (login = ? OR email = ?) AND id != ?");
        $check_sql->bind_param('ssi', $login, $email, $user_id);
        $check_sql->execute();
        $check_result = $check_sql->get_result();
        if ($check_result->num_rows > 0) {
            $error = "Пользователь с таким логином или email уже существует";
        } else {
            $update_sql = $conn->prepare("UPDATE users SET login = ?, phone = ?, email = ? WHERE id = ?");
            $update_sql->bind_param('sssi', $login, $phone, $email, $user_id);
            if ($update_sql->execute()) {
                $_SESSION['user_name'] = $login;
                $_SESSION['user_email'] = $email;
                $message = "Данные успешно обновлены";
                $user['login'] = $login;
                $user['phone'] = $phone;
                $user['email'] = $email;
            } else {
                $error = "Ошибка при обновлении: " . $conn->error;
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

if (isset($_GET['cancel_order']) && is_numeric($_GET['cancel_order'])) {
    $order_id = (int)$_GET['cancel_order'];

    $check_sql = "SELECT status FROM orders WHERE id_order = $order_id AND user_id = $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        $order = $check_result->fetch_assoc();
        if ($order['status'] === 'new') {
            $conn->query("DELETE FROM orders WHERE id_order = $order_id AND user_id = $user_id");
            $message = "Заказ #$order_id успешно отменен.";
        } else {
            $error = "Нельзя отменить заказ в статусе '" . getStatusLabel($order['status']) . "'.";
        }
    } else {
        $error = "Заказ не найден.";
    }
    header('Location: profile.php?tab=orders&msg=' . urlencode($message ?: $error) . '&type=' . ($message ? 'success' : 'error'));
    exit;
}

if (isset($_GET['delete_review']) && is_numeric($_GET['delete_review'])) {
    $review_id = (int)$_GET['delete_review'];

    $check_sql = "SELECT id FROM reviews WHERE id = $review_id AND user_id = $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        $conn->query("DELETE FROM reviews WHERE id = $review_id");
        $message = "Отзыв успешно удален.";
    } else {
        $error = "Отзыв не найден или у вас нет прав на его удаление.";
    }
    header('Location: profile.php?tab=reviews&msg=' . urlencode($message ?: $error) . '&type=' . ($message ? 'success' : 'error'));
    exit;
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

$user_reviews = [];
$reviews_sql = "SELECT * FROM reviews WHERE user_id = $user_id ORDER BY created_at DESC";
$reviews_result = $conn->query($reviews_sql);
if ($reviews_result && $reviews_result->num_rows > 0) {
    while ($row = $reviews_result->fetch_assoc()) {
        $user_reviews[] = $row;
    }
}

$total_reviews = 0;
$avg_rating = 0;
$rating_distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$stats_result = $conn->query("SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM reviews WHERE status = 'approved'");
if ($stats_result && $stats_result->num_rows > 0) {
    $stats = $stats_result->fetch_assoc();
    $total_reviews = (int)$stats['total'];
    $avg_rating = $stats['avg_rating'] ? round((float)$stats['avg_rating'], 1) : 0;
    $dist_result = $conn->query("SELECT rating, COUNT(*) as count FROM reviews WHERE status = 'approved' GROUP BY rating ORDER BY rating DESC");
    if ($dist_result) {
        while ($row = $dist_result->fetch_assoc()) {
            $rating_distribution[(int)$row['rating']] = (int)$row['count'];
        }
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

function getReviewStatusLabel($status) {
    $labels = [
        'pending' => 'На модерации',
        'approved' => 'Одобрен',
        'rejected' => 'Отклонен'
    ];
    return $labels[$status] ?? $status;
}

function getReviewStatusClass($status) {
    $classes = [
        'pending' => 'status-pending',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected'
    ];
    return $classes[$status] ?? '';
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
if (isset($_GET['msg'])) {
    if ($_GET['type'] === 'success') $message = $_GET['msg'];
    else $error = $_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Butoshka</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-page {
            background: #f5f5f5;
            min-height: calc(100vh - 200px);
            padding: 40px 0;
        }

        .profile-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 80px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-welcome h1 {
            color: #2e2a21;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .profile-welcome p {
            color: #677964;
            font-size: 16px;
        }

        .profile-stats {
            display: flex;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #677964;
        }

        .stat-label {
            font-size: 13px;
            color: #888;
        }

        .profile-tabs {
            display: flex;
            gap: 5px;
            background: white;
            border-radius: 15px;
            padding: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex-wrap: wrap;
        }

        .tab-btn {
            flex: 1;
            padding: 14px 20px;
            background: transparent;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .tab-btn:hover {
            background: #f5f5f5;
            color: #2e2a21;
        }

        .tab-btn.active {
            background: #677964;
            color: white;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }

        .profile-card h3 {
            color: #2e2a21;
            font-size: 22px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #677964;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2e2a21;
        }

        .form-group input {
            width: 100%;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #677964;
        }

        .form-row {
            display: grid;
            flex-direction: column;
        }

        .btn {
            background: #677964;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            background: #556652;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #677964;
            color: #677964;
        }

        .btn-outline:hover {
            background: #677964;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
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

        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            width: 120px;
            font-weight: 600;
            color: #677964;
        }

        .info-value {
            flex: 1;
            color: #2e2a21;
        }

        .order-card {
            background: #f9f9f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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

        .order-number {
            font-weight: bold;
            color: #2e2a21;
        }

        .order-date {
            color: #888;
            font-size: 14px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-assembling { background: #fff3e0; color: #f57c00; }
        .status-shipped { background: #f3e5f5; color: #7b1fa2; }
        .status-delivered { background: #e8f5e9; color: #388e3c; }

        .order-products {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin: 15px 0;
            font-size: 14px;
        }

        .order-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #677964;
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .reviews-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #677964;
        }

        .reviews-header-row h3 {
            margin: 0;
            padding: 0;
            border: none;
        }

        .reviews-rating-block {
            background: linear-gradient(135deg, #f8faf7, #f0f5ee);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .reviews-rating-main {
            display: flex;
            gap: 35px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .reviews-rating-average {
            text-align: center;
            flex-shrink: 0;
        }

        .reviews-big-number {
            font-size: 56px;
            font-weight: 800;
            color: #2e2a21;
            line-height: 1;
            letter-spacing: -2px;
        }

        .reviews-big-stars {
            font-size: 26px;
            letter-spacing: 4px;
            display: flex;
            justify-content: center;
            margin: 6px 0 4px;
        }

        .reviews-big-stars .star-full {
            color: #ffc107;
            text-shadow: 0 2px 6px rgba(255,193,7,0.3);
        }

        .reviews-big-stars .star-half {
            color: #ffc107;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 6px rgba(255,193,7,0.3);
        }

        .reviews-big-stars .star-half::after {
            content: '★';
            position: absolute;
            left: 50%;
            top: 0;
            width: 50%;
            overflow: hidden;
            color: #ddd;
        }

        .reviews-big-stars .star-empty {
            color: #e0e0e0;
        }

        .reviews-rating-total {
            color: #677964;
            font-size: 13px;
            font-weight: 500;
        }

        .reviews-rating-bars {
            flex: 1;
            min-width: 200px;
        }

        .reviews-bar-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .reviews-bar-row:last-child {
            margin-bottom: 0;
        }

        .reviews-bar-label {
            font-size: 12px;
            font-weight: 700;
            color: #2e2a21;
            width: 16px;
            text-align: right;
            flex-shrink: 0;
        }

        .reviews-bar-track {
            flex: 1;
            height: 7px;
            background: #e2e8e0;
            border-radius: 10px;
            overflow: hidden;
        }

        .reviews-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffc107, #ffb300);
            border-radius: 10px;
            transition: width 1s ease;
        }

        .reviews-bar-count {
            font-size: 11px;
            color: #888;
            width: 20px;
            text-align: left;
            flex-shrink: 0;
        }

        .review-card {
            background: #f9f9f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .review-stars {
            color: #ffc107;
            font-size: 18px;
        }

        .review-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .review-comment {
            color: #444;
            line-height: 1.5;
            margin: 15px 0;
        }

        .review-date {
            color: #999;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .profile-page {
                padding: 20px 0;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-stats {
                justify-content: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
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
            
            .review-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .reviews-rating-block {
                padding: 20px;
            }

            .reviews-rating-main {
                flex-direction: column;
                gap: 20px;
            }

            .reviews-big-number {
                font-size: 48px;
            }

            .reviews-big-stars {
                font-size: 22px;
            }

            .reviews-rating-bars {
                min-width: unset;
                width: 100%;
            }

            .reviews-header-row {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .profile-card {
                padding: 20px;
            }
            
            .profile-card h3 {
                font-size: 18px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .order-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
    <div class="profile-page">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-welcome">
                    <h1>Привет, <?= htmlspecialchars($user['login']) ?>!</h1>
                    <p>Добро пожаловать в ваш личный кабинет</p>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= count($orders) ?></div>
                        <div class="stat-label">заказов</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= count($user_reviews) ?></div>
                        <div class="stat-label">отзывов</div>
                    </div>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="profile-tabs">
                <button class="tab-btn <?= $active_tab == 'profile' ? 'active' : '' ?>" data-tab="profile">Личные данные</button>
                <button class="tab-btn <?= $active_tab == 'orders' ? 'active' : '' ?>" data-tab="orders">Мои заказы</button>
                <button class="tab-btn <?= $active_tab == 'reviews' ? 'active' : '' ?>" data-tab="reviews">Мои отзывы</button>
            </div>
            
            <div id="tab-profile" class="tab-content <?= $active_tab == 'profile' ? 'active' : '' ?>">
                <div class="profile-card">
                    <h3>Личная информация</h3>
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
                    
                    <br><br>
        
                    <h3 >Редактировать профиль</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Логин</label>
                                <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" required minlength="3">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7 (XXX) XXX-XX-XX">
                        </div>
                        <button type="submit" name="update_profile" class="btn">Сохранить изменения</button>
                    </form>
                
                    <h3>Смена пароля</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Текущий пароль</label>
                                <input type="password" name="current_password" required autocomplete="current-password">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Новый пароль</label>
                                <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <label>Подтверждение пароля</label>
                                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn">Сменить пароль</button>
                    </form>
                </div>
            </div>
            
            <div id="tab-orders" class="tab-content <?= $active_tab == 'orders' ? 'active' : '' ?>">
                <div class="profile-card">
                    <h3>История заказов</h3>
                    
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <p>📭 У вас пока нет заказов</p>
                            <a href="index.php#catalog" class="btn">Перейти к покупкам</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-number">Заказ #<?= $order['id_order'] ?></span>
                                    <span class="order-date">от <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                                    <span class="status-badge <?= getStatusClass($order['status'] ?? 'new') ?>">
                                        <?= getStatusLabel($order['status'] ?? 'new') ?>
                                    </span>
                                </div>
                                
                                <div class="order-products">
                                    <strong>Состав:</strong><br>
                                    <?= htmlspecialchars($order['products_list']) ?>
                                </div>
                                
                                <div class="order-total">
                                    Итого: <?= number_format($order['order_price'], 0, '', ' ') ?> ₽
                                </div>
                                
                                <?php if (($order['status'] ?? 'new') === 'new'): ?>
                                    <div class="order-actions">
                                        <a href="?cancel_order=<?= $order['id_order'] ?>&tab=orders" 
                                           class="btn btn-danger btn-sm" 
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
            
            <div id="tab-reviews" class="tab-content <?= $active_tab == 'reviews' ? 'active' : '' ?>">
                <?php if ($total_reviews > 0): ?>
                <div class="reviews-rating-block">
                    <div class="reviews-rating-main">
                        <div class="reviews-rating-average">
                            <div class="reviews-big-number"><?= number_format($avg_rating, 1, '.', '') ?></div>
                            <div class="reviews-big-stars">
                                <?php
                                $full = floor($avg_rating);
                                $half = ($avg_rating - $full) >= 0.5;
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $full): ?>
                                        <span class="star-full">★</span>
                                    <?php elseif ($i == $full + 1 && $half): ?>
                                        <span class="star-half">★</span>
                                    <?php else: ?>
                                        <span class="star-empty">★</span>
                                    <?php endif;
                                endfor; ?>
                            </div>
                            <div class="reviews-rating-total"><?= $total_reviews ?> отзывов</div>
                        </div>
                        <div class="reviews-rating-bars">
                            <?php for ($i = 5; $i >= 1; $i--):
                                $percent = $total_reviews > 0 ? round($rating_distribution[$i] / $total_reviews * 100) : 0;
                            ?>
                            <div class="reviews-bar-row">
                                <span class="reviews-bar-label"><?= $i ?></span>
                                <div class="reviews-bar-track">
                                    <div class="reviews-bar-fill" style="width: <?= $percent ?>%;"></div>
                                </div>
                                <span class="reviews-bar-count"><?= $rating_distribution[$i] ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="profile-card">
                    <div class="reviews-header-row">
                        <h3>Мои отзывы</h3>
                        <a href="reviews.php" class="btn btn-sm <?= empty($user_reviews) ? '' : 'btn-outline' ?>">Написать отзыв</a>
                    </div>
                    
                    <?php if (empty($user_reviews)): ?>
                        <div class="empty-state">
                            <p>✍️ Вы еще не оставляли отзывы</p>
                            <a href="reviews.php" class="btn">Написать отзыв</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($user_reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?= $i <= $review['rating'] ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="review-status <?= getReviewStatusClass($review['status']) ?>">
                                        <?= getReviewStatusLabel($review['status']) ?>
                                    </span>
                                </div>
                                
                                <div class="review-comment">
                                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                    <div class="review-date">
                                        <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                                    </div>
                                    
                                    <?php if ($review['status'] == 'pending'): ?>
                                        <a href="?delete_review=<?= $review['id'] ?>&tab=reviews" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Удалить этот отзыв?');">
                                            Удалить
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </main>
    
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                window.history.pushState({}, '', url);
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(`tab-${tabName}`).classList.add('active');
            });
        });
    </script>
    
    <?php include 'footer.php'; ?>
