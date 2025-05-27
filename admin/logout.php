<?php
require_once '../includes/config.php';

// Update session log if user is logged in
if (isAdminLoggedIn()) {
    query("UPDATE user_sessions SET logout_time = NOW(), is_active = 0 WHERE user_id = ? AND is_active = 1", 
          [$_SESSION['admin_id']]);
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . ADMIN_URL . '/login.php');
exit;
?>
