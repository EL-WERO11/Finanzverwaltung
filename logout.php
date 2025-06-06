<?php
session_start();

// Alle Session-Variablen löschen
$_SESSION = array();

// Session zerstören
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Zur Login-Seite umleiten
header("Location: login.php");
exit;
?>