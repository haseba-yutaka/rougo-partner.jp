<?php

//ユーザー：デバイス・ブラウザ・OS取得
class browser_zag{
  
  function get_info(){
    
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $browser_name = $browser_version = $webkit_version = $platform = NULL;
    $is_webkit = false;
    
    //Browser
    if(preg_match('/Edge/i', $ua)){
      
      $browser_name = 'Edge';
      
      if(preg_match('/Edge\/([0-9.]*/', $ua, $match)){
      
        $browser_version = $match[1]; 
      }
      
    }elseif(preg_match('/(MSIE|Trident)/i', $ua)){
      
      $browser_name = 'IE';
      
      if(preg_match('/MSIE\s([0-9.]*)/', $ua, $match)){
        
        $browser_version = $match[1];
      
      }elseif(preg_match('/Trident\/7/', $ua, $match)){
        
        $browser_version = 11;
      }
    
    }elseif(preg_match('/Presto|OPR|OPiOS/i', $ua)){
      
      $browser_name = 'Opera';
      
      if(preg_match('/(Opera|OPR|OPiOS)\/([0-9.]*)/', $ua, $match)) $browser_version = $match[2];
      
    }elseif(preg_match('/Firefox/i', $ua)){
      
      $browser_name = 'Firefox';
      
      if(preg_match('/Firefox\/([0-9.]*)/', $ua, $match)) $browser_version = $match[1];
      
    }elseif(preg_match('/Chrome|CriOS/i', $ua)){
      
      $browser_name = 'Chrome';
      
      if(preg_match('/(Chrome|CriOS)\/([0-9.]*)/', $ua, $match)) $browser_version = $match[2];
      
    }elseif(preg_match('/Safari/i', $ua)){
      
      $browser_name = 'Safari';
      
      if(preg_match('/Version\/([0-9.]*)/', $ua, $match)) $browser_version = $match[1];
    }
    
    //Webkit
    if(preg_match('/AppleWebkit/i', $ua)){
      
      $is_webkit = true;
      
      if(preg_match('/AppleWebKit\/([0-9.]*)/', $ua, $match)) $webkit_version = $match[1];
    }
    
    //Platform
    if(preg_match('/ipod/i', $ua)){
      
      $platform = 'iPod';
      
    }elseif(preg_match('/iphone/i', $ua)){
      
      $platform = 'iPhone';
      
    }elseif(preg_match('/ipad/i', $ua)){
      
      $platform = 'iPad';
      
    }elseif(preg_match('/android/i', $ua)){
      
      $platform = 'Android';
      
    }elseif(preg_match('/windows phone/i', $ua)){
      
      $platform = 'Windows Phone';
      
    }elseif(preg_match('/linux/i', $ua)){
      
      $platform = 'Linux';
      
    }elseif(preg_match('/macintosh|mac os/i', $ua)) {
      
      $platform = 'Mac';
      
    }elseif(preg_match('/windows/i', $ua)){
      
      $platform = 'Windows';
    }
    
    return array(
      
      'ua' => $ua,
      'browser_name' => $browser_name,
      'browser_version' => intval($browser_version),
      'is_webkit' => $is_webkit,
      'webkit_version' => intval($webkit_version),
      'platform' => $platform
    );
  }//get_info()
}
 
$browser = new browser_zag(); 
$browser_info = $browser->get_info();

//ユーザー：端末
if($browser_info['platform'] == 'Mac' || $browser_info['platform'] == 'Windows' || $browser_info['platform'] == 'Linux') {
    $hokandata['user_device'] = "パソコン";
} elseif($browser_info['platform'] == 'iPad') {
    $hokandata['user_device'] = "タブレット";
} else {
    $hokandata['user_device'] = "スマートフォン";
}

//ユーザー：ブラウザ
$hokandata['user_browser'] = $browser_info['browser_name'];

//ユーザー：OS
$hokandata['user_os'] = $browser_info['platform'];

