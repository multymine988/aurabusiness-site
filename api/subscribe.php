<?php
$email = $_POST['email'] ?? '';
$redirect = $_POST['redirect'] ?? '/';

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $log = date('c') . ' - ' . $email . PHP_EOL;
  file_put_contents(__DIR__ . '/../subscribers.txt', $log, FILE_APPEND | LOCK_EX);
  error_log('[SUBSCRIBE] ' . $email . ' ' . date('c'));
}

$redirect = preg_replace('/[^a-zA-Z0-9._\/-]/', '', $redirect);
header('Location: /' . ltrim($redirect, '/'));
exit;
