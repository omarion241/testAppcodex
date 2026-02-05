<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /entrepot/list.php');
    exit;
}

header('Location: /auth/login.php');
exit;
