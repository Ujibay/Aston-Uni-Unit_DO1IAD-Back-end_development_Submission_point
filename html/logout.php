<?php

// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Empty browser session information from server
$_SESSION = [];

// Destroy cookie by setting the time to the past, causing browser cleanup, 
// keep path and other settings so not to accidently create a new cookie in a different path or for a different domain.
if (ini_get("session.use_cookies")) {
    $parameters = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parameters["path"],
        $parameters["domain"],
        $parameters["secure"],
        $params["httponly"]
    );
}

// Kill server session file, we are done with this.
session_destroy();

// And lets go back to the main page.
header("location: index.php");
exit();