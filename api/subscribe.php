<?php
$email = $_POST['email'] ?? '';
$redirect = $_POST['redirect'] ?? '/';

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $log = date('c') . ' - ' . $email . PHP_EOL;
  $file = __DIR__ . '/subscribers.txt';
  @file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
  if (!file_exists($file)) @touch($file);
  error_log('[SUBSCRIBE] ' . $email . ' saved to ' . $file);
}

$redirect = preg_replace('/[^a-zA-Z0-9._\/-]/', '', $redirect);
header('Location: /' . ltrim($redirect, '/'));
exit;
