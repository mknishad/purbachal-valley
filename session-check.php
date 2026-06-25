<?php
session_start();
require_once 'config.php';

echo "Session Data:<br>";
print_r($_SESSION);
?>