<?php

$host = "localhost";
$database = "hotel_booking_system"; 
$charset = "utf8mb4";
$username ="root";
$db_password = "";

$dsn = "mysql:host=$host;dbname=$database;charset=$charset";

$pdo = new PDO($dsn , $username , $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
