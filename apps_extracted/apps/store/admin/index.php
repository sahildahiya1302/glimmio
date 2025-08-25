<?php
declare(strict_types=1);
session_start();

// Redirect to dashboard if logged in, else to login page
if (isset($_SESSION['user_id'])) {
    header('Location: /admin/dashboard/dashboard.php');
    exit;
} else {
    header('Location: ../backend/auth/login.php');
    exit;
}
