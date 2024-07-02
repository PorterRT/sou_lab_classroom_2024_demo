<?php
session_start();

function ensureAuthenticated() {
    // Check if the user is authenticated
    if (!isset($_SESSION['email'])) {
        header('Location: /oauth2callback.php'); // Redirect to OAuth callback if not authenticated
        exit;
    }

    // Check if the user is not an admin and is trying to access Admin.php
    if (basename($_SERVER['PHP_SELF']) == 'Admin.php' && !$_SESSION['isAdmin']) {
        header('Location: /main.php'); // Redirect to guest landing page if user is not an admin
        exit;
    }
}

// Call the function to ensure authentication
ensureAuthenticated();
?>