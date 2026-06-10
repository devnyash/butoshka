<?php
session_start();
require_once 'db.php';
require_once 'kor.php';

$approved_reviews = [];
$sql = "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approved_reviews[] = $row;
    }
}

$total_reviews = 0;
$avg_rating = 0;
$distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$stats_result = $conn->query("SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM reviews WHERE status = 'approved'");
if ($stats_result && $stats_result->num_rows > 0) {
    $stats = $stats_result->fetch_assoc();
    $total_reviews = (int)$stats['total'];
    $avg_rating = $stats['avg_rating'] ? round((float)$stats['avg_rating'], 1) : 0;
    $dist_result = $conn->query("SELECT rating, COUNT(*) as count FROM reviews WHERE status = 'approved' GROUP BY rating ORDER BY rating DESC");
    if ($dist_result) {
        while ($row = $dist_result->fetch_assoc()) {
            $distribution[(int)$row['rating']] = (int)$row['count'];
        }
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Необходимо авторизоваться';
    } else {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $rating = intval($_POST['rating']);
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        
        if ($rating < 1 || $rating > 5) {
            $error = 'Оценка должна быть от 1 до 5';
        } elseif (empty($comment)) {
            $error = 'Введите текст отзыва';
        } else {
            $sql = "INSERT INTO reviews (user_id, user_name, rating, comment, status) VALUES ('$user_id', '$user_name', '$rating', '$comment', 'pending')";
            if ($conn->query($sql)) {
                $message = 'Спасибо за отзыв! Он будет опубликован после проверки администратором.';
            } else {
                $error = 'Ошибка при добавлении отзыва';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы - Butoshka</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reviews-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .reviews-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .reviews-header h1 {
            color: #2e2a21;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .reviews-header p {
            color: #666;
            font-size: 16px;
        }
        
        .add-review-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .add-review-section h3 {
            color: #2e2a21;
            margin-bottom: 25px;
            font-size: 24px;
            text-align: center;
        }
        
        .rating-stars {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input:checked ~ label {
            color: #ffc107;
        }
        
        .review-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            font-family: inherit;
            margin-bottom: 20px;
        }
        
        .review-form textarea:focus {
            outline: none;
            border-color: #677964;
        }
        
        .btn-submit {
            background: #677964;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #556652;
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
        
        .reviews-list-section h3 {
            color: #2e2a21;
            margin-bottom: 25px;
            font-size: 24px;
            text-align: center;
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .review-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        
        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        
        .review-author {
            font-weight: bold;
            color: #2e2a21;
            font-size: 18px;
        }
        
        .review-stars {
            color: #ffc107;
            font-size: 18px;
            letter-spacing: 2px;
        }
        
        .review-stars .star-empty {
            color: #ddd;
        }
        
        .review-date {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .review-comment {
            color: #444;
            line-height: 1.5;
            margin-top: 10px;
        }
        
        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            color: #666;
        }
        
        .login-warning {
            text-align: center;
            padding: 40px;
            background: #f5f5f5;
            border-radius: 12px;
        }
        
        .login-warning a {
            color: #677964;
            text-decoration: none;
        }
        
        .login-warning a:hover {
            text-decoration: underline;
        }
        
        .rating-label {
            font-weight: 500;
            color: #2e2a21;
            margin-bottom: 10px;
        }
        
        .reviews-rating-summary {
            background: linear-gradient(135deg, #f8faf7 0%, #eef3eb 100%);
            border-radius: 20px;
            padding: 40px;
            margin-top: 80px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .reviews-rating-summary::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(103,121,100,0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .reviews-rating-main {
            display: flex;
            gap: 40px;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .reviews-rating-average {
            text-align: center;
            flex-shrink: 0;
        }

        .reviews-big-number {
            font-size: 64px;
            font-weight: 800;
            color: #2e2a21;
            line-height: 1;
            margin-bottom: 8px;
            letter-spacing: -2px;
        }

        .reviews-big-stars {
            font-size: 28px;
            letter-spacing: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
        }

        .reviews-big-stars .star-full {
            color: #ffc107;
            text-shadow: 0 2px 8px rgba(255,193,7,0.3);
        }

        .reviews-big-stars .star-half {
            color: #ffc107;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 8px rgba(255,193,7,0.3);
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

        .reviews-total-reviews {
            color: #677964;
            font-size: 14px;
            font-weight: 500;
        }

        .reviews-distribution {
            flex: 1;
            min-width: 250px;
        }

        .reviews-bar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .reviews-bar-row:last-child {
            margin-bottom: 0;
        }

        .reviews-bar-label {
            font-size: 13px;
            font-weight: 600;
            color: #2e2a21;
            width: 20px;
            text-align: right;
            flex-shrink: 0;
        }

        .reviews-bar-track {
            flex: 1;
            height: 8px;
            background: #e8e8e8;
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
            font-size: 12px;
            color: #888;
            width: 25px;
            text-align: left;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .reviews-container {
                margin: 20px auto;
            }
            
            .reviews-header h1 {
                font-size: 28px;
            }
            
            .add-review-section {
                padding: 20px;
            }
            
            .rating-stars label {
                font-size: 25px;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .reviews-rating-summary {
                padding: 30px 20px;
            }

            .reviews-rating-main {
                flex-direction: column;
                gap: 25px;
            }

            .reviews-big-number {
                font-size: 52px;
            }

            .reviews-big-stars {
                font-size: 24px;
            }

            .reviews-distribution {
                min-width: unset;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .add-review-section h3 {
                font-size: 20px;
            }
            
            .reviews-list-section h3 {
                font-size: 20px;
            }
            
            .rating-stars label {
                font-size: 22px;
            }
            
            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="reviews-container">
            <?php if ($total_reviews > 0): ?>
            <div class="reviews-rating-summary">
                <div class="reviews-rating-main">
                    <div class="reviews-rating-average">
                        <div class="reviews-big-number"><?php echo number_format($avg_rating, 1, '.', ''); ?></div>
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
                        <div class="reviews-total-reviews">На основе <?php echo $total_reviews; ?> отзывов</div>
                    </div>
                    <div class="reviews-distribution">
                        <?php for ($i = 5; $i >= 1; $i--): 
                            $percent = $total_reviews > 0 ? round($distribution[$i] / $total_reviews * 100) : 0;
                        ?>
                        <div class="reviews-bar-row">
                            <span class="reviews-bar-label"><?php echo $i; ?></span>
                            <div class="reviews-bar-track">
                                <div class="reviews-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                            <span class="reviews-bar-count"><?php echo $distribution[$i]; ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="reviews-header">
                <h1>Отзывы наших клиентов</h1>
                <p>Ваше мнение важно для нас</p>
            </div>
            
            <div class="add-review-section">
                <h3><?php echo isset($_SESSION['user_id']) ? 'Оставить отзыв' : 'Оставить отзыв могут только зарегистрированные пользователи'; ?></h3>
                
                <?php if ($message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="" class="review-form">
                        <div class="rating-label">Ваша оценка:</div>
                        <div class="rating-stars">
                            <input type="radio" name="rating" id="star5" value="5" required>
                            <label for="star5">★</label>
                            <input type="radio" name="rating" id="star4" value="4">
                            <label for="star4">★</label>
                            <input type="radio" name="rating" id="star3" value="3">
                            <label for="star3">★</label>
                            <input type="radio" name="rating" id="star2" value="2">
                            <label for="star2">★</label>
                            <input type="radio" name="rating" id="star1" value="1">
                            <label for="star1">★</label>
                        </div>
                        <textarea name="comment" rows="4" placeholder="Поделитесь впечатлениями о букетах..." required></textarea>
                        <button type="submit" name="submit_review" class="btn-submit">Отправить отзыв</button>
                    </form>
                <?php else: ?>
                    <div class="login-warning">
                        <p>Для того чтобы оставить отзыв, необходимо <a href="#" onclick="openAuthModal(); return false;">авторизоваться</a></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="reviews-list-section">
                <h3>Отзывы покупателей</h3>
                
                <div class="reviews-list">
                    <?php if (empty($approved_reviews)): ?>
                        <div class="no-reviews">
                            <p>Пока нет ни одного отзыва. Будьте первым!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($approved_reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="review-author"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                ★
                                            <?php else: ?>
                                                <span class="star-empty">★</span>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-comment">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                                <div class="review-date">
                                    <?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>