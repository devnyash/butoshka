<?php
require_once ('db.php');
session_start();

$login = $_POST['login']; 
$phone = $_POST['phone'];
$email = $_POST['email'];
$pass = $_POST['pass'];
$reppass = $_POST['reppass'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Неверный формат email";
    header('Location: regist.php');
    exit;
}

if(empty($login) || empty($phone) || empty($email) || empty($pass) || empty($reppass)){
    $_SESSION['error_message'] = "Заполните все поля";
    header('Location: regist.php');
    exit;
} else {
    if($pass != $reppass){
        $_SESSION['error_message'] = "Пароли не совпадают";
        header('Location: regist.php');
        exit;
    } else {
        $check_sql = "SELECT id FROM users WHERE login = '$login' OR email = '$email'";
        $check_result = $conn->query($check_sql);
        if($check_result->num_rows > 0){
            $_SESSION['error_message'] = "Пользователь с таким логином или email уже существует";
            header('Location: regist.php');
            exit;
        } else {
            $sql = "INSERT INTO `users` (`login`, `phone`, `email`, `pass`, `role`) VALUES ('$login', '$phone', '$email', '$pass', 'user')";
            if($conn->query($sql) === TRUE){
                $_SESSION['success_message'] = "Регистрация успешна! Теперь вы можете войти";
                header('Location: avtoris.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Ошибка: " . $conn->error;
                header('Location: regist.php');
                exit;
            }
        }
    }
}
?>