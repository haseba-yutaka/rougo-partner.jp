<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// トークンを生成
$token = bin2hex(random_bytes(32)); // 64文字のランダムな文字列
$_SESSION['send_token'] = $token;



// ===============================
// Step 1: 送信処理 (__confirmed が存在する場合)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['__confirmed'])) {
  if (empty($_SESSION['form'])) {
    header('Location: index.php');
    exit;
  }

  $form = $_SESSION['form'];
  require_once(__DIR__ . '/mail.php'); 

  unset($_SESSION['form']);
  header('Location: thanks.php');
  exit;
}


// ===============================
// Step 2: 確認画面表示 (バリデーション)
// ===============================

// POST以外はindexに戻す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

// エスケープ関数
function h($str) {
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$post = $_POST;
$error = [];

//お名前の結合
$last_name = $post['last_name'] ?? '';
$first_name = $post['first_name'] ?? '';
$full_name = ($last_name && $first_name) ? "{$last_name} {$first_name}" : '';

//お名前（フリガナ）の結合
$last_name_kana = $post['last_name_kana'] ?? '';
$first_name_kana = $post['first_name_kana'] ?? '';
$full_name_kana = ($last_name_kana && $first_name_kana) ? "{$last_name_kana} {$first_name_kana}" : '';

// 年月日の結合
$birth_y = $post['birth_y'] ?? '';
$birth_m = $post['birth_m'] ?? '';
$birth_d = $post['birth_d'] ?? '';
$birth_date = ($birth_y && $birth_m && $birth_d) ? "{$birth_y}{$birth_m}月{$birth_d}日" : '';

//住所の結合
$zip = $post['zip'] ?? '';
$prefecture = $post['prefecture'] ?? '';
$city = $post['city'] ?? '';
$address_detail = $post['address_detail'] ?? '';
$address = ($zip && $prefecture && $city && $address_detail) ? "〒{$zip} {$prefecture}{$city}{$address_detail}" : '';



// バリデーション

  // プレゼント
  if (empty($post['present'])) {
    $error['present'] = 'blank';
  }


  // お名前（姓）
  if (empty($post['last_name'])) {
    $error['last_name'] = 'blank';
  } elseif (mb_strlen($post['last_name']) > 100) {
    $error['last_name'] = 'length';
  }

  // お名前（名）
  if (empty($post['first_name'])) {
    $error['first_name'] = 'blank';
  } elseif (mb_strlen($post['first_name']) > 100) {
    $error['first_name'] = 'length';
  }

  // お名前（セイ）
  if (empty($post['last_name_kana'])) {
    $error['last_name_kana'] = 'blank';
  } elseif (mb_strlen($post['last_name_kana']) > 100) {
    $error['last_name_kana'] = 'length';
  }

  // お名前（メイ）
  if (empty($post['first_name_kana'])) {
    $error['first_name_kana'] = 'blank';
  } elseif (mb_strlen($post['first_name_kana']) > 100) {
    $error['first_name_kana'] = 'length';
  }

  // 性別
  if (empty($post['gender'])) {
    $error['gender'] = 'blank';
  }

  // 電話番号
  $phone = str_replace('-', '', $post['tel']); // ハイフン除去
  if ($phone === '') {
    $error['tel'] = 'blank';
  } elseif (!preg_match('/^0\d{9,10}$/', $phone)) {
    $error['tel'] = 'type'; // 固定 or 携帯ともにマッチしない場合
  } else {
    $post['tel'] = $phone; // バリデーションOKの電話番号を再格納（ハイフンなし）
  }

  // メールアドレス
  if (empty($post['email'])) {
    $error['email'] = 'blank';
  } elseif (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
    $error['email'] = 'type';
  }

	// 生年月日(年)
	if (empty($post['birth_y'])) {
		$error['birth_y'] = 'blank';
	}
	// 生年月日(月)
	if (empty($post['birth_m'])) {
		$error['birth_m'] = 'blank';
	}
	// 生年月日(日)
	if (empty($post['birth_d'])) {
		$error['birth_d'] = 'blank';
	}

  // 郵便番号（7桁数字）
  if (empty($post['zip'])) {
    $error['zip'] = 'blank';
  } elseif (!preg_match('/^\d{7}$/', $post['zip'])) {
    $error['zip'] = 'format';
  }

  // 都道府県
  if (empty($post['prefecture'])) {
    $error['prefecture'] = 'blank';
  }

  // 市区町村
  if (empty($post['city'])) {
    $error['city'] = 'blank';
  } elseif (mb_strlen($post['city']) > 100) {
    $error['city'] = 'length';
  }

  // 番地・建物名
  if (empty($post['address_detail'])) {
    $error['address_detail'] = 'blank';
  } elseif (mb_strlen($post['address_detail']) > 200) {
    $error['address_detail'] = 'length';
  }

  // 現在のライフスタイル
  if (empty($post['lifestyle'])) {
    $error['lifestyle'] = 'lifestyle';
  }

    // 医療保険やがん保険にご加入
  if (empty($post['insurance_join'])) {
    $error['insurance_join'] = 'blank';
  }


  // 現在の保険料
  if (empty($post['insurance_premium'])) {
    $error['insurance_premium'] = 'blank';
  }


  // 保険について
  if (empty($post['insurance_concern'])) {
    $error['insurance_concern'] = 'blank';
  }


  // ご利用上の注意事項の同意
  if (empty($post['terms']) || $post['terms'] !== '同意') {
    $error['terms'] = 'blank';
  }

  // 個人情報の同意
  if (empty($post['privacy']) || $post['privacy'] !== '同意') {
    $error['privacy'] = 'blank';
  }

  // お電話にてご本人様確認ができた方が抽選の対象
  if (empty($post['lottery']) || $post['lottery'] !== '同意') {
    $error['lottery'] = 'blank';
  }




// エラーがあれば index.php に戻す
if (!empty($error)) {
  $_SESSION['post'] = $post;
  $_SESSION['error'] = $error;
  header('Location: index.php');
  exit;
}

// エラーなし：確認用に保存
$_SESSION['form'] = $post;


?>
<!-- head -->
<?php include 'inc/head.php'; ?>
<!-- head end -->
<body>
<!-- header -->
<?php include 'inc/header.php'; ?>
<!-- header end -->
<div class="confirm form" id="confirm">
  <div class="form__inner">
    <div class="form__wrap">
        <h2 class="c-ttl">
          確認画面
        </h2>
        <ul class="step">
            <li class="step__item">入力</li>
            <li class="step__item step__item-active">確認</li>
            <li class="step__item">完了</li>
        </ul>
        <form id="send-form" action="mail.php" method="post">

          <!-- プレゼント選択 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">ご希望のプレゼント</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['present']) ?></p>
              <input type="hidden" name="present" value="<?= h($post['present']) ?>">
            </div>
          </div>



          <!-- お名前 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">お名前</p>
            </div>
            <div class="c-form__group__box__value">
              <div class="column">
                <!-- お名前 -->
                <div class="c-form__group__box__value-inner -width50">
                <div class="c-form__group__box__add">お名前</div>
                <p class="confirm__txt"><?= h($full_name) ?></p>
                <input type="hidden" name="last_name" value="<?= h($post['last_name']) ?>">
                <input type="hidden" name="first_name" value="<?= h($post['first_name']) ?>">
                </div>
                <!-- フリガナ -->
                <div class="c-form__group__box__value-inner -width50">
                  <div class="c-form__group__box__add">フリガナ</div>
                  <p class="confirm__txt"><?= h($full_name_kana) ?></p>
                  <input type="hidden" name="last_name_kana" value="<?= h($post['last_name_kana']) ?>">
                  <input type="hidden" name="first_name_kana" value="<?= h($post['first_name_kana']) ?>">
                </div>
              </div>
            </div>
          </div> 

          <!-- 性別 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">性別</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['gender']) ?></p>
              <input type="hidden" name="gender" value="<?= h($post['gender']) ?>">
            </div>
          </div>

          <!-- 生年月日 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">生年月日</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($birth_date) ?></p>
              <input type="hidden" name="birth_y" value="<?= h($post['birth_y']) ?>">
              <input type="hidden" name="birth_m" value="<?= h($post['birth_m']) ?>">
              <input type="hidden" name="birth_d" value="<?= h($post['birth_d']) ?>">
            </div>
          </div>

          <!-- 住所 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">住所</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($address) ?></p>
              <input type="hidden" name="zip" value="<?= h($post['zip']) ?>">
              <input type="hidden" name="prefecture" value="<?= h($post['prefecture']) ?>">
              <input type="hidden" name="city" value="<?= h($post['city']) ?>">
              <input type="hidden" name="address_detail" value="<?= h($post['address_detail']) ?>">
            </div>
          </div>

          <!-- 電話番号 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">電話番号</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['tel']) ?></p>
              <input type="hidden" name="tel" value="<?= h($post['tel']) ?>">
            </div>
          </div>

          <!-- メールアドレス -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">メールアドレス</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['email']) ?></p>
              <input type="hidden" name="email" value="<?= h($post['email']) ?>">
            </div>
          </div>

          <!-- 現在のライフスタイル -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">現在のライフスタイル</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['lifestyle']) ?></p>
              <input type="hidden" name="lifestyle" value="<?= h($post['lifestyle']) ?>">
            </div>
          </div>

          <!-- 医療保険やがん保険にご加入 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">医療保険やがん保険にご加入</p>
            </div>
            <div class="c-form__group__box__value">
              <?php
                $insurance_join_text = '';
                if (!empty($post['insurance_join']) && is_array($post['insurance_join'])) {
                  $insurance_join_text = implode('、', $post['insurance_join']);
                }
              ?>
              <p class="confirm__txt"><?= h($insurance_join_text) ?></p>

              <?php if (!empty($post['insurance_join']) && is_array($post['insurance_join'])): ?>
                <?php foreach ($post['insurance_join'] as $v): ?>
                  <input type="hidden" name="insurance_join[]" value="<?= h($v) ?>">
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>


          <!-- 毎月の保険料 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">毎月の保険料</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['insurance_premium']) ?></p>
              <input type="hidden" name="insurance_premium" value="<?= h($post['insurance_premium']) ?>">
            </div>
          </div>

          <!-- 保険について最も当てはまるもの -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">
                保険について最も当てはまるもの
              </p>
            </div>
            <div class="c-form__group__box__value">
              <?php
                $insurance_concern_text = '';
                if (!empty($post['insurance_concern']) && is_array($post['insurance_concern'])) {
                  $insurance_concern_text = implode('、', $post['insurance_concern']);
                }
              ?>
              <p class="confirm__txt"><?= h($insurance_concern_text) ?></p>

              <?php if (!empty($post['insurance_concern']) && is_array($post['insurance_concern'])): ?>
                <?php foreach ($post['insurance_concern'] as $v): ?>
                  <input type="hidden" name="insurance_concern[]" value="<?= h($v) ?>">
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>


          <!-- ご利用上の注意事項 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">ご利用上の注意事項</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['terms']."する") ?></p>
              <input type="hidden" name="terms" value="<?= h($post['terms']) ?>">
            </div>
          </div>

          <!-- 個人情報の取扱い -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">個人情報の取扱い</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['privacy']."する") ?></p>
              <input type="hidden" name="privacy" value="<?= h($post['privacy']) ?>">
            </div>
          </div>

          <!-- お電話にてご本人様確認ができた方が抽選の対象 -->
          <div class="c-form__group__box">
            <div class="c-form__group__box__item">
              <p class="c-form__group__box__title">お電話にてご本人様確認ができた方が抽選の対象</p>
            </div>
            <div class="c-form__group__box__value">
              <p class="confirm__txt"><?= h($post['lottery']."する") ?></p>
              <input type="hidden" name="lottery" value="<?= h($post['lottery']) ?>">
            </div>
          </div>

          <div class="c-form__button confirm__btn">
            <input type="hidden" name="__confirmed" value="1">
            <input type="hidden" name="send_token" value="<?= h($token) ?>">
            <input type="submit" value="送信する">
          </div>

        </form>

        <!-- 戻る用のPOST送信フォーム -->
        <form action="index.php" method="post">
          <?php foreach ($post as $key => $value): ?>
            <?php if (is_array($value)): ?>
              <?php foreach ($value as $v): ?>
                <input type="hidden" name="<?= h($key) ?>[]" value="<?= h($v) ?>">
              <?php endforeach; ?>
            <?php else: ?>
              <input type="hidden" name="<?= h($key) ?>" value="<?= h($value) ?>">
            <?php endif; ?>
          <?php endforeach; ?>
          <div class="c-form__button">
            <input class="-back" type="submit" value="戻る">
          </div>
        </form>
      </div>
  </div>
</div>
<!-- footer -->
<?php include 'inc/footer.php'; ?>
<!-- footer end -->

<?php
/*
// 確認用
echo '<pre>';
var_dump($_SESSION);
echo '</pre>';
*/

?>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="./js/form.js"></script>
</body>
</html>