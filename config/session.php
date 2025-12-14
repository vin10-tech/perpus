
<?php
define('BASE_URL', '/perpus');
session_start();

function isLoggedIn() {
    return isset($_SESSION['id_petugas']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function login($id_petugas, $username, $nama_petugas) {
    $_SESSION['id_petugas'] = $id_petugas;
    $_SESSION['username'] = $username;
    $_SESSION['nama_petugas'] = $nama_petugas;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed");
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
