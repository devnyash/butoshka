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

$categories = [
    'all' => 'Все букеты',
    'roses' => 'Розы',
    'tulips' => 'Тюльпаны',
    'peonies' => 'Пионы',
    'hydrangeas' => 'Гортензии',
    'lilies' => 'Лилии',
    'carnations' => 'Гвоздики',
    'chrysanthemums' => 'Хризантемы',
    'mimosa' => 'Мимоза',
    'mixed' => 'Сборные',
];
?>
<!DOCTYPE html>
<html lang="ru">
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
            background: #677964;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
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

        .catalog-shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 20px;
        }

        .catalog-title {
            text-align: center;
            color: #2e2a21;
            font-size: 34px;
            margin-bottom: 14px;
        }

        .catalog-subtitle {
            text-align: center;
            color: #677964;
            max-width: 700px;
            margin: 0 auto 26px;
            line-height: 1.5;
        }

        .catalog-filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .filter-pill {
            border: none;
            border-radius: 999px;
            padding: 11px 20px;
            background: #e8ede4;
            color: #2e2a21;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .filter-pill:hover,
        .filter-pill.active {
            background: #677964;
            color: #fff;
            transform: translateY(-2px);
        }

        .card-category {
            display: inline-flex;
            align-self: flex-start;
            margin-bottom: 12px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #eef3eb;
            color: #677964;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
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

        .catalog-empty {
            display: none;
            text-align: center;
            color: #677964;
            padding: 10px 20px 40px;
            font-size: 16px;
        }

        .catalog-sort {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .sort-label {
            font-size: 14px;
            color: #888;
            font-weight: 500;
        }

        .sort-pill {
            border: none;
            border-radius: 999px;
            padding: 8px 18px;
            background: #f0f0f0;
            color: #666;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sort-pill:hover {
            background: #e0e0e0;
            color: #2e2a21;
        }

        .sort-pill.active {
            background: #677964;
            color: #fff;
        }

        @media (max-width: 768px) {
            .catalog-title {
                font-size: 28px;
            }

            .catalog-shell {
                padding: 0 15px 15px;
            }

            .filter-pill {
                font-size: 14px;
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>
    

    <main>
        <div class="wrapper">
            <section class="intro">
                <div class="intro-content">
                    <h1>Добро пожаловать в Butoshka</h1>
                    <p>Собираем букеты под настроение: от классических роз до ярких сезонных миксов с доставкой в удобное для вас время.</p>
                </div>
            </section>
        </div>

        <section class="grid" id="catalog">
            <div class="catalog-shell">
                <h2 class="catalog-title">Каталог букетов</h2>
                <p class="catalog-subtitle">Выбирайте категорию по виду цветов.</p>

                <div class="catalog-filters" id="catalog-filters">
                    <?php foreach ($categories as $slug => $label): ?>
                        <button
                            type="button"
                            class="filter-pill <?php echo $slug === 'all' ? 'active' : ''; ?>"
                            data-filter="<?php echo $slug; ?>"
                        >
                            <?php echo htmlspecialchars($label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="catalog-sort" id="catalog-sort">
                    <span class="sort-label">Сортировка:</span>
                    <button type="button" class="sort-pill active" data-sort="default">По умолчанию</button>
                    <button type="button" class="sort-pill" data-sort="price-asc">Сначала дешёвые</button>
                    <button type="button" class="sort-pill" data-sort="price-desc">Сначала дорогие</button>
                </div>
            </div>

            <div class="cards" id="catalog-cards">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1/-1;">Товаров пока нет</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $categorySlug = $product['category'] ?? 'mixed';
                        $categoryLabel = $categories[$categorySlug] ?? $categories['mixed'];
                        ?>
                        <div class="card" data-category="<?php echo htmlspecialchars($categorySlug); ?>" data-price="<?php echo (float)$product['price']; ?>">
                            <div class="top">
                                <div class="image">
                                    <img src="img_out.php?id=<?php echo (int)$product['id']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                            </div>
                            <div class="bottom">
                                <span class="card-category"><?php echo htmlspecialchars($categoryLabel); ?></span>
                                <div class="prices">
                                    <span class="flow"><?php echo htmlspecialchars($product['name']); ?></span>
                                    <span class="price"><?php echo number_format((float)$product['price'], 0, '', ' '); ?> ₽</span>
                                </div>
                                <div class="title"><?php echo htmlspecialchars($product['description']); ?></div>
                                <button
                                    class="add-to-cart"
                                    data-id="<?php echo (int)$product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                    data-image="<?php echo (int)$product['id']; ?>"
                                >
                                    В корзину
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p class="catalog-empty" id="catalog-empty">В этой категории пока нет букетов. Выберите другую кнопку и посмотрим ещё.</p>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>

    <div id="notification" class="notification"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButtons = document.querySelectorAll('.add-to-cart');
        const filterButtons = document.querySelectorAll('.filter-pill');
        const cards = document.querySelectorAll('.card[data-category]');
        const emptyState = document.getElementById('catalog-empty');
        
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
                .catch(() => {
                    showNotification('Ошибка соединения', 'error');
                });
            });
        });

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                let visibleCount = 0;

                filterButtons.forEach(item => item.classList.remove('active'));
                this.classList.add('active');

                cards.forEach(card => {
                    const shouldShow = filter === 'all' || card.dataset.category === filter;
                    card.style.display = shouldShow ? '' : 'none';

                    if (shouldShow) {
                        visibleCount++;
                    }
                });

                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                applySort();
            });
        });

        const sortButtons = document.querySelectorAll('.sort-pill');
        const cardsContainer = document.getElementById('catalog-cards');
        let currentSort = 'default';

        function applySort() {
            const visibleCards = Array.from(cards).filter(c => c.style.display !== 'none');

            if (currentSort === 'price-asc') {
                visibleCards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
            } else if (currentSort === 'price-desc') {
                visibleCards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
            } else {
                visibleCards.sort((a, b) => Array.from(cards).indexOf(a) - Array.from(cards).indexOf(b));
            }

            visibleCards.forEach(c => cardsContainer.appendChild(c));
        }

        sortButtons.forEach(button => {
            button.addEventListener('click', function() {
                sortButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentSort = this.dataset.sort;
                applySort();
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
