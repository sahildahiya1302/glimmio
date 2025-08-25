<?php
declare(strict_types=1);
session_start();
log_activity($_SESSION["user_id"] ?? null, "logout", ["ip" => $_SERVER["REMOTE_ADDR"] ?? ""]);
session_destroy();
header('Location: /backend/auth/login.php');
exit;
