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
    <style>
        .auth-modal-overlay {
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

        .auth-modal-overlay.open {
            display: flex;
            opacity: 1;
        }

        .auth-modal {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            padding: 40px 35px 35px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            transform: scale(0.95) translateY(10px);
            transition: transform 0.25s ease;
        }

        .auth-modal-overlay.open .auth-modal {
            transform: scale(1) translateY(0);
        }

        .auth-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: #aaa;
            cursor: pointer;
            transition: color 0.2s;
            background: none;
            border: none;
            line-height: 1;
            padding: 0;
            margin: 0;
        }

        .auth-close:hover {
            color: #2e2a21;
        }

        .auth-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 30px;
            background: #f5f5f5;
            border-radius: 12px;
            padding: 4px;
        }

        .auth-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            transition: all 0.25s;
        }

        .auth-tab.active {
            background: white;
            color: #2e2a21;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .auth-form {
            display: none;
            flex-direction: column;
            gap: 0;
            padding: 0;
            margin: 0;
            background: transparent;
            border-radius: 0;
            max-width: 100%;
        }

        .auth-form.active {
            display: flex;
        }

        .auth-form h2 {
            color: #2e2a21;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .auth-form .auth-subtitle {
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .auth-form input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
            background: #fafafa;
            margin: 0 0 15px;
        }

        .auth-form input:focus {
            border-color: #677964;
            background: white;
        }

        .auth-form button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: #677964;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin: 5px 0 0;
        }

        .auth-form button[type="submit"]:hover {
            background: #556652;
        }

        .auth-form button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .auth-error {
            padding: 10px 14px;
            background: #fef2f2;
            color: #dc3545;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
            display: none;
            border: 1px solid #fecaca;
        }

        .auth-success {
            padding: 14px;
            background: #f0fdf4;
            color: #166534;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
            display: none;
            border: 1px solid #bbf7d0;
        }

        .auth-hint {
            color: #888;
            font-size: 12px;
            text-align: center;
            margin: -10px 0 15px;
        }

        @media (max-width: 480px) {
            .auth-modal {
                margin: 20px;
                padding: 30px 25px 25px;
            }
        }
    </style>
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
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Профиль</a>
        <?php else: ?>
            <a href="#" id="profile-open-btn">Профиль</a>
        <?php endif; ?>

        <?php
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            echo '<a href="admin.php" style="color: #ecde8e;">Админ панель</a>';
        }
        if (isset($_SESSION['user_name'])) {
            echo '<a href="logout.php">Выйти</a>';
        } else {
            echo '<a href="#" id="auth-open-btn">Войти</a>';
        }
        ?>
    </nav>
</header>

<div class="auth-modal-overlay" id="auth-modal">
    <div class="auth-modal">
        <button class="auth-close" id="auth-close-btn">&times;</button>

        <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login">Вход</button>
            <button class="auth-tab" data-tab="register">Регистрация</button>
        </div>

        <form class="auth-form active" id="auth-login-form">
            <h2>Добро пожаловать!</h2>
            <p class="auth-subtitle">Войдите, чтобы продолжить</p>
            <div class="auth-error" id="login-error"></div>
            <input type="text" placeholder="Логин" name="login" required autocomplete="username">
            <input type="password" placeholder="Пароль" name="pass" required autocomplete="current-password">
            <button type="submit">Войти</button>
        </form>

        <form class="auth-form" id="auth-register-form">
            <h2>Создать аккаунт</h2>
            <p class="auth-subtitle">Станьте частью Butoshka</p>
            <div class="auth-error" id="register-error"></div>
            <div class="auth-success" id="register-success"></div>
            <input type="text" placeholder="Логин" name="login" required minlength="3" autocomplete="username">
            <input type="text" placeholder="Телефон" name="phone" required autocomplete="tel">
            <input type="email" placeholder="Email" name="email" required autocomplete="email">
            <input type="password" placeholder="Пароль" name="pass" required minlength="8" autocomplete="new-password">
            <input type="password" placeholder="Повторите пароль" name="reppass" required minlength="8" autocomplete="new-password">
            <p class="auth-hint">Пароль: минимум 8 символов, буквы и цифры</p>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>

<script>
function toggleMenu() {
    document.querySelector('.nav-menu').classList.toggle('active');
}

(function() {
    const modal = document.getElementById('auth-modal');
    const openBtn = document.getElementById('auth-open-btn');
    const closeBtn = document.getElementById('auth-close-btn');
    const tabs = document.querySelectorAll('.auth-tab');
    const loginForm = document.getElementById('auth-login-form');
    const registerForm = document.getElementById('auth-register-form');
    const loginError = document.getElementById('login-error');
    const registerError = document.getElementById('register-error');
    const registerSuccess = document.getElementById('register-success');

    var profileOpenBtn = document.getElementById('profile-open-btn');

    window.openAuthModal = function() {
        openModal();
    };

    if (profileOpenBtn) {
        profileOpenBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (window.location.search.includes('auth=login')) {
        openModal();
        <?php if (isset($_SESSION['error_message'])): ?>
        loginError.textContent = <?= json_encode($_SESSION['error_message']) ?>;
        loginError.style.display = 'block';
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
        registerSuccess.textContent = <?= json_encode($_SESSION['success_message']) ?>;
        registerSuccess.style.display = 'block';
        setTimeout(function() {
            document.querySelector('.auth-tab[data-tab="login"]').click();
        }, 1500);
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    }

    if (!openBtn) return;

    function openModal() {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        loginError.style.display = 'none';
        registerError.style.display = 'none';
        registerSuccess.style.display = 'none';
    }

    openBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openModal();
    });

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            var target = this.dataset.tab;
            document.querySelectorAll('.auth-form').forEach(function(f) { f.classList.remove('active'); });
            document.getElementById('auth-' + target + '-form').classList.add('active');
            loginError.style.display = 'none';
            registerError.style.display = 'none';
            registerSuccess.style.display = 'none';
        });
    });

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loginError.style.display = 'none';
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Вход...';

        var formData = new FormData(this);
        formData.append('ajax', '1');

        fetch('avt.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Войти';
            if (data.success) {
                window.location.href = data.redirect || 'index.php';
            } else {
                loginError.textContent = data.message || 'Ошибка входа';
                loginError.style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = 'Войти';
            loginError.textContent = 'Ошибка соединения';
            loginError.style.display = 'block';
        });
    });

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        registerError.style.display = 'none';
        registerSuccess.style.display = 'none';
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Регистрация...';

        var formData = new FormData(this);
        formData.append('ajax', '1');

        fetch('reg.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Зарегистрироваться';
            if (data.success) {
                registerSuccess.textContent = data.message || 'Регистрация успешна!';
                registerSuccess.style.display = 'block';
                registerForm.querySelector('button[type="submit"]').textContent = 'Войти';
                setTimeout(function() {
                    document.querySelector('.auth-tab[data-tab="login"]').click();
                }, 1000);
            } else {
                registerError.textContent = data.message || 'Ошибка регистрации';
                registerError.style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = 'Зарегистрироваться';
            registerError.textContent = 'Ошибка соединения';
            registerError.style.display = 'block';
        });
    });
})();
</script>