<?php
require_once("config.php");

session_name(SESSION_NAME);
session_start();
session_unset();
session_destroy();

header("Location: login.php?msg=logged_out");
exit();
?>
