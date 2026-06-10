<?php

function isPasswordStrong(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Za-zА-Яа-яЁё]/u', $password)
        && preg_match('/\d/', $password);
}

function isPasswordHashStored(string $passwordValue): bool
{
    return password_get_info($passwordValue)['algo'] !== null;
}

function verifyAndUpgradePassword(mysqli $conn, array $user, string $plainPassword): bool
{
    $storedPassword = $user['pass'] ?? '';

    if ($storedPassword === '') {
        return false;
    }

    if (isPasswordHashStored($storedPassword)) {
        if (!password_verify($plainPassword, $storedPassword)) {
            return false;
        }

        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
            $stmt->bind_param('si', $newHash, $user['id']);
            $stmt->execute();
        }

        return true;
    }

    if (!hash_equals($storedPassword, $plainPassword)) {
        return false;
    }

    $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
    $stmt->bind_param('si', $newHash, $user['id']);
    $stmt->execute();

    return true;
}
