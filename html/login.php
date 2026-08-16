<?php

// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.html';                        // After all this we want to go back to the same page, so lets store where we came from

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
// get username if exists, else use '' as user name (and fail hopefully)
    $username = $_POST['username'] ?? '';
    // get password if exists, else use ''
    $password = $_POST['password'] ?? '';

    // Fill message with information ready to send to Node.js
    $message = json_encode([
        'username' => $username,
        'password' => $password
    ]);

    $curl = curl_init('http://127.0.0.1:3000/login');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                               // Expect a return
    curl_setopt($curl, CURLOPT_POST, true);                                         // Set action POST to true
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);     // Set header to say message is JSON
    curl_setopt($curl, CURLOPT_POSTFIELDS, $message);                               // Attach message containing username and password
    
    $returnTransfer = curl_exec($curl);                                             // Get return message from NodeJS
    curl_close($curl);

    $response = json_decode($returnTransfer, true);                                 // Decode message, as an array
    
    if (isset($response['success']) && $response['success'] === true) {             // did we receive a message and did the login work
        $_SESSION['uid'] = $response['uid'];
        $_SESSION['username'] = $response['username'];
        header("Location: " . $redirectUrl);                                        // Take the user back to where they were
        exit();
    } else {
        $_SESSION['login_error'] = $response['message'] ?? 'Incorrect User name or Password';
        header("Location: " . $redirectUrl);                                        // If login failed, still take them back to where they were
        exit();
    }
    
    
 }