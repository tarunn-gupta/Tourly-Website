<?php
// Local XAMPP database connection. Change these values if your MySQL setup differs.
$host = '127.0.0.1';
$db   = 'tourly_db';
$user = 'root';
$pass = ''; // XAMPP default is usually blank. Set your MySQL password here if you created one.
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
try { $pdo = new PDO($dsn, $user, $pass, $options); }
catch (PDOException $e) { http_response_code(500); header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'Database connection failed. Check api/db.php and import database.sql.']); exit; }
