<?php
require_once('db.php');
require_once('kor.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: avtoris.php');
    exit;
}

if (getCartCount() == 0) {
    header('Location: korzina.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$fio = $_POST['fio'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$delivery_date = $_POST['delivery_date'];
$card_number = $_POST['card_number'];
$total = getCartTotal();
$items = getCartItems();

$conn->begin_transaction();

try {
    $sql = "INSERT INTO orders (user_id, address, order_price, delivery_date, card_number, recipient_name, recipient_phone) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdssss", $user_id, $address, $total, $delivery_date, $card_number, $fio, $phone);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    foreach ($items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt_items->execute();
    }

    $conn->commit();

    $_SESSION['cart'] = [];

    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='css/style.css'>
        <title>Заказ оформлен</title>
        <style>
            .success-container {
                max-width: 600px;
                margin: 100px auto;
                text-align: center;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .success-icon {
                font-size: 80px;
                color: #677964;
                margin-bottom: 20px;
            }
            .order-number {
                font-size: 24px;
                color: #677964;
                font-weight: bold;
                margin: 20px 0;
            }
            .buttons {
                margin-top: 30px;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                margin: 0 10px;
                background: #677964;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #556652;
            }
        </style>
    </head>
    <body>
        <div class='success-container'>
            <h2>Заказ успешно оформлен!</h2>
            <p>Спасибо за покупку! Мы скоро свяжемся с вами.</p>
            <p>Дата доставки: <strong>" . date('d.m.Y', strtotime($delivery_date)) . "</strong></p>
            <div class='buttons'>
                <a href='my_orders.php' class='btn'>Мои заказы</a>
                <a href='index.php' class='btn'>На главную</a>
            </div>
        </div>
    </body>
    </html>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "Ошибка при оформлении заказа: " . $e->getMessage();
    echo "<br><a href='zakaz.php'>Вернуться</a>";
}
?>