<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php?auth=login');
    exit;
}

if (isset($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user'];
    if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $user_id AND role != 'admin'");
    }
    header('Location: admin.php');
    exit;
}

$users = [];
$sql = "SELECT id, email, login, pass, phone, role FROM users ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$pending_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
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
            margin-top: 80px;
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
            font-size: 24px;
        }
        
        .admin-header .admin-info {
            font-size: 14px;
            opacity: 0.9;
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
            position: relative;
        }
        
        .admin-menu a:hover {
            background: #677964;
            color: white;
        }
        
        .menu-badge {
            background: #ffc107;
            color: #856404;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11px;
            margin-left: 8px;
            font-weight: bold;
        }
        
        .admin-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
        }
        
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .users-header h3 {
            color: #2e2a21;
            font-size: 20px;
        }
        
        .stats {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 15px;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #677964;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }
        
        .users-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .users-table tr:hover {
            background: #f5f5f5;
        }
        
        .admin-badge {
            background: #ecde8e;
            color: #2e2a21;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .user-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .warning {
            margin-top: 20px;
            padding: 12px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
            font-size: 14px;
            border-radius: 4px;
        }
        
        .btn {
            background: #2e2a21;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .btn-small {
            padding: 5px 12px;
            font-size: 12px;
        }
        
        .btn-delete {
            background: #677964;
        }
        
        .no-users {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 992px) {
            .admin-wrapper {
                margin: 20px auto;
            }
            
            .admin-header h2 {
                font-size: 22px;
            }
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                padding: 0 15px;
                margin: 15px auto;
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
            
            .users-header h3 {
                font-size: 18px;
            }
            
            .stats {
                font-size: 12px;
                padding: 4px 10px;
            }
            
            .users-table th,
            .users-table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .admin-badge, .user-badge {
                padding: 3px 8px;
                font-size: 11px;
            }
            
            .btn-small {
                padding: 4px 10px;
                font-size: 11px;
            }
        }
        
        @media (max-width: 576px) {
            .admin-wrapper {
                padding: 0 10px;
                margin: 10px auto;
            }
            
            .admin-header {
                padding: 12px;
            }
            
            .admin-header h2 {
                font-size: 18px;
            }
            
            .admin-header .admin-info {
                font-size: 12px;
            }
            
            .admin-menu {
                padding: 10px;
                gap: 8px;
            }
            
            .admin-menu a {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .admin-content {
                padding: 12px;
            }
            
            .users-header {
                flex-direction: column;
                text-align: center;
            }
            
            .users-header h3 {
                font-size: 16px;
            }
            
            .users-table,
            .users-table thead,
            .users-table tbody,
            .users-table tr,
            .users-table td {
                display: block;
            }
            
            .users-table thead {
                display: none;
            }
            
            .users-table tr {
                margin-bottom: 15px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 10px;
                background: white;
            }
            
            .users-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 5px;
                border-bottom: 1px solid #eee;
                text-align: right;
            }
            
            .users-table td:last-child {
                border-bottom: none;
            }
            
            .users-table td::before {
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                color: #677964;
                margin-right: 10px;
            }
            
            .users-table td[data-label="ID"]::before { content: "ID:"; }
            .users-table td[data-label="Логин"]::before { content: "Логин:"; }
            .users-table td[data-label="Email"]::before { content: "Email:"; }
            .users-table td[data-label="Телефон"]::before { content: "Телефон:"; }
            .users-table td[data-label="Роль"]::before { content: "Роль:"; }
            .users-table td[data-label="Действия"]::before { content: "Действия:"; }
            
            .warning {
                font-size: 12px;
                text-align: center;
            }
        }
        
        @media (max-width: 380px) {
            .admin-menu a {
                font-size: 11px;
                padding: 5px 10px;
            }
            
            .users-table td {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                text-align: left;
            }
            
            .users-table td::before {
                margin-bottom: 3px;
            }
            
            .btn-small {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-wrapper">
        <div class="admin-header">
            <div>
                <h2>Панель администратора</h2>
                <div class="admin-info">Вы вошли как: <?= htmlspecialchars($_SESSION['user_name']) ?></div>
            </div>
        </div>

        <div class="admin-menu">
            <a href="admin.php">Пользователи</a>
            <a href="orders.php">Заказы</a>
            <a href="tovar_uprav.php">Товары</a>
            <a href="admin_reviews.php">
                Отзывы
                <?php if ($pending_reviews > 0): ?>
                    <span class="menu-badge"><?= $pending_reviews ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php">На сайт</a>
        </div>

        <div class="admin-content">
            <div class="users-header">
                <h3>
                    Управление пользователями 
                    <span class="stats">Всего: <?= count($users) ?></span>
                </h3>
            </div>
            
            <?php if (empty($users)): ?>
                <div class="no-users">
                    <p>Пользователей пока нет</p>
                </div>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td data-label="ID">#<?= $user['id'] ?></td>
                                <td data-label="Логин"><strong><?= htmlspecialchars($user['login']) ?></strong></td>
                                <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                                <td data-label="Телефон"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                <td data-label="Роль">
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="admin-badge">Администратор</span>
                                    <?php else: ?>
                                        <span class="user-badge">Пользователь</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Действия">
                                    <?php if ($user['role'] != 'admin'): ?>
                                        <a href="?delete_user=<?= $user['id'] ?>" 
                                           class="btn btn-small btn-delete"
                                           onclick="return confirm('Удалить пользователя <?= htmlspecialchars($user['login']) ?>?')">
                                            Удалить
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="warning">
                    ⚠️ Внимание: Администратора нельзя удалить.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>