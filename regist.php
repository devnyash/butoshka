<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Регистрация</title>
    <style>
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .password-hint {
            color: #2e2a21;
            text-align: center;
            font-size: 13px;
            margin: 6px 8px 0;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <main>
        <div class="wrapper">
        <form action="reg.php" method="post">
            <h2>Регистрация</h2>
            
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            <input type="text" placeholder="Введите логин" name="login" required minlength="3" autocomplete="username">
            <input type="text" placeholder="Введите номер телефона" name="phone" required autocomplete="tel">
            <input type="email" placeholder="Введите почту" name="email" required autocomplete="email">
            <input type="password" placeholder="Введите пароль" name="pass" required minlength="8" autocomplete="new-password">
            <input type="password" placeholder="Повторите пароль" name="reppass" required minlength="8" autocomplete="new-password">
            <div class="password-hint">Пароль: минимум 8 символов, буквы и цифры</div>
            <button type="submit">Зарегистрироваться</button>
            <p>
                Уже есть аккаунт? <a href="avtoris.php">Авторизоваться</a>
            </p>
        </form>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
