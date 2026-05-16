<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');

if (strlen($username) < 3) {
  echo json_encode(['verified' => false, 'message' => 'Ton pseudo doit faire au moins 3 caractères.']);
  exit;
}

if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
  echo json_encode(['verified' => false, 'message' => 'Pseudo invalide. Utilise seulement lettres, chiffres, points, tirets.']);
  exit;
}

error_log("[FOLLOW] $username — " . date('c'));

echo json_encode([
  'verified' => true,
  'message' => 'Follow vérifié ✅ Merci !'
]);
