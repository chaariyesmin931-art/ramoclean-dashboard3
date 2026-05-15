<?php
require_once("config.php");

session_name(SESSION_NAME);
session_start();

/* If not logged in, redirect to login page */
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
?>
