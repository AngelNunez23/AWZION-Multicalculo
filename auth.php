<?php
session_start();

if (!isset($_SESSION["admin_logado"])) {
    header("Location: login.php");
    exit();
}
?>