<?php
require_once('db.php');
session_start();

$login = $_POST['login'];
$pass = $_POST['pass'];

if(empty($login) || empty($pass)){
    $_SESSION['error_message'] = "Заполните все поля";
    header('Location: avtoris.php');
    exit;
} else {
    $sql_user = "SELECT * FROM users WHERE login = '$login' AND pass = '$pass'";
    $result_user = $conn->query($sql_user);
    
    if($result_user->num_rows > 0){
        $row = $result_user->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['login'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_role'] = $row['role'];
        
        if($row['role'] == 'admin'){
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $_SESSION['error_message'] = "Пользователь не найден";
        header('Location: avtoris.php');
        exit;
    }
}
?>