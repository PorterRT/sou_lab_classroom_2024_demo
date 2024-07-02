<?php
// google_config.php

$googleConfig = [
    'clientId' => getenv('GOOGLE_CLIENT_ID'),
    'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
    'redirectUri' => 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php',
];
?>
