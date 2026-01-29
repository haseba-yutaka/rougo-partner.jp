<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start(); 

// ==============================
// セッションチェック（不正アクセス防止）
// ==============================
// トークンチェック（1回目の送信）
if (
  !isset($_SESSION['send_token']) ||
  !isset($_POST['send_token']) ||
  !hash_equals($_SESSION['send_token'], $_POST['send_token'])
) {
  // トークン不一致（=二重送信と見なす）→ メール処理せずthanksに飛ばす
  header('Location: thanks.php');
  exit;
}

// トークン正当（初回）→ トークンを破棄して処理へ
unset($_SESSION['send_token']);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/phpmailer/PHPMailer.php');
require_once(__DIR__ . '/phpmailer/SMTP.php');
require_once(__DIR__ . '/phpmailer/Exception.php');


// ==============================
// フォームデータ取得＆初期整形
// ==============================
//入力された情報を$formに保存
$form = $_SESSION['form'];
$referer = $form['referer_url'] ?? '';

// 流入元
$form['source'] = $_SESSION['referer']['source'] ?? ''; 

// 流入URLの一文を生成
if ($referer !== '') {
  $referer_sentence = $referer . ' よりお問い合わせを受け付けました。';
} else {
  $referer_sentence = '不明なページよりお問い合わせを受け付けました。';
}

//正年月日の結合
$birthday = $form['birth_y'] . '年' . $form['birth_m'] . '月' . $form['birth_d'] . '日';
$address = '〒' . $form['zip'] . ' ' . $form['prefecture'] . $form['city'] . $form['address_detail'];


// 配列/文字列どちらでも表示用テキストにする（複数選択対策）
function toText($v, $sep = '、') {
  if (is_array($v)) {
    $v = array_values(array_filter($v, fn($x) => $x !== '' && $x !== null));
    return implode($sep, $v);
  }
  return (string)($v ?? '');
}

// メール用：箇条書き（・ + 改行）
function toBulletText($v, $prefix = '・', $newline = "\n") {
  if (is_array($v)) {
    $v = array_values(array_filter($v, fn($x) => $x !== '' && $x !== null));
    if (empty($v)) return '';
    return $prefix . implode($newline . $prefix, $v);
  }
  $v = trim((string)($v ?? ''));
  return $v === '' ? '' : $prefix . $v;
}

$insurance_join_text = toBulletText($form['insurance_join'] ?? []);       //医療保険やがん保険の加入
$insurance_concern_text = toBulletText($form['insurance_concern'] ?? []); //保険について（お悩み・関心事項）



// ==============================
// 共通メール本文構成
// ==============================
// // 共通本文
$common_body = <<<EOT
【お名前】{$form['last_name']} {$form['first_name']}
【お名前(フリガナ)】{$form['last_name_kana']} {$form['first_name_kana']}
【性別】{$form['gender']}
【生年月日】{$birthday}
【住所】{$address}
【電話番号】{$form['tel']}
【メールアドレス】{$form['email']}
【ご希望のプレゼント】{$form['present']}
【現在のライフスタイル】{$form['lifestyle']}
【毎月の保険料】{$form['insurance_premium']}
【医療保険やがん保険の加入】
{$insurance_join_text}
【保険について最も当てはまるもの】
{$insurance_concern_text}
EOT;

// 共通フッター
$common_footer = <<<EOT
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
＜お問合せ先＞
ママのぜんぶ企画プレゼントキャンペーン事務局
MAIL：mama-all@zenb-agency.co.jp
営業時間：午前10時～午後5時（土日祝日を除く）
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
EOT;




// ===============================
// hokan連携(必須項目)
// ===============================
//▼hokan連携必須項目(10項目)
$hokandata['last_name'] = $form['last_name'] ?? '';
$hokandata['first_name'] = $form['first_name'] ?? '';
$hokandata['last_name_kana'] = $form['last_name_kana'] ?? '';
$hokandata['first_name_kana'] = $form['first_name_kana'] ?? '';
$hokandata['email'] = $form['email'] ?? '';
$hokandata['tel'] = $form['tel'] ?? '';
$hokandata['zip'] = $form['zip'] ?? '';
$hokandata['address'] = $form['prefecture'] . $form['city'] . $form['address_detail'];
$hokandata['remarks'] = $form['remarks'] ?? '';
$hokandata['industry'] = ''; //空

//▼hokan連携任意項目
//正年月日はYYYY-MM-DD形式
$birth_y = preg_replace('/[^0-9]/', '', $form['birth_y'] ?? '');
$birth_m = str_pad(preg_replace('/[^0-9]/', '', $form['birth_m'] ?? ''), 2, '0', STR_PAD_LEFT);
$birth_d = str_pad(preg_replace('/[^0-9]/', '', $form['birth_d'] ?? ''), 2, '0', STR_PAD_LEFT);