//extra_values生成（emptyの項目はエラーになる場合があるため、入力がある場合のみ配列を生成）
if(!empty($hokandata['prefectures'])) {
	$hokandata['extra_values'][] = array('internal_key' => "prefectures",
                'value' => $hokandata['prefectures']);
}
if(!empty($hokandata['partner'])) {
	$hokandata['extra_values'][] = array('internal_key' => "partner",
                'value' => $hokandata['partner']);
}
if(!empty($hokandata['child'])) {
	$hokandata['extra_values'][] = array('internal_key' => "child",
                'value' => $hokandata['child']);
}
if(!empty($hokandata['household_income'])) {
	$hokandata['extra_values'][] = array('internal_key' => "household_income",
                'value' => $hokandata['household_income']);
}
if(!empty($hokandata['consultation_content'])) {
	$hokandata['extra_values'][] = array('internal_key' => "consultation_content",
                'value' => $hokandata['consultation_content']);
}
if(!empty($hokandata['consultation_content_other'])) {
	$hokandata['extra_values'][] = array('internal_key' => "consultation_content_other",
                'value' => $hokandata['consultation_content_other']);
}
if(!empty($hokandata['consultation_place'])) {
	$hokandata['extra_values'][] = array('internal_key' => "consultation_place",
                'value' => $hokandata['consultation_place']);
}
if(!empty($hokandata['consultation_date'])) {
	$hokandata['extra_values'][] = array('internal_key' => "consultation_date",
                'value' => $hokandata['consultation_date']);
}
if(!empty($hokandata['present'])) {
	$hokandata['extra_values'][] = array('internal_key' => "present",
                'value' => $hokandata['present']);
}

