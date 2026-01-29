<?php
//----------------------------------------------------------------------
//独自パラメータ
//----------------------------------------------------------------------

//SID
if (isset($_GET['sid']) && !isset($_SESSION['referer']['url_sid']) || isset($_GET['sid']) && isset($_SESSION['referer']['url_sid']) && ($_SESSION['referer']['url_sid'] != $_GET['sid'])) {
    $_SESSION['referer']['url_sid'] = $_GET['sid'];
}

//----------------------------------------------------------------------
//アフィリエイトパラメーター取得
//----------------------------------------------------------------------

// cookieの有効期限（50日）
$cookie_expire = time() + 60 * 60 * 24 * 50;

// クッキーから `cid`, `p`, `mid` を取得する関数
function getCidPandMidFromCookies() {
    $result = ['cid' => null, 'p' => null, 'mid' => null];
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'CL_') === 0) {
            $result['cid'] = $value;
            $result['p'] = substr($name, 3); // クッキー名の 'CL_' の後の文字列を取得
        } elseif (strpos($name, 'MID_') === 0) {
            $result['mid'] = $value;
        }
    }
    return $result;
}

// クッキーから値を取得
$cookie_data = getCidPandMidFromCookies();
$cid_from_cookie = $cookie_data['cid'];
$p_from_cookie = $cookie_data['p'];
$mid_from_cookie = $cookie_data['mid'];

// 成果ID
if (isset($_GET['cid'])) {
    // GETパラメータの `cid` が優先
    $_SESSION['referer']['url_cid'] = $_GET['cid'];
    setcookie('cid', $_GET['cid'], $cookie_expire);
} elseif (!isset($_SESSION['referer']['url_cid']) || 
          (isset($cid_from_cookie) && $_SESSION['referer']['url_cid'] != $cid_from_cookie)) {
    // GETパラメータがない場合はクッキーの `cid` を使用
    $_SESSION['referer']['url_cid'] = $cid_from_cookie;
}

// 広告ID
if (isset($_GET['p'])) {
    // GETパラメータの `p` が優先
    $_SESSION['referer']['url_p'] = $_GET['p'];
    setcookie('p', $_GET['p'], $cookie_expire);
} elseif (!isset($_SESSION['referer']['url_p']) || 
          (isset($p_from_cookie) && $_SESSION['referer']['url_p'] != $p_from_cookie)) {
    // GETパラメータがない場合はクッキーの `p` を使用
    $_SESSION['referer']['url_p'] = $p_from_cookie;
}

// メディアID
if (isset($_GET['mid'])) {
    // GETパラメータの `mid` が優先
    $_SESSION['referer']['url_mid'] = $_GET['mid'];
    setcookie('mid', $_GET['mid'], $cookie_expire);
} elseif (!isset($_SESSION['referer']['url_mid']) || 
          (isset($mid_from_cookie) && $_SESSION['referer']['url_mid'] != $mid_from_cookie)) {
    // GETパラメータがない場合はクッキーの `mid` を使用
    $_SESSION['referer']['url_mid'] = $mid_from_cookie;
}

//アフィリエイトパラメーター？
if(empty($_SESSION['referer']['partner'])&&isset($_GET['partner'])){
	$_SESSION['referer']['partner'] = $_GET['partner'];
}


//----------------------------------------------------------------------
//広告パラメーター取得
//----------------------------------------------------------------------

//流入元（独自）
if (isset($_GET['source']) && !isset($_SESSION['referer']['source']) || isset($_GET['source']) && isset($_SESSION['referer']['source']) && ($_SESSION['referer']['source'] != $_GET['source'])) {
    $_SESSION['referer']['source'] = $_GET['source'];
}

//参照元
if (isset($_GET['utm_source']) && !isset($_SESSION['referer']['url_utm_source']) || isset($_GET['utm_source']) && isset($_SESSION['referer']['url_utm_source']) && ($_SESSION['referer']['url_utm_source'] != $_GET['utm_source'])) {
    $_SESSION['referer']['url_utm_source'] = $_GET['utm_source'];
}

//メディア・媒体
if (isset($_GET['utm_medium']) && !isset($_SESSION['referer']['url_utm_medium']) || isset($_GET['utm_medium']) && isset($_SESSION['referer']['url_utm_medium']) && ($_SESSION['referer']['url_utm_medium'] != $_GET['utm_medium'])) {
    $_SESSION['referer']['url_utm_medium'] = $_GET['utm_medium'];
}

//キーワード
if (isset($_GET['utm_term']) && !isset($_SESSION['referer']['url_utm_term']) || isset($_GET['utm_term']) && isset($_SESSION['referer']['url_utm_term']) && ($_SESSION['referer']['url_utm_term'] != $_GET['utm_term'])) {
    $_SESSION['referer']['url_utm_term'] = $_GET['utm_term'];
}

//キャンペーン名
if (isset($_GET['utm_campaign']) && !isset($_SESSION['referer']['url_utm_campaign']) || isset($_GET['utm_campaign']) && isset($_SESSION['referer']['url_utm_campaign']) && ($_SESSION['referer']['url_utm_campaign'] != $_GET['utm_campaign'])) {
    $_SESSION['referer']['url_utm_campaign'] = $_GET['utm_campaign'];
}

//コンテンツ
if (isset($_GET['utm_content']) && !isset($_SESSION['referer']['url_utm_content']) || isset($_GET['utm_content']) && isset($_SESSION['referer']['url_utm_content']) && ($_SESSION['referer']['url_utm_content'] != $_GET['utm_content'])) {
    $_SESSION['referer']['url_utm_content'] = $_GET['utm_content'];
}

?>