<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php?auth=login');
    exit;
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE reviews SET status = 'approved' WHERE id = $id");
    header('Location: admin_reviews.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
    header('Location: admin_reviews.php');
    exit;
}

$reviews = [];
$sql = "SELECT * FROM reviews ORDER BY 
        CASE status 
            WHEN 'pending' THEN 1 
            WHEN 'approved' THEN 2 
            ELSE 3 
        END, created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

$pending_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление отзывами - Админ панель</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .admin-header {
            background: #677964;
            color: white;
            padding: 20px;
            margin-top: 80px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
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
        
        .pending-badge {
            background: #ffc107;
            color: #856404;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .approved-badge {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .review-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .review-stars {
            color: #ffc107;
            font-size: 18px;
        }
        
        .review-comment {
            margin: 15px 0;
            line-height: 1.5;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-delete {
            background: #677964;
            color: white;
        }
        
        .btn-back {
            background: #2e2a21;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .stats {
            background: #2e2a21;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        @media (max-width: 576px) {
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .review-actions {
                flex-direction: column;
            }
            
            .btn {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-wrapper">
        <div class="admin-header">
            <h2>Управление отзывами</h2>
            <div class="stats">На модерации: <?php echo $pending_count; ?></div>
        </div>
        
        <div class="admin-menu">
            <a href="admin.php">Пользователи</a>
            <a href="orders.php">Заказы</a>
            <a href="tovar_uprav.php">Товары</a>
            <a href="admin_reviews.php">Отзывы</a>
            <a href="index.php">На сайт</a>
        </div>
        
        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                    <p>Отзывов пока нет</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                                <span style="color: #999; margin-left: 10px;">ID: <?php echo $review['user_id']; ?></span>
                            </div>
                            <div>
                                <?php if ($review['status'] == 'pending'): ?>
                                    <span class="pending-badge">На модерации</span>
                                <?php else: ?>
                                    <span class="approved-badge">Одобрен</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>
                        
                        <div style="color: #999; font-size: 12px;">
                            <?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?>
                        </div>
                        
                        <div class="review-actions">
                            <?php if ($review['status'] == 'pending'): ?>
                                <a href="?approve=<?php echo $review['id']; ?>" class="btn btn-approve">✓ Одобрить</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $review['id']; ?>" class="btn btn-delete" onclick="return confirm('Удалить отзыв?')">✗ Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>