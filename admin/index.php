<?php
/**
 * Admin Panel Root - Index.php
 * admin.ecedekor.com.tr/index.php
 */

require_once 'includes/config.php';

// Giriş yapılmışsa dashboard'a, yapılmamışsa login'e yönlendir
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>