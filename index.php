<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/grid.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Butoshka - Магазин букетов</title>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #67796485;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.error {
            background: #998688;
        }
        
        .cart-counter {
            background: #70a161;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
            min-width: 18px;
            text-align: center;
            display: inline-block;
        }

        .add-to-cart.added {
            background-color: #677964 !important;
            color: #fff !important;
        }
        
        .add-to-cart {
            background-color: #2e2a21;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            margin-top: 10px;
            width: 100%;
        }
        
        .add-to-cart:hover {
            background-color: #677964;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php 
    require_once 'kor.php';
    require_once 'db.php';
  
    $products = [];
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    include 'header.php';
    ?>

    <main>
        <div class="wrapper">
            <section class="intro">
                <div class="intro-content">
                    <h1>Добро пожаловать в Butoshka</h1>
                    <p>Мы создаём самые красивые букеты по доступным ценам. Доставка в удобное для вас время.</p>
                </div>
            </section>
        </div>

        <section class="grid" id="catalog">
            <h2 class="catalog-title">Каталог букетов</h2>
            <div class="cards">
<?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1/-1;">Товаров пока нет</p>
                <?php else: ?>
                    <?php foreach ($products as $product): 
                        $image = !empty($product['image']) ? $product['image'] : 'placeholder.jpg';
                    ?>
                    <div class="card">
                        <div class="top">
                            <div class="image">
                                <img src="img/<?php echo htmlspecialchars($image); ?>" 
                                     alt="<?php echo $product['name']; ?>">
                            </div>
                        </div>  
                        <div class="bottom">  
                            <div class="prices">
                                <span class="flow"><?php echo $product['name']; ?></span>
                                <span class="price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                            </div>
                            <div class="title"><?php echo $product['description']; ?></div>
                            <button class="add-to-cart" 
                                    data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo $product['name']; ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-image="<?php echo $image; ?>">
                                В корзину
                            </button>
                        </div>  
                    </div>  
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>

    <div id="notification" class="notification"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButtons = document.querySelectorAll('.add-to-cart');
        
        addButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
         
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = this.dataset.price;
                const productImage = this.dataset.image;
                
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('id', productId);
                formData.append('name', productName);
                formData.append('price', productPrice);
                formData.append('image', productImage);
               
                fetch('kor.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Товар добавлен в корзину!');
                        updateCartCounter(data.count);
                        
                        this.classList.add('added');
                        this.textContent = 'Добавлено';
                        
                        setTimeout(() => {
                            this.classList.remove('added');
                            this.textContent = 'В корзину';
                        }, 1500);
                    } else if (data.limitReached) {
                        showNotification('Достигнут лимит ' + data.limit + ' товаров в корзине!', 'error');
                    } else {
                        showNotification('Ошибка при добавлении', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Ошибка соединения', 'error');
                });
            });
        });
        
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification show ' + type;
            
            setTimeout(() => {
                notification.className = 'notification';
            }, 3000);
        }
        
        function updateCartCounter(count) {
            const cartLink = document.querySelector('a[href="korzina.php"]');
            if (cartLink) {
                const oldCounter = cartLink.querySelector('.cart-counter');
                if (oldCounter) oldCounter.remove();
                const counter = document.createElement('span');
                counter.className = 'cart-counter';
                counter.textContent = count;
                cartLink.appendChild(counter);
            }
        }
        
        const formData = new FormData();
        formData.append('action', 'get');
        
        fetch('kor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count) {
                updateCartCounter(data.count);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    });
    </script>
</body>
</html>