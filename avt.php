<?php
require_once 'db.php';
require_once 'auth_helpers.php';
session_start();

$login = trim($_POST['login'] ?? '');
$pass = $_POST['pass'] ?? '';

$error = '';

if (empty($login) || empty($pass)) {
    $error = "Заполните все поля";
}

if (!$error) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE login = ? LIMIT 1");
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !verifyAndUpgradePassword($conn, $user, $pass)) {
        $error = "Неверный логин или пароль";
    }
}

if ($error) {
    if ($_POST['ajax'] ?? '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
    $_SESSION['error_message'] = $error;
    header('Location: index.php?auth=login');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['login'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

if ($_POST['ajax'] ?? '') {
    header('Content-Type: application/json');
    $redirect = ($user['role'] === 'admin') ? 'admin.php' : 'index.php';
    echo json_encode(['success' => true, 'redirect' => $redirect]);
    exit;
}

if ($user['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

header('Location: index.php');
exit;
