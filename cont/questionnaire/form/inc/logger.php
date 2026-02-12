<?php

function form_mask_email(string $email): string
{
  $email = trim($email);
  if ($email === '' || strpos($email, '@') === false) return $email;
  [$local, $domain] = explode('@', $email, 2);
  $head = mb_substr((string)$local, 0, 2, 'UTF-8');
  return $head . '***@' . (string)$domain;
}

function form_mask_tel(string $tel): string
{
  $digits = preg_replace('/\D+/', '', $tel);
  if ($digits === '') return '';
  $tail = substr($digits, -4);
  return '***' . $tail;
}

function form_log_write(string $event, array $context = [], ?string $requestId = null): void
{
  $logDir = __DIR__ . '/../log';
  if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
  }

  $entry = [
    'ts' => date('Y-m-d H:i:s'),
    'event' => $event,
    'request_id' => $requestId,
  ];

  foreach ($context as $k => $v) {
    if (is_string($v) && strlen($v) > 2000) {
      $v = substr($v, 0, 2000) . '...(truncated)';
    }
    $entry[$k] = $v;
  }

  $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($line === false) return;

  $logFile = $logDir . '/form-' . date('Ymd') . '.log';
  @file_put_contents($logFile, $line . "\n", FILE_APPEND | LOCK_EX);
}
