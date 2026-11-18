<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Allow the VS Code Live Server preview to call the PHP API served by XAMPP.
// Only local development origins on Live Server's usual ports are accepted.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('#^https?://(localhost|127\.0\.0\.1):55[0-9]{2}$#', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'POST requests only.']); exit; }
require __DIR__ . '/db.php';
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid form data.']); exit; }
$type = preg_replace('/[^a-z_]/', '', (string)($data['form_type'] ?? ''));
$payload = $data['details'] ?? [];
if (!is_array($payload)) $payload = [];
$name = trim((string)($payload['fullName'] ?? $payload['name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$phone = trim((string)($payload['mobile'] ?? $payload['phone'] ?? ''));
if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Please enter a name and a valid email address.']); exit; }
try {
 $stmt = $pdo->prepare('INSERT INTO submissions (form_type, full_name, email, phone, details_json) VALUES (:type, :name, :email, :phone, :details)');
 $stmt->execute([':type'=>$type ?: 'general', ':name'=>$name, ':email'=>$email, ':phone'=>$phone, ':details'=>json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
 echo json_encode(['success'=>true,'message'=>'Your details have been saved successfully.','id'=>(int)$pdo->lastInsertId()]);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Could not save your details. Please try again.']); }
