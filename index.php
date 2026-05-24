<?php
session_start();
require_once 'config/database.php';

$route = isset($_GET['route']) ? sanitize($_GET['route']) : 'dashboard';
$auth_pages = ['login', 'register'];

if (!isset($_SESSION['user_id']) && !in_array($route, $auth_pages)) {
    header("Location: /auth/login.php");
    exit();
}
if (isset($_SESSION['user_id']) && in_array($route, $auth_pages)) {
    header("Location: /auth/dashboard");
    exit();
}

switch ($route) {
    case 'login': include 'auth/login.php'; break;
    case 'register': include 'auth/register.php'; break;
    case 'logout': include 'auth/logout.php'; break;
    case 'dashboard':
    case 'transaksi':
    case 'kategori':
    case 'profile':
        include 'layouts/header.php';
        include "pages/$route.php";
        break;
    default:
        include 'layouts/header.php';
        echo "<div class='p-8 text-center text-red-500 font-bold'>404 - Halaman Tidak Ditemukan</div>";
        break;
}