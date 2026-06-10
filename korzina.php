<?php
require_once 'kor.php';
$cartItems = getCartItems();
$total = getCartTotal();
$count = getCartCount();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/korz.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Корзина - Butoshka</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="shopping-cart">
            <div class="cart-header">
                <h2 class="title">Корзина</h2>
                <?php if ($count > 0): ?>
                    <span class="cart-count">
                        <?php 
                        echo $count . ' '; 
                        if($count % 10 == 1 && $count % 100 != 11) echo 'товар';
                        elseif(in_array($count % 10, [2,3,4]) && !in_array($count % 100, [12,13,14])) echo 'товара';
                        else echo 'товаров';
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <p>Ваша корзина пока пуста</p>
                    <p style="font-size: 14px; color: #999; margin-bottom: 25px;">
                        Добавьте букеты из каталога
                    </p>
                    <a href="index.php#catalog" class="continue-shopping">
                        Перейти к покупкам
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): 
                ?>
                <div class="item" data-id="<?php echo $item['id']; ?>">
                    <button class="delete-btn" onclick="removeItem(<?php echo $item['id']; ?>)" title="Удалить">
                        ✕
                    </button>
                    <div class="item-image">
                        <img src="img_out.php?id=<?php echo (int)$item['id']; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>

                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-price">
                            <?php echo number_format($item['price'], 0, '', ' '); ?> ₽
                        </div>
                    </div>

                    <div class="item-quantity">
                        <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">−</button>
                        <input type="text" class="qty-input" value="<?php echo $item['quantity']; ?>" readonly>
                        <button class="qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                    </div>

                    <div class="item-total">
                        <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="total-amount">
                        Итого: <span><?php echo number_format($total, 0, '', ' '); ?>₽</span>
                    </div>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<a href="#" class="checkout-btn" onclick="openCheckoutModal(); return false;">Оформить заказ</a>';
                    } else {
                        echo '<a href="#" class="checkout-btn" onclick="openAuthModal(); return false; style="max-width: 600px;"">Авторизоваится</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

<div class="checkout-modal-overlay" id="checkout-modal">
    <div class="checkout-modal">
        <button class="checkout-close" id="checkout-close-btn">&times;</button>
        <h2>Оформление заказа</h2>

        <div id="checkout-success" style="display:none; text-align:center; padding:30px 0;">
            <div style="font-size:60px; color:#677964; margin-bottom:20px;">&#10003;</div>
            <h3 style="color:#2e2a21; margin-bottom:10px;">Заказ успешно оформлен!</h3>
            <p style="color:#666; margin-bottom:25px;">Спасибо за покупку! Мы скоро свяжемся с вами.</p>
            <a href="profile.php" class="checkout-btn" style="display:inline-block; text-decoration:none; width: auto;">Мои заказы</a>
            <a href="index.php" class="checkout-btn" style="display:inline-block; text-decoration:none; width: auto; background:#2e2a21; margin-left:10px;">На главную</a>
        </div>

        <form id="checkout-form">
            <div class="checkout-error" id="checkout-error"></div>

            <div class="checkout-field">
                <label>ФИО получателя</label>
                <input type="text" name="fio" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required placeholder="Иванов Иван Иванович">
            </div>
            <div class="checkout-field">
                <label>Номер телефона</label>
                <input type="text" name="phone" required placeholder="+7 (999) 999-99-99">
            </div>
            <div class="checkout-field">
                <label>Адрес доставки</label>
                <input type="text" name="address" required placeholder="Улица, дом, квартира">
            </div>
            <div class="checkout-field">
                <label>Дата доставки</label>
                <input type="date" name="delivery_date" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="checkout-field">
                <label>Номер карты для оплаты</label>
                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
            </div>

            <div class="checkout-total">
                Сумма заказа: <span id="checkout-total-amount"><?= number_format($total, 0, '', ' ') ?> ₽</span>
            </div>

            <button type="submit" class="checkout-btn" id="checkout-submit-btn">Оформить заказ</button>
        </form>
    </div>
</div>

<style>
.checkout-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.25s ease;
}

.checkout-modal-overlay.open {
    display: flex;
    opacity: 1;
}

.checkout-modal {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 35px;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    transform: scale(0.95) translateY(10px);
    transition: transform 0.25s ease;
}

.checkout-modal-overlay.open .checkout-modal {
    transform: scale(1) translateY(0);
}

.checkout-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    color: #aaa;
    cursor: pointer;
    background: none;
    border: none;
    line-height: 1;
    padding: 0;
}

.checkout-close:hover {
    color: #2e2a21;
}

.checkout-modal h2 {
    color: #2e2a21;
    font-size: 24px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #677964;
}

.checkout-field {
    margin-bottom: 18px;
}

.checkout-field label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2e2a21;
    font-size: 14px;
}

.checkout-field input {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s;
    background: #fafafa;
    box-sizing: border-box;
}

.checkout-field input:focus {
    border-color: #677964;
    background: white;
}

.checkout-total {
    text-align: right;
    font-size: 18px;
    font-weight: bold;
    color: #677964;
    margin: 20px 0;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.checkout-btn {
    padding: 14px;
    background: #677964;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    text-align: center;
}

.checkout-btn:hover {
    background: #556652;
}

.checkout-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.checkout-error {
    padding: 10px 14px;
    background: #fef2f2;
    color: #dc3545;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    display: none;
    border: 1px solid #fecaca;
}
</style>

    <script>
    function openCheckoutModal() {
        document.getElementById('checkout-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
        document.getElementById('checkout-form').style.display = '';
        document.getElementById('checkout-success').style.display = 'none';
        document.getElementById('checkout-error').style.display = 'none';
    }

    function closeCheckoutModal() {
        document.getElementById('checkout-modal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('checkout-modal');
        const closeBtn = document.getElementById('checkout-close-btn');
        const form = document.getElementById('checkout-form');
        const errorDiv = document.getElementById('checkout-error');
        const submitBtn = document.getElementById('checkout-submit-btn');

        closeBtn.addEventListener('click', closeCheckoutModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeCheckoutModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeCheckoutModal();
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Оформляем...';

            var formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('zak.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Оформить заказ';
                if (data.success) {
                    form.style.display = 'none';
                    document.getElementById('checkout-success').style.display = 'block';
                    var counter = document.querySelector('.cart-counter');
                    if (counter) counter.textContent = '0';
                } else {
                    errorDiv.textContent = data.message || 'Ошибка оформления заказа';
                    errorDiv.style.display = 'block';
                }
            })
            .catch(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Оформить заказ';
                errorDiv.textContent = 'Ошибка соединения';
                errorDiv.style.display = 'block';
            });
        });
    });
    </script>

    <script>
    function updateQuantity(productId, change) {
        const item = document.querySelector(`.item[data-id="${productId}"]`);
        const input = item.querySelector('.qty-input');
        let newQuantity = parseInt(input.value) + change;

        if (newQuantity < 1) {
            if (confirm('Удалить товар из корзины?')) {
                removeItem(productId);
            }
            return;
        }

        if (newQuantity > 20) {
            newQuantity = 20;
            alert('Максимальное количество - 20 шт');
        }

        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', productId);
        formData.append('quantity', newQuantity);
        
        fetch('kor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = newQuantity;
                location.reload();
            } else {
                alert('Достигнут лимит 20 товаров в корзине!');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при обновлении количества');
        });
    }
    
    function removeItem(productId) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('id', productId);
        
        fetch('kor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при удалении товара');
        });
    }
    </script>
</body>
</html>