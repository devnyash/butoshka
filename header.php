<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'kor.php';
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title><?php echo $page_title ?? 'Butoshka - Магазин букетов'; ?></title>
</head>
<body>
<header>
    <a href="index.php">BUTOSHKA</a>
    <div class="burger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <nav class="nav-menu">
        <a href="index.php">Главная</a>
        <a href="reviews.php">Отзывы</a>
        <a href="korzina.php">Корзина <?php 
            if($cartCount > 0) {
                echo '<span class="cart-counter">'.$cartCount.'</span>';
            } else {
                echo '<span class="cart-counter">0</span>';
            }
        ?></a>
        <a href="profile.php">Профиль</a>

        <?php
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            echo '<a href="admin.php" style="color: #ecde8e;">Админ панель</a>';
        }
        if (isset($_SESSION['user_name'])) {
            echo '<a href="logout.php">Выйти</a>';
        } else {
            echo '<a href="avtoris.php">Войти</a>';
        }
        ?>
    </nav>
</header>

<script>
function toggleMenu() {
    document.querySelector('.nav-menu').classList.toggle('active');
}
</script>