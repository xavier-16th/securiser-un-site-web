<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require admin access - call this at top of Create, Update, Delete pages
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
    
    if (!isAdmin()) {
        die("Access denied. Only administrators can perform this action.");
    }
}

// Require login only - call this for Read pages
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}
?>
