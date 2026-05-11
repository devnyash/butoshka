<?php
session_start();
require_once 'kor.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: avtoris.php');
    exit;
}

if (getCartCount() == 0) {
    header('Location: korzina.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
    <main>
        <div class="wrapper">
            <form action="zak.php" method="post">
                <h2>Оформить заказ</h2>
                
                <input type="text" placeholder="Введите ФИО получателя" name="fio" value="<?= $_SESSION['user_name'] ?? '' ?>" required>
                <input type="text" placeholder="Введите номер телефона" name="phone" required>
                <input type="text" placeholder="Введите адрес" name="address" required>
                
                <label>Дата доставки</label>
                <input type="date" name="delivery_date" min="<?= date('Y-m-d') ?>" required>
                
                <label>Номер карты для оплаты</label>
                <input type="text" placeholder="1234 5678 9012 3456" name="card_number" maxlength="19" required>
                
                <p style="text-align: right; font-weight: bold;">
                    Сумма заказа: <?= number_format(getCartTotal(), 0, '', ' ') ?> ₽
                </p>
                
                <button type="submit">Оформить заказ</button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>