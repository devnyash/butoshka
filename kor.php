<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('CART_LIMIT', 20);

function initCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function canAddToCart($quantity = 1) {
    return getCartCount() + $quantity <= CART_LIMIT;
}

function addToCart($productId, $name, $price, $image) {
    initCart();
    
    if (!canAddToCart(1)) {
        return false;
    }
    
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            if (!canAddToCart($item['quantity'])) {
                return false;
            }
            $item['quantity']++;
            return true;
        }
    }
    
    $_SESSION['cart'][] = [
        'id' => $productId,
        'name' => $name,
        'price' => (float)$price,
        'image' => $image,
        'quantity' => 1
    ];
    return true;
}

function removeFromCart($productId) {
    initCart();
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($productId) {
        return $item['id'] != $productId;
    }));
}

function updateQuantity($productId, $quantity) {
    initCart();
    $quantity = max(1, min(20, (int)$quantity));
    $currentQty = 0;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $productId) {
            $currentQty = $item['quantity'];
            break;
        }
    }
    $diff = $quantity - $currentQty;
    if ($diff > 0 && !canAddToCart($diff)) {
        return false;
    }
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] = $quantity;
            break;
        }
    }
    return true;
}

function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

function getCartTotal() {
    initCart();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['id'], $_POST['name'], $_POST['price'])) {
                $added = addToCart(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['price'],
                    $_POST['image'] ?? ''
                );
                echo json_encode([
                    'success' => $added,
                    'count' => getCartCount(),
                    'total' => getCartTotal(),
                    'limit' => CART_LIMIT,
                    'limitReached' => !$added
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
            }
            break;
            
        case 'remove':
            if (isset($_POST['id'])) {
                removeFromCart($_POST['id']);
                echo json_encode([
                    'success' => true,
                    'count' => getCartCount(),
                    'total' => getCartTotal()
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
            break;
            
        case 'update':
            if (isset($_POST['id'], $_POST['quantity'])) {
                $updated = updateQuantity($_POST['id'], $_POST['quantity']);
                echo json_encode([
                    'success' => $updated,
                    'total' => getCartTotal(),
                    'count' => getCartCount()
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
            break;
            
        case 'get':
            echo json_encode([
                'success' => true,
                'items' => getCartItems(),
                'count' => getCartCount(),
                'total' => getCartTotal()
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
    exit;
}
?>