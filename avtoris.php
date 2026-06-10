<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>Авторизация</title>
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <main>
        <div class="wrapper">
        <form action="avt.php" method="post">
            <h2>Авторизация</h2>
            
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <input type="text" placeholder="Введите логин" name="login" required autocomplete="username">
            <input type="password" placeholder="Введите пароль" name="pass" required autocomplete="current-password">
            <button type="submit">Войти</button>
            <p>
               Еще нет аккаунта? <a href="regist.php">Зарегистрироваться</a>
            </p>
        </form>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
