<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница не найдена - Butoshka</title>
    <link rel="icon" href="img/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error-container {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .error-content {
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #677964;
            margin-bottom: 20px;
            line-height: 1;
        }
        
        .error-title {
            font-size: 28px;
            color: #2e2a21;
            margin-bottom: 15px;
        }
        
        .error-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .error-btn {
            display: inline-block;
            background: #677964;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .error-btn:hover {
            background: #556652;
            transform: translateY(-2px);
        }
        
        @media (max-width: 576px) {
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 22px;
            }
            
            .error-text {
                font-size: 14px;
            }
            
            .error-btn {
                padding: 10px 25px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <?php 
    http_response_code(404);
    include 'header.php'; 
    ?>
    
    <main>
        <div class="error-container">
            <div class="error-content">
                <div class="error-code">404</div>
                <h1 class="error-title">Страница не найдена</h1>
                <p class="error-text">
                    К сожалению, страница, которую вы ищете, не существует или была перемещена.
                </p>
                <a href="index.php" class="error-btn">Вернуться на главную</a>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>