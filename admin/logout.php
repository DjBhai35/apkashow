<?php
/**
 * CinemaVault - Admin Logout
 */
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION[ADMIN_SESSION_KEY]);
session_regenerate_id(true);

header("Location: " . BASE_URL . "/admin/login.php");
exit();
