<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $username = trim($input['username'] ?? '');

  if (strlen($username) < 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid username (min 3 characters)']);
    exit;
  }

  $u = urlencode($username);
  $yt_key = getenv('YOUTUBE_API_KEY') ?: 'AIzaSyDh-n_kLpJM92gqXHULYyYEWFIKZj_Pev0';

  function curlGet($url, $timeout = 6) {
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $timeout,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http === 404) return false;
    return $data;
  }

  function profileExists($url) {
    $html = curlGet($url);
    if ($html === false || $html === null || $html === '') return false;
    $lower = strtolower($html);
    if (strpos($lower, 'page not found') !== false) return false;
    if (strpos($lower, 'cette page n\'existe pas') !== false) return false;
    if (strpos($lower, 'couldn\'t find') !== false) return false;
    if (strpos($lower, 'sorry, this page') !== false) return false;
    return true;
  }

  $checked = [];

  $ig = profileExists("https://www.instagram.com/$u/");
  if ($ig) $checked[] = 'Instagram';

  $tt = profileExists("https://www.tiktok.com/@$u");
  if ($tt) $checked[] = 'TikTok';

  $yt = false;
  if ($yt_key) {
    $data = curlGet("https://www.googleapis.com/youtube/v3/search?part=snippet&q=$u&type=channel&key=$yt_key");
    if ($data) {
      $json = json_decode($data, true);
      $yt = !empty($json['items']);
    }
  } else {
    $yt = profileExists("https://www.youtube.com/@$u");
  }
  if ($yt) $checked[] = 'YouTube';

  error_log("[FOLLOW] $username — " . implode(', ', $checked) . ' ' . date('c'));

  if (count($checked) > 0) {
    echo json_encode(['verified' => true, 'message' => 'Compte trouvé sur ' . implode(' & ', $checked) . '. Follow vérifié !']);
  } else {
    echo json_encode(['verified' => false, 'message' => 'Aucun compte trouvé avec ce pseudo sur Instagram, TikTok ou YouTube. Vérifie ton pseudo et réessaie.']);
  }
} catch (Exception $e) {
  error_log('[FOLLOW ERROR] ' . $e->getMessage());
  echo json_encode(['verified' => false, 'message' => 'Erreur de vérification. Réessaie dans quelques instants.']);
}