if ($birth_y && $birth_m && $birth_d) {
  $hokandata['birthday'] = $birth_y . '-' . $birth_m . '-' . $birth_d;
} else {
  $hokandata['birthday'] = '';
}

//性別はint型に変換
if ($form['gender'] === "男性") {
  $hokandata['gender'] = 1;
} elseif ($form['gender'] === "女性") {
  $hokandata['gender'] = 2;
} else {
  $hokandata['gender'] = 0;
}

$hokandata['prefectures'] = $form['prefecture'] ?? '';
$hokandata['user_useragent'] = $_SERVER['HTTP_USER_AGENT'];
$hokandata['user_lpurl'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . str_replace(['form/', 'confirm/', 'form.php', 'mail.php'], '', $_SERVER['REQUEST_URI']);

//流入経路名
$hokandata['route'] = '【購入(リスト)】老後安心パートナーオーガニック_アンケートFM';

//ご希望のプレゼント
$hokandata['present'] = $form['present'] ?? '';

//ライフスタイル
$hokandata['rougo_lifestyle'] = $form['lifestyle'] ?? '';

//毎月の保険料
$hokandata['rougo_insurance_premium'] = $form['insurance_premium'] ?? '';

//保険加入(文字整形済み)
$hokandata['rougo_insurance_join'] = toBulletText($form['insurance_join'] ?? []);

//保険について(文字整形済み)
$hokandata['rougo_insurance_concern'] = toBulletText($form['insurance_concern'] ?? []);

//sidパラメータ
$hokandata['referer']['url_sid'] = $_SESSION['referer']['url_sid'] ?? '';

//URLパラメータ
$hokandata['referer']['url_utm_source']   = $_SESSION['referer']['url_utm_source'] ?? '';
$hokandata['referer']['url_utm_medium']   = $_SESSION['referer']['url_utm_medium'] ?? '';
$hokandata['referer']['url_utm_term']     = $_SESSION['referer']['url_utm_term'] ?? '';
$hokandata['referer']['url_utm_campaign'] = $_SESSION['referer']['url_utm_campaign'] ?? '';
$hokandata['referer']['url_utm_content']  = $_SESSION['referer']['url_utm_content'] ?? '';

//アフィパラメータ
$hokandata['referer']['url_mid'] = $_SESSION['referer']['url_mid'] ?? '';
$hokandata['referer']['url_cid'] = $_SESSION['referer']['url_cid'] ?? '';
$hokandata['referer']['url_p']   = $_SESSION['referer']['url_p'] ?? '';


//hokan読み込み
$hokandata['log'] = $hokandata['log'] ?? [];

// ==============================
// leadパラメータ（広告最適化 ）
// ==============================
$hokandata['rougo_insurance_concern'] = toBulletText($form['insurance_concern'] ?? []);
// 医療保険・がん保険の加入状況（複数選択）
$join = $form['insurance_join'] ?? [];
$join = is_array($join) ? $join : [$join];
$join = array_values(array_filter(array_map('trim', $join)));

// 保険についての悩み・関心事項（複数選択）
$concern = $form['insurance_concern'] ?? [];
$concern = is_array($concern) ? $concern : [$concern];
$concern = array_values(array_filter(array_map('trim', $concern)));

// 相談意欲が低いと判定する条件（OR判定）
$is_no_intent_lead =
    in_array('加入していない', $join, true) ||
    in_array('わからない', $join, true) ||
    in_array('保険について困っていることはない', $concern, true);

// hokanに「相談意欲なし」を送る（条件に当てはまる場合のみ）
if ($is_no_intent_lead) {
  $hokandata['rougo_read'] = '相談意欲なし';
}


// 送信（ZAGhokan）
$httpcode = null;
$result   = null;
require __DIR__ . '/inc/zag-hokanapi.php';

$hokandata['log']['zaghokan'] = [
  'datetime' => date("Y-m-d H:i:s"),
  'httpcode' => $httpcode,
  'result'   => $result,
];

// ZAGhoknaで積んだextra_valuesを破棄(値が2重になるのを防ぐ)
$hokandata['extra_values'] = [];

// 送信（HZhokan）
$httpcode = null;
$result   = null;
require __DIR__ . '/inc/hz-hokanapi.php';

$hokandata['log']['hzhokan'] = [
  'datetime' => date("Y-m-d H:i:s"),
  'httpcode' => $httpcode,
  'result'   => $result,
];

// ログ本文
$hokan_log_body  = "▼hokanAPIログ\n";

if (!empty($hokandata['log']['zaghokan'])) {
  $z = $hokandata['log']['zaghokan'];
  $hokan_log_body .= "\n【ZAGhokan】\n";
  $hokan_log_body .= "送信日時：{$z['datetime']}\n";
  $hokan_log_body .= "HTTPステータス：{$z['httpcode']}\n";
  $hokan_log_body .= "レスポンス：\n" . print_r($z['result'], true) . "\n";
}

if (!empty($hokandata['log']['hzhokan'])) {
  $h = $hokandata['log']['hzhokan'];
  $hokan_log_body .= "\n【HZhokan】\n";
  $hokan_log_body .= "送信日時：{$h['datetime']}\n";
  $hokan_log_body .= "HTTPステータス：{$h['httpcode']}\n";
  $hokan_log_body .= "レスポンス：\n" . print_r($h['result'], true) . "\n";
}


try {

  //管理者宛メール文章
  $admin_body = <<<EOT
  {$referer_sentence}
  内容は以下の通りです。

  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  {$common_body}
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  {$hokan_log_body}
  EOT;
  

  //応募者宛メール文章
  $user_body = <<<EOT
  {$form['last_name']} 様

  この度は本キャンペーンにご応募いただきまことにありがとうございます。
  ご入力いただいた内容は以下でござます。

  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  {$common_body}
  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  【当選発表・商品発送に関して】
  ・賞品の抽選は、本キャンペーンに加え、ママのぜんぶ（株式会社ZENB AGENCY）が主催する以下の協賛プレゼントキャンペーンの全応募者を対象として実施いたします。
  【ママのぜんぶ】×【ほけんのぜんぶ】プレゼントキャンペーン
  【ママのぜんぶ】×【保険の縁結び】プレゼントキャンペーン
  【ママのぜんぶ】×【みらいのほけん】プレゼントキャンペーン
  【老後安心パートナー】×【ほけんのぜんぶ】プレゼントキャンペーン
  応募期間終了後、厳正な抽選を実施し、商品の発送をもって当選のお知らせとさせていただきます。

  ※商品の発送は、応募期間終了から2ヶ月以内を予定しております。
  抽選完了後、順次発送いたします。やむを得ない事情により発送が遅延する場合もございますので、あらかじめご了承くださいませ。

  ご不明点などございましたらお気軽にお問合せ下さい。

  ご利用上の注意事項　https://zenb-agency.co.jp/mama-campaign/
  個人情報の取り扱いについて　https://zenb-agency.co.jp/announce/

  +++++++++++++++++++++++++++++++++++++++++++++++
  ＜お問合せ先＞
  老後安心パートナー企画プレゼントキャンペーン事務局
  MAIL：mama-all@zenb-agency.co.jp

  営業時間：午前10時～午後5時（土日祝日を除く）

  以上、宜しくお願いいたします。
  +++++++++++++++++++++++++++++++++++++++++++++++
  EOT;

  //PHPMailer設定
  $mail = new PHPMailer(true);
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP();
  $mail->Host = 'smtp.larksuite.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'life@hoken-all.co.jp';
  $mail->Password = 'Wufc51Pc9BHQe7ni';
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;

  // $mail = new PHPMailer(true);
  // $mail->CharSet = 'UTF-8';
  // $mail->isSMTP();
  // $mail->Host = 'sv16171.xserver.jp';
  // $mail->SMTPAuth = true;
  // $mail->Username = 'test@takenoko-web.co.jp';
  // $mail->Password = 'development_mail';
  // $mail->SMTPSecure = 'tls';
  // $mail->Port = 587;

  //共通設定
  $mail->setFrom('mama-all@zenb-agency.co.jp', 'ママのぜんぶ');
  $mail->addReplyTo('mama-all@zenb-agency.co.jp', 'ママのぜんぶ企画プレゼントキャンペーン事務局');
  $mail->Sender = 'life@hoken-all.co.jp';  // Return-Path 相当（PHPMailer）

  //管理者設定
  $mail->addAddress('life@hoken-all.co.jp', '管理者');
  $mail->Subject = "【開発中】後ほど設定"; 
  $mail->Body = $admin_body;
  $mail->send();

  //応募者設定
  $mail->clearAddresses();
  $mail->addAddress($form['email'], $form['last_name'] . ' ' . $form['first_name']);
  $mail->Subject = '【開発中】後ほど設定';
  $mail->Body = $user_body;
  $mail->send();

  // ==============================
  // 正常処理後・リダイレクト
  // ==============================

  // 完了画面へリダイレクト
  $to = 'thanks.php' . ($is_no_intent_lead ? '?lead=false' : '');
  header('Location: ' . $to);
  exit;


  // ==============================
  // エラーハンドリング（送信失敗時）
  // ==============================

} catch (Exception $e) {
  error_log('メール送信エラー: ' . $mail->ErrorInfo);
  header('Location: /index.php?error=mail');
  exit;
}