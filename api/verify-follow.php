<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');

if (strlen($username) < 3) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid username (min 3 characters)']);
  exit;
}

$u = urlencode($username);
$yt_key = getenv('YOUTUBE_API_KEY');

function profileExists($url) {
  $ctx = stream_context_create(['http' => [
    'timeout' => 8,
    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
  ]]);
  $html = @file_get_contents($url, false, $ctx);
  if ($html === false) return false;
  $lower = strtolower($html);
  if (strpos($lower, 'page not found') !== false) return false;
  if (strpos($lower, 'cette page n\'existe pas') !== false) return false;
  if (strpos($lower, 'couldn\'t find') !== false) return false;
  if (strpos($lower, 'sorry, this page') !== false) return false;
  return true;
}

$results = [];
$checked = [];

// Instagram
$ig = profileExists("https://www.instagram.com/$u/");
$results[] = $ig ? 'Instagram ✅' : 'Instagram ❌';
if ($ig) $checked[] = 'Instagram';

// TikTok
$tt = profileExists("https://www.tiktok.com/@$u");
$results[] = $tt ? 'TikTok ✅' : 'TikTok ❌';
if ($tt) $checked[] = 'TikTok';

// YouTube
$yt = false;
if ($yt_key) {
  $data = @file_get_contents("https://www.googleapis.com/youtube/v3/search?part=snippet&q=$u&type=channel&key=$yt_key");
  if ($data) {
    $json = json_decode($data, true);
    $yt = !empty($json['items']);
  }
} else {
  $yt = profileExists("https://www.youtube.com/@$u");
}
$results[] = $yt ? 'YouTube ✅' : 'YouTube ❌';
if ($yt) $checked[] = 'YouTube';

error_log("[FOLLOW] $username — " . implode(' | ', $results) . ' ' . date('c'));

if (count($checked) > 0) {
  echo json_encode([
    'verified' => true,
    'message' => 'Compte trouvé sur ' . implode(' & ', $checked) . '. Follow vérifié !'
  ]);
} else {
  echo json_encode([
    'verified' => false,
    'message' => 'Aucun compte trouvé avec ce pseudo sur Instagram, TikTok ou YouTube. Vérifie ton pseudo et réessaie.'
  ]);
}
