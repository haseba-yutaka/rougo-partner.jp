<?php
// ============== hokanapi.php（新フォーム準拠・辞書なし） ==============

// --- UA/デバイス判定 ---
class browser
{
  function get_info()
  {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser_name = $platform = null;
    if (preg_match('/Edge/i', $ua)) $browser_name = 'Edge';
    elseif (preg_match('/(MSIE|Trident)/i', $ua)) $browser_name = 'IE';
    elseif (preg_match('/Presto|OPR|OPiOS/i', $ua)) $browser_name = 'Opera';
    elseif (preg_match('/Firefox/i', $ua)) $browser_name = 'Firefox';
    elseif (preg_match('/Chrome|CriOS/i', $ua)) $browser_name = 'Chrome';
    elseif (preg_match('/Safari/i', $ua)) $browser_name = 'Safari';

    if (preg_match('/ipod/i', $ua)) $platform = 'iPod';
    elseif (preg_match('/iphone/i', $ua)) $platform = 'iPhone';
    elseif (preg_match('/ipad/i', $ua)) $platform = 'iPad';
    elseif (preg_match('/android/i', $ua)) $platform = 'Android';
    elseif (preg_match('/windows phone/i', $ua)) $platform = 'Windows Phone';
    elseif (preg_match('/linux/i', $ua)) $platform = 'Linux';
    elseif (preg_match('/macintosh|mac os/i', $ua)) $platform = 'Mac';
    elseif (preg_match('/windows/i', $ua)) $platform = 'Windows';

    return [
      'ua' => $ua,
      'browser_name' => $browser_name,
      'platform' => $platform
    ];
  }
}
$browser = new browser();
$browser_info = $browser->get_info();

// --- 基本メタ ---
$form = $_SESSION['form'] ?? [];

// present は日本語ラベル（改行除去）
if (isset($form['present'])) {
  $form['present'] = str_replace(["\\n", "\r\n", "\r", "\n"], '', $form['present']);
}