if(!empty($hokandata['route'])) {
	$hokandata['extra_values'][] = array('internal_key' => "route",
                'value' => $hokandata['route']);
}
if(!empty($hokandata['user_useragent'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_useragent",
                'value' => $hokandata['user_useragent']);
}
if(!empty($hokandata['user_lpurl'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_lpurl",
                'value' => $hokandata['user_lpurl']);
}
if(!empty($_SERVER['REMOTE_ADDR'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_ip",
                'value' => $_SERVER['REMOTE_ADDR']);
}
if(!empty($hokandata['user_device'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_device",
                'value' => $hokandata['user_device']);
}
if(!empty($hokandata['user_browser'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_browser",
                'value' => $hokandata['user_browser']);
}
if(!empty($hokandata['user_os'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_os",
                'value' => $hokandata['user_os']);
}
if(!empty($hokandata['referer']['user_referer'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_referer",
                'value' => $hokandata['referer']['user_referer']);
}
if(!empty($hokandata['referer']['url_cid'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_cid",
                'value' => $hokandata['referer']['url_cid']);
}
if(!empty($hokandata['referer']['url_p'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_p",
                'value' => $hokandata['referer']['url_p']);
}
if(!empty($hokandata['referer']['url_mid'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_mid",
                'value' => $hokandata['referer']['url_mid']);
}
if(!empty($hokandata['referer']['url_utm_source'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_utm_source",
                'value' => $hokandata['referer']['url_utm_source']);
}
if(!empty($hokandata['referer']['url_utm_medium'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_utm_medium",
                'value' => $hokandata['referer']['url_utm_medium']);
}
if(!empty($hokandata['referer']['url_utm_campaign'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_utm_campaign",
                'value' => $hokandata['referer']['url_utm_campaign']);
}
if(!empty($hokandata['referer']['url_utm_term'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_utm_term",
                'value' => $hokandata['referer']['url_utm_term']);
}
if(!empty($hokandata['referer']['url_utm_content'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_utm_content",
                'value' => $hokandata['referer']['url_utm_content']);
}
if(!empty($hokandata['line_schedule'])) {
	$hokandata['extra_values'][] = array('internal_key' => "line_schedule_service",
                'value' => $hokandata['line_schedule']);
}
if(!empty($hokandata['insurance_plan'])) {
	$hokandata['extra_values'][] = array('internal_key' => "insurance_plan",
                'value' => $hokandata['insurance_plan']);
}
if(!empty($hokandata['request_documents'])) {
	$hokandata['extra_values'][] = array('internal_key' => "request_documents",
                'value' => $hokandata['request_documents']);
}

if(!empty($hokandata['introducer'])) {
	$hokandata['extra_values'][] = array('internal_key' => "introducer",
                'value' => $hokandata['introducer']);
}
if(!empty($hokandata['referer']['url_sid'])) {
	$hokandata['extra_values'][] = array('internal_key' => "url_sid",
                'value' => $hokandata['referer']['url_sid']);
}

if(!empty($hokandata['user_formurl'])) {
	$hokandata['extra_values'][] = array('internal_key' => "user_formurl",
                'value' => $hokandata['user_formurl']);
}

if(isset($hokandata['extra_values_remarks'])&&!empty($hokandata['extra_values_remarks'])) {
	$hokandata['extra_values'][] = array('internal_key' => "remarks",
                'value' => $hokandata['extra_values_remarks']);
}

if(isset($hokandata['rougo_lifestyle'])&&!empty($hokandata['rougo_lifestyle'])) {
	$hokandata['extra_values'][] = array('internal_key' => "rougo_lifestyle",
                'value' => $hokandata['rougo_lifestyle']);
}

if(isset($hokandata['rougo_insurance_join'])&&!empty($hokandata['rougo_insurance_join'])) {
	$hokandata['extra_values'][] = array('internal_key' => "rougo_insurance_join",
                'value' => $hokandata['rougo_insurance_join']);
}

if(isset($hokandata['rougo_insurance_premium'])&&!empty($hokandata['rougo_insurance_premium'])) {
	$hokandata['extra_values'][] = array('internal_key' => "rougo_insurance_premium",
                'value' => $hokandata['rougo_insurance_premium']);
}

if(isset($hokandata['rougo_insurance_concern'])&&!empty($hokandata['rougo_insurance_concern'])) {
	$hokandata['extra_values'][] = array('internal_key' => "rougo_insurance_concern",
                'value' => $hokandata['rougo_insurance_concern']);
}

if(isset($hokandata['rougo_read'])&&!empty($hokandata['rougo_read'])) {
	$hokandata['extra_values'][] = array('internal_key' => "rougo_read",
                'value' => $hokandata['rougo_read']);
}


//hokanAPI
$send_url = "https://api.hokan.io/api/ext/v1/customers";
$data = array(
  'identify_same_customer' => true,
  'data' => array(
    'customer_type' => "individual",
    'zip' => $hokandata['zip'] ?? '',
    'address' => $hokandata['address'] ?? '',
    'tel' => $hokandata['tel'] ?? '',
    'email' => $hokandata['email'] ?? '',
    'extra_values' => $hokandata['extra_values'] ?? [],
    'last_name' => $hokandata['last_name'] ?? '',
    'first_name' => $hokandata['first_name'] ?? '',
    'last_name_kana' => $hokandata['last_name_kana'] ?? '',
    'first_name_kana' => $hokandata['first_name_kana'] ?? '',
    'gender' => $hokandata['gender'] ?? '',
    'birthday' => $hokandata['birthday'] ?? '',
    'industry' => $hokandata['industry'] ?? '',
    'annual_income' => $hokandata['annual_income'] ?? '',
    'note' => $hokandata['remarks'] ?? '',
  )
);

//JSON形式でデータを投げる
$json = json_encode($data, JSON_PRETTY_PRINT);
$ch = curl_init();
//ZAGhokan
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-API-KEY: Basic YmFiNGYxZTUtMGRhZS00Mjk0LWIwNmEtMGY3MTc2ZTdlOTQ3LjhiZDUxOGQzLTJmYTAtNGYwMy05ZTgyLTVkNTdjYzE3NDQ3Yi4xMDM4MjU0NDY4LmY5MmI5NGQxM2FiMzE5MGE3ZWRhNjgyZTJkMDFmYjhjNmE3OWQzYzQ5MGExNzM5MmVhMDFjNjNmZGI4ZWEwZTI='));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $send_url);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$result = curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$result = json_decode($result, true);

curl_close($ch);



/*echo '<pre>■ステータスコード';
print_r($httpcode);
echo '</pre>';

echo '<pre>■送信結果';
print_r($result);
echo '</pre>';
exit();*/
?>