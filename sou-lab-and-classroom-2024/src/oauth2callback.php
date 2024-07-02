<?php
require 'vendor/autoload.php';
include 'googleConfig.php';
include 'config.php'; // Include your database configuration

use League\OAuth2\Client\Provider\Google;

session_start();
$provider = new Google($googleConfig);

if (!empty($_GET['error'])) {
    exit('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'));
} elseif (empty($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $ownerDetails = $provider->getResourceOwner($token);

    $email = $ownerDetails->getEmail();

    if (strpos($email, '@sou.edu') !== false) {
        $_SESSION['email'] = $email;

        try {
            // Create a new PDO instance
            $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare and execute a query to check if the user exists in AdminAccess table and if they are SuperAdmin
            $sth = $con->prepare("SELECT SuperAdmin FROM AdminAccess WHERE UserName = :email");
            $sth->bindParam(':email', $email, PDO::PARAM_STR);
            $sth->execute();
            $result = $sth->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and if they are SuperAdmin
            if ($result) {
                $_SESSION['isAdmin'] = $result['SuperAdmin'] ? true : false;
            } else {
                $_SESSION['isAdmin'] = false;
            }

            header('Location: main.php'); // Adjust this redirect location if needed
            exit;
        } catch (PDOException $e) {
            // Handle any errors that occur during the database connection and query execution
            echo "Database error: " . $e->getMessage();
            exit;
        }
    } else {
        // Redirect to Guest/landing.html for non-sou.edu emails
        header('Location: /Guest/landing.html'); // Adjust this path if needed
        exit;
    }
}
?>