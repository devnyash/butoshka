<?php
require_once 'db.php';
require_once 'auth_helpers.php';
session_start();

$login = trim($_POST['login'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['pass'] ?? '';
$reppass = $_POST['reppass'] ?? '';

$error = '';

if (empty($login) || empty($phone) || empty($email) || empty($pass) || empty($reppass)) {
    $error = "Заполните все поля";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Неверный формат email";
} elseif (mb_strlen($login) < 3) {
    $error = "Логин должен содержать минимум 3 символа";
} elseif ($pass !== $reppass) {
    $error = "Пароли не совпадают";
} elseif (!isPasswordStrong($pass)) {
    $error = "Пароль должен быть не короче 8 символов и содержать буквы и цифры";
}

if (!$error) {
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
    $checkStmt->bind_param('ss', $login, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Пользователь с таким логином или email уже существует";
    }
}

if ($error) {
    if ($_POST['ajax'] ?? '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
    $_SESSION['error_message'] = $error;
    header('Location: regist.php');
    exit;
}

$passwordHash = password_hash($pass, PASSWORD_DEFAULT);
$insertStmt = $conn->prepare(
    "INSERT INTO users (login, phone, email, pass, role) VALUES (?, ?, ?, ?, 'user')"
);
$insertStmt->bind_param('ssss', $login, $phone, $email, $passwordHash);

if ($insertStmt->execute()) {
    if ($_POST['ajax'] ?? '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Регистрация успешна! Теперь вы можете войти.']);
        exit;
    }
    $_SESSION['success_message'] = "Регистрация успешна! Теперь вы можете войти";
    header('Location: index.php?auth=login');
    exit;
}

$error = "Ошибка: " . $conn->error;
if ($_POST['ajax'] ?? '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}
$_SESSION['error_message'] = $error;
header('Location: regist.php');
exit;
