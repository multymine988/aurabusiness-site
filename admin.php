<?php
$pass = 'aura2026';
if (!isset($_GET['p']) || $_GET['p'] !== $pass) {
  header('Content-Type: text/html; charset=utf-8');
  echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Accès restreint</title><style>body{font-family:sans-serif;background:#0a0a0a;color:#fff;display:flex;height:100vh;align-items:center;justify-content:center}form{background:#111;padding:30px;border-radius:12px}input{padding:10px;border-radius:6px;border:1px solid #333;background:#222;color:#fff}button{padding:10px 20px;background:#e94560;border:none;border-radius:6px;color:#fff;cursor:pointer}</style></head><body><form method="get"><h2 style="margin-bottom:16px">🔒 Admin</h2><input type="password" name="p" placeholder="Mot de passe" required><br><br><button type="submit">Accéder</button></form></body></html>';
  exit;
}

$file = __DIR__ . '/api/subscribers.txt';
$emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Abonnés</title>
<style>body{font-family:monospace;background:#0a0a0a;color:#f5f5f5;padding:30px;max-width:600px;margin:0 auto}h1{color:#e94560;font-size:20px}.count{color:#888;margin-bottom:16px}li{padding:6px 0;border-bottom:1px solid #222;font-size:13px}ul{list-style:none;padding:0}.empty{color:#555}.back{color:#777;text-decoration:none;font-size:13px;display:block;margin-top:20px}</style>
</head>
<body>
<h1>📧 Abonnés newsletter</h1>
<p class="count"><?= count($emails) ?> inscription(s)</p>
<?php if (count($emails) > 0): ?>
<ul><?php foreach (array_reverse($emails) as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul>
<?php else: ?><p class="empty">Aucun email pour l'instant.</p><?php endif; ?>
<a class="back" href="/">← Retour au site</a>
</body>
</html>