// --- 基本メタ ---
$hokandata = [];
$hokandata['user_useragent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
$hokandata['user_device']    = in_array($browser_info['platform'], ['Mac', 'Windows', 'Linux'], true) ? 'パソコン' : (($browser_info['platform'] === 'iPad') ? 'タブレット' : 'スマートフォン');
$hokandata['user_browser']   = $browser_info['browser_name'] ?? '';
$hokandata['user_os']        = $browser_info['platform'] ?? '';
$hokandata['user_lpurl']     = $_SESSION['referer']['first_lp_url'] ?? ($_SESSION['referer']['referer_url'] ?? '');
$hokandata['route']          = $hokanProject ?? ($hokan['hokanProject'] ?? ''); // mail.php 側で取得済み
$hokandata['prefecture']     = $form['prefecture'] ?? '';

// 氏名・連絡先・住所
$hokandata['present']         = $form['present']         ?? '';
$hokandata['last_name']       = $form['name_sei']        ?? '';
$hokandata['first_name']      = $form['name_mei']        ?? '';
$hokandata['last_name_kana']  = $form['name_kana_sei']   ?? '';
$hokandata['first_name_kana'] = $form['name_kana_mei']   ?? '';
$hokandata['email']           = $form['email']           ?? '';
$hokandata['tel']             = preg_replace('/[^0-9]/', '', $form['phone'] ?? '');
$hokandata['zip']             = preg_replace('/[^0-9]/', '', $form['zip'] ?? '');
$hokandata['address']         = trim(($form['prefecture'] ?? '') . ($form['city'] ?? '') . ($form['address_detail'] ?? ''));
$hokandata['remarks']         = $form['remarks']         ?? '';

// 誕生日（YYYYMMDD → YYYY-MM-DD）
if (
  !empty($form['year']) && !empty($form['month']) && !empty($form['day'])
  && checkdate((int)$form['month'], (int)$form['day'], (int)$form['year'])
) {
  $hokandata['birthday'] = sprintf('%04d-%02d-%02d', (int)$form['year'], (int)$form['month'], (int)$form['day']);
} else {
  $hokandata['birthday'] = '';
}

// 性別：数値化
if (($form['gender'] ?? '') === '男性')      $hokandata['gender'] = 1;
elseif (($form['gender'] ?? '') === '女性')  $hokandata['gender'] = 2;
else                                        $hokandata['gender'] = 0;

// --- extra_values（新設問のコード値のみ積む：辞書は持たない） ---
$ev = [];

if (!empty($form['holiday_category'])) {
  $label = $list_holiday[$form['holiday_category']] ?? $form['holiday_category'];
  $ev[] = ['internal_key' => 'holiday_category', 'value' => $label];
}
if (!empty($form['family_status'])) {
  $label = $list_family_status[$form['family_status']] ?? $form['family_status'];
  $ev[] = ['internal_key' => 'family_status', 'value' => $label];
}
// 複数選択：各コードをラベル化してカンマ連結
if (!empty($form['housing_plan']) && is_array($form['housing_plan'])) {
  $labels = array_map(function ($k) use ($list_housing_plan) {
    return $list_housing_plan[$k] ?? $k;
  }, $form['housing_plan']);
  $labels = array_values(array_filter($labels, 'strlen'));
  if ($labels) {
    $ev[] = ['internal_key' => 'housing_plan', 'value' => implode(',', $labels)];
  }
}

if (!empty($form['money_concerns']) && is_array($form['money_concerns'])) {
  $labels = array_map(function ($k) use ($list_money_concerns) {
    return $list_money_concerns[$k] ?? $k;
  }, $form['money_concerns']);
  $labels = array_values(array_filter($labels, 'strlen'));
  $ev[] = ['internal_key' => 'money_concerns', 'value' => implode(',', $labels)];
}
if (!empty($form['insurance_concerns']) && is_array($form['insurance_concerns'])) {
  $labels = array_map(function ($k) use ($list_insurance_concerns) {
    return $list_insurance_concerns[$k] ?? $k;
  }, $form['insurance_concerns']);
  $labels = array_values(array_filter($labels, 'strlen'));
  $ev[] = ['internal_key' => 'insurance_concerns', 'value' => implode(',', $labels)];
}
if (!empty($form['fp_interest']) && is_array($form['fp_interest'])) {
  $labels = array_map(function ($k) use ($list_fp_interest) {
    return $list_fp_interest[$k] ?? $k;
  }, $form['fp_interest']);
  $labels = array_values(array_filter($labels, 'strlen'));
  $ev[] = ['internal_key' => 'fp_interest', 'value' => implode(',', $labels)];
}

// present（日本語ラベル）
if (!empty($hokandata['present'])) {
  $ev[] = ['internal_key' => 'present', 'value' => $hokandata['present']];
}

if (!empty($hokandata['prefecture'])) {
  $ev[] = ['internal_key' => 'prefectures', 'value' => $hokandata['prefecture']];
}

// ★ 元の挙動に戻す：ここで url_sid を1回だけ積む（SESSIONのみ、GETフォールバックなし）
if (!empty($_SESSION['referer']['url_sid'])) {
  $ev[] = ['internal_key' => 'url_sid', 'value' => $_SESSION['referer']['url_sid']];
}

// 環境情報
if (!empty($hokandata['route']))          $ev[] = ['internal_key' => 'route',          'value' => $hokandata['route']];
if (!empty($hokandata['user_useragent'])) $ev[] = ['internal_key' => 'user_useragent', 'value' => $hokandata['user_useragent']];
if (!empty($hokandata['user_lpurl']))     $ev[] = ['internal_key' => 'user_lpurl',     'value' => $hokandata['user_lpurl']];
if (!empty($_SERVER['REMOTE_ADDR']))      $ev[] = ['internal_key' => 'user_ip',        'value' => $_SERVER['REMOTE_ADDR']];
if (!empty($hokandata['user_device']))    $ev[] = ['internal_key' => 'user_device',    'value' => $hokandata['user_device']];
if (!empty($hokandata['user_browser']))   $ev[] = ['internal_key' => 'user_browser',   'value' => $hokandata['user_browser']];
if (!empty($hokandata['user_os']))        $ev[] = ['internal_key' => 'user_os',        'value' => $hokandata['user_os']];

// UTM 等（※ここには url_sid は含めない：二重積み防止）
$ref = $_SESSION['referer'] ?? [];
  foreach (
    [
      'user_referer',
      'url_sid',
      'url_cid',
      'url_p',
      'url_mid',
      'url_utm_source',
      'url_utm_medium',
    'url_utm_campaign',
    'url_utm_term',
    'url_utm_content'
  ] as $k
) {
  if (!empty($ref[$k])) $ev[] = ['internal_key' => $k, 'value' => $ref[$k]];
}

$hokandata['extra_values'] = $ev;

// --- API 送信データ（annual_income / industry は送らない） ---
$data = [
  'identify_same_customer' => true,
  'data' => [
    'customer_type'    => 'individual',
    'present'          => $hokandata['present'],
    'zip'              => $hokandata['zip'],
    'address'          => $hokandata['address'],
    'tel'              => $hokandata['tel'],
    'email'            => $hokandata['email'],
    'extra_values'     => $hokandata['extra_values'],
    'last_name'        => $hokandata['last_name'],
    'first_name'       => $hokandata['first_name'],
    'last_name_kana'   => $hokandata['last_name_kana'],
    'first_name_kana'  => $hokandata['first_name_kana'],
    'gender'           => $hokandata['gender'],
    'birthday'         => $hokandata['birthday'],
    'note'             => $hokandata['remarks'],
    'route'            => $hokandata['route'],
  ]
];

// ==================== 二つのHokanへ送信（逐次） ====================
$send_url = 'https://api.hokan.io/api/ext/v1/customers';
$json     = json_encode($data, JSON_UNESCAPED_UNICODE);

// アカウントごとのヘッダ（元の書式を踏襲）
$apiHeadersList = [
  'accountA' => [
    'Content-Type: application/json',
    'X-API-KEY: Basic ZWNhMzUzODQtYzkxOS00NTA0LWI3ZWItZTE0MTM0MmE5ZjM0LjQyMzFiODU3LWM1MjktNDcyYy1hOTA3LWRmZGRkNTlmODE5My4xMDM4MjU0MzY3Ljk2ODIwNDczYjk4ZjU5ZGJjZDE5YWI3ZTQzZTU4N2JiZTYwMTJhOTdjYjQ2ODBlMGYxZDQyNjQ3Zjc3YWJlMjM='
  ],
  'accountB' => [
    'Content-Type: application/json',
    'X-API-KEY: Basic YmFiNGYxZTUtMGRhZS00Mjk0LWIwNmEtMGY3MTc2ZTdlOTQ3LjhiZDUxOGQzLTJmYTAtNGYwMy05ZTgyLTVkNTdjYzE3NDQ3Yi4xMDM4MjU0NDY4LmY5MmI5NGQxM2FiMzE5MGE3ZWRhNjgyZTJkMDFmYjhjNmE3OWQzYzQ5MGExNzM5MmVhMDFjNjNmZGI4ZWEwZTI='
  ],
];

$hokan_results = [];
foreach ($apiHeadersList as $name => $headers) {
  $ch = curl_init($send_url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);   // 既存値を維持
  // オプション：必要なら全体タイムアウトを付ける（次行のコメント解除）
  // curl_setopt($ch, CURLOPT_TIMEOUT, 8);

  $raw      = curl_exec($ch);
  $errno    = curl_errno($ch);
  $error    = curl_error($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  $parsed   = $raw !== false ? json_decode($raw, true) : null;
  curl_close($ch);

  $hokan_results[$name] = [
    'http'  => $httpcode,
    'errno' => $errno,
    'error' => $error,
    'json'  => $parsed,
    'raw'   => $raw,
  ];
}
// 後方互換（必要なら）：最後に送った結果を$result/$httpcodeへ
$result   = $hokan_results['accountB']['json'];
$httpcode = $hokan_results['accountB']['http'];
// ==================== /二つのHokanへ送信 ====================

// // --- デバッグ（必要時のみ有効化） ---
// echo '<pre>■extra_values'; print_r($hokandata['extra_values']); echo '</pre>';
// echo '<pre>■API送信データ'; print_r($data); echo '</pre>';
// echo '<pre>■送信結果 A/B'; print_r($hokan_results); echo '</pre>';
