<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// unset($_SESSION);

require 'inc/parameters.php';

function h($str) {
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$post = $_SESSION['post'] ?? [];
$error = $_SESSION['error'] ?? [];

// POSTされたデータを再設定（戻るボタン用）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['confirm'])) {
  $_SESSION['post'] = $_POST;
  header('Location: index.php');
  exit;
}


//URLを取得
function getCurrentUrl() {
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
  $host = $_SERVER['HTTP_HOST'];
  $script = $_SERVER['SCRIPT_NAME']; 
  return $protocol . $host . $script;
}
$current_url = getCurrentUrl();

//年齢の範囲
$current_year = date('Y');          
$min_year = $current_year - 100; // 最年長：100歳
$max_year = $current_year - 51; // 最年少：51歳


?>

<!-- head -->
<?php include 'inc/head.php'; ?>
<!-- head end -->
<body>
<!-- header -->
<?php include 'inc/header.php'; ?>
<!-- header end -->
<?php if (!empty($error)): ?>
  <script>
    window.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('form');
      if (form) {
        form.scrollIntoView();
      }
    });
  </script>
<?php endif; ?>
<div class="form" id="form">
  <div class="form__inner">
    <div class="form__wrap">
      <h2 class="c-ttl">
        応募フォーム
      </h2>
      <ul class="step">
          <li class="step__item step__item-active">入力</li>
          <li class="step__item">確認</li>
          <li class="step__item">完了</li>
      </ul>
      <form class="c-form" action="confirm.php" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="referer_url" value="<?= h($current_url) ?>">

        <!-- プレゼント選択 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">ご希望のプレゼントを選択してください</p>
          </div>
          <div class="c-form__group__box__value">    
            <div class="radio__wrap">
              <input type="radio" id="present1" value="星野リゾート 宿泊ギフト券50,000円分" name="present"
                <?= (isset($post['present']) && $post['present'] === '星野リゾート 宿泊ギフト券50,000円分') ? 'checked' : ''; ?>>
              <label for="present1" class="c-form__group__box__radio <?= !empty($error['present']) ? 'input-error' : (!empty($post['present']) ? 'input-valid' : '') ?>">
                星野リゾート 宿泊ギフト券50,000円分
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="present2" value="UNIQLO eGift Card 10,000円" name="present"
                <?= (isset($post['present']) && $post['present'] === 'UNIQLO eGift Card 10,000円') ? 'checked' : ''; ?>>
              <label for="present2" class="c-form__group__box__radio <?= !empty($error['present']) ? 'input-error' : (!empty($post['present']) ? 'input-valid' : '') ?>">
                UNIQLO eGift Card 10,000円
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="present3" value="サイベックス リベル（アーモンドベージュ）" name="present"
                <?= (isset($post['present']) && $post['present'] === 'サイベックス リベル（アーモンドベージュ）') ? 'checked' : ''; ?>>
              <label for="present3" class="c-form__group__box__radio <?= !empty($error['present']) ? 'input-error' : (!empty($post['present']) ? 'input-valid' : '') ?>">
                サイベックス リベル（アーモンドベージュ）
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="present4" value="Roomba 105 Combo ロボット ブラック" name="present"
                <?= (isset($post['present']) && $post['present'] === 'Roomba 105 Combo ロボット ブラック') ? 'checked' : ''; ?>>
              <label for="present4" class="c-form__group__box__radio <?= !empty($error['present']) ? 'input-error' : (!empty($post['present']) ? 'input-valid' : '') ?>">
                Roomba 105 Combo ロボット ブラック
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="present5" value="厳選 魚沼産コシヒカリ" name="present"
                <?= (isset($post['present']) && $post['present'] === '厳選 魚沼産コシヒカリ') ? 'checked' : ''; ?>>
              <label for="present5" class="c-form__group__box__radio <?= !empty($error['present']) ? 'input-error' : (!empty($post['present']) ? 'input-valid' : '') ?>">
                厳選 魚沼産コシヒカリ
              </label>
            </div>
            
            <?php if (!empty($error['present']) && $error['present'] === 'blank'): ?>
              <p class="c-form__group__box__error">選択してください</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- 生年月日 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">ご自身の生年月日を教えてください</p>
          </div>
          <div class="c-form__group__box__value">
            <div class="column">
              <div class="label__select__box -width50">
                <label class="label__select">
                  <select name="birth_y" class="<?= !empty($error['birth_y']) ? 'input-error' : (!empty($post['birth_y']) ? 'input-valid' : '') ?>">
                    <option value="">年</option>
                    <?php for ($year = $max_year; $year >= $min_year; $year--): ?>
                      <?php $value = $year . '年'; ?>
                      <option value="<?= $value ?>" <?= ($post['birth_y'] ?? '') === $value ? 'selected' : '' ?>>
                        <?= $value ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </label>
                <?php if (!empty($error['birth_y']) && $error['birth_y'] === 'blank'): ?>
                  <p class="c-form__group__box__error">年を選択してください</p>
                <?php endif; ?>
              </div>
              <div class="label__select__box -width25">
                <label class="label__select">
                  <select name="birth_m" class="<?= !empty($error['birth_m']) ? 'input-error' : (!empty($post['birth_m']) ? 'input-valid' : '') ?>">
                    <option value="">月</option>
                    <?php for ($month = 1; $month <= 12; $month++): ?>
                      <option value="<?= $month; ?>" <?= ($post['birth_m'] ?? '') == $month ? 'selected="selected"' : ''; ?>>
                        <?= $month; ?>月
                      </option>
                    <?php endfor; ?>
                  </select>
                </label>
                <?php if (!empty($error['birth_m']) && $error['birth_m'] === 'blank'): ?>
                  <p class="c-form__group__box__error">月を選択してください</p>
                <?php endif; ?>
              </div>
              <div class="label__select__box -width25">
                <label class="label__select">
                  <select name="birth_d" class="<?= !empty($error['birth_d']) ? 'input-error' : (!empty($post['birth_d']) ? 'input-valid' : '') ?>">
                    <option value="">日</option>
                    <?php for ($day = 1; $day <= 31; $day++): ?>
                      <option value="<?= $day; ?>" <?= ($post['birth_d'] ?? '') == $day ? 'selected="selected"' : ''; ?>>
                        <?= $day; ?>日
                      </option>
                    <?php endfor; ?>
                  </select>
                </label>
                <?php if (!empty($error['birth_d']) && $error['birth_d'] === 'blank'): ?>
                  <p class="c-form__group__box__error">日を選択してください</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>

        <!-- お名前 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">あなたのお名前を教えてください　</p>
          </div>
          <div class="c-form__group__box__value">
            <div class="column">
              <!-- 姓 -->
              <div class="c-form__group__box__value-inner -width50">
                <div class="c-form__group__box__add">姓</div>
                <input type="text" name="last_name" class="<?= isset($error['last_name']) ? 'input-error' : (!empty($post['last_name']) ? 'input-valid' : '') ?>" placeholder="例：山田" value="<?= h($post['last_name'] ?? '') ?>">
                <?php if (!empty($error['last_name']) && $error['last_name'] === 'blank'): ?>
                  <p class="c-form__group__box__error">姓を入力してください</p>
                <?php elseif (!empty($error['last_name']) && $error['last_name'] === 'length'): ?>
                  <p class="c-form__group__box__error">姓は100文字以内で入力してください</p>
                <?php endif; ?>
              </div>
              <!-- 名 -->
              <div class="c-form__group__box__value-inner -width50">
                <div class="c-form__group__box__add">名</div>
                <input type="text" name="first_name" class="<?= isset($error['first_name']) ? 'input-error' : (!empty($post['first_name']) ? 'input-valid' : '') ?>" placeholder="例：太郎" value="<?= h($post['first_name'] ?? '') ?>">
                <?php if (!empty($error['first_name']) && $error['first_name'] === 'blank'): ?>
                  <p class="c-form__group__box__error">名を入力してください</p>
                <?php elseif (!empty($error['first_name']) && $error['first_name'] === 'length'): ?>
                  <p class="c-form__group__box__error">名は100文字以内で入力してください</p>
                <?php endif; ?>
              </div>
              <!-- セイ -->
              <div class="c-form__group__box__value-inner -width50">
                <div class="c-form__group__box__add">セイ</div>
                <input type="text" name="last_name_kana" class="<?= isset($error['last_name_kana']) ? 'input-error' : (!empty($post['last_name_kana']) ? 'input-valid' : '') ?>" placeholder="例：ヤマダ" value="<?= h($post['last_name_kana'] ?? '') ?>">
                <?php if (!empty($error['last_name_kana']) && $error['last_name_kana'] === 'blank'): ?>
                  <p class="c-form__group__box__error">セイを入力してください</p>
                <?php elseif (!empty($error['last_name_kana']) && $error['last_name_kana'] === 'length'): ?>
                  <p class="c-form__group__box__error">セイは100文字以内で入力してください</p>
                <?php endif; ?>
              </div>
              <!-- メイ -->
              <div class="c-form__group__box__value-inner -width50">
                <div class="c-form__group__box__add">メイ</div>
                <input type="text" name="first_name_kana" class="<?= isset($error['first_name_kana']) ? 'input-error' : (!empty($post['first_name_kana']) ? 'input-valid' : '') ?>" placeholder="例：タロウ" value="<?= h($post['first_name_kana'] ?? '') ?>">
                <?php if (!empty($error['first_name_kana']) && $error['first_name_kana'] === 'blank'): ?>
                  <p class="c-form__group__box__error">メイを入力してください</p>
                <?php elseif (!empty($error['first_name_kana']) && $error['first_name_kana'] === 'length'): ?>
                  <p class="c-form__group__box__error">メイは100文字以内で入力してください</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- 性別 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">ご自身の性別を教えてください　</p>
          </div>
          <div class="c-form__group__box__value">
            <div class="column">
              <div class="radio__wrap block-gender">
                <input type="radio" id="man" value="男性" name="gender"
                  <?= (isset($post['gender']) && $post['gender'] === '男性') ? 'checked' : ''; ?>>
                <label for="man" class="c-form__group__box__radio <?= !empty($error['gender']) ? 'input-error' : (!empty($post['gender']) ? 'input-valid' : '') ?>">
                  男性
                </label>
              </div>
              <div class="radio__wrap block-gender">
                <input type="radio" id="woman" value="女性" name="gender"
                  <?= (isset($post['gender']) && $post['gender'] === '女性') ? 'checked' : ''; ?>>
                <label for="woman" class="c-form__group__box__radio <?= !empty($error['gender']) ? 'input-error' : (!empty($post['gender']) ? 'input-valid' : '') ?>">
                  女性
                </label>
              </div>
            </div>
            <?php if (!empty($error['gender']) && $error['gender'] === 'blank'): ?>
              <p class="c-form__group__box__error">性別を選択してください</p>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- 住所 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">ご住所を教えてください</p>
          </div>
          <div class="c-form__group__box__value">
            <div class="column">
              <!-- 郵便番号 -->
              <div class="c-form__group__box__value-inner block-zip">
                <div class="c-form__group__box__add">郵便番号</div>
                <input type="text" name="zip" class="<?= isset($error['zip']) ? 'input-error' : (!empty($post['zip']) ? 'input-valid' : '') ?>" placeholder="1710014" value="<?= h($post['zip'] ?? '') ?>">
                <p class="c-form__group__box__note">※ハイフン不要</p>
                <p class="c-form__group__box__note">
                  ※郵便番号を入力すると住所が自動入力されます<br>
                  <a href="https://www.post.japanpost.jp/zipcode/" target="_blank">
                    郵便番号を調べる
                  </a>
                </p>
                <?php if (!empty($error['zip']) && $error['zip'] === 'blank'): ?>
                  <p class="c-form__group__box__error">郵便番号を入力してください</p>
                <?php endif; ?>
              </div>
              <!-- 都道府県 -->
              <div class="c-form__group__box__value-inner block-prefecture">
                <div class="c-form__group__box__add">都道府県</div>
                <div class="label__select__box">
                  <label class="label__select">
                    <select name="prefecture" class="<?= !empty($error['prefecture']) ? 'input-error' : (!empty($post['prefecture']) ? 'input-valid' : '') ?>">
                      <option value="">選択してください</option>
                      <?php
                      $prefectures = [
                        '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
                        '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
                        '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
                        '岐阜県', '静岡県', '愛知県', '三重県',
                        '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
                        '鳥取県', '島根県', '岡山県', '広島県', '山口県',
                        '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                      ];
                      foreach ($prefectures as $pref) {
                        $selected = ($post['prefecture'] ?? '') === $pref ? 'selected' : '';
                        echo '<option value="' . h($pref) . '" ' . $selected . '>' . h($pref) . '</option>';
                      }
                      ?>
                    </select>
                  </label>
                </div>
                <?php if (!empty($error['prefecture']) && $error['prefecture'] === 'blank'): ?>
                  <p class="c-form__group__box__error">都道府県を選択してください</p>
                <?php endif; ?>
              </div>
              <!-- 市区町村 -->
              <div class="c-form__group__box__value-inner block-city">
                <div class="c-form__group__box__add">市区町村</div>
                <input type="text" name="city" class="<?= isset($error['city']) ? 'input-error' : (!empty($post['city']) ? 'input-valid' : '') ?>" placeholder="例：東京都" value="<?= h($post['city'] ?? '') ?>">
                <?php if (!empty($error['city']) && $error['city'] === 'blank'): ?>
                  <p class="c-form__group__box__error">市区町村を入力してください</p>
                <?php endif; ?>
              </div>
              <!-- 番地・建物名 -->
              <div class="c-form__group__box__value-inner block-address_detail">
                <div class="c-form__group__box__add">番地・建物名</div>
                <input type="text" name="address_detail" class="<?= isset($error['address_detail']) ? 'input-error' : (!empty($post['address_detail']) ? 'input-valid' : '') ?>" placeholder="例：池袋2-53-5 KDX池袋ウエストビル9F" value="<?= h($post['address_detail'] ?? '') ?>">
                <?php if (!empty($error['address_detail']) && $error['address_detail'] === 'blank'): ?>
                  <p class="c-form__group__box__error">番地・建物名を入力してください</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- 電話番号 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">電話番号を教えてください</p>
          </div>
          <div class="c-form__group__box__value">
            <input type="tel" name="tel" class="<?= !empty($error['tel']) ? 'input-error' : (!empty($post['tel']) ? 'input-valid' : '') ?>" placeholder="例：08012345678" value="<?= h($post['tel'] ?? '') ?>">
            <p class="c-form__group__box__note">※ハイフン不要</p>
            <?php if (!empty($error['tel']) && $error['tel'] === 'blank'): ?>
              <p class="c-form__group__box__error">電話番号を入力してください</p>
            <?php elseif (!empty($error['tel']) && $error['tel'] === 'type'): ?>
              <p class="c-form__group__box__error">電話番号を半角数字で正しく入力してください</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- メールアドレス -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">メールアドレスを教えてください</p>
          </div>
          <div class="c-form__group__box__value">
            <input type="text" name="email" class="<?= !empty($error['email']) ? 'input-error' : (!empty($post['email']) ? 'input-valid' : '') ?>" placeholder="例：info@example.com" value="<?= h($post['email'] ?? '') ?>">
            <?php if (!empty($error['email']) && $error['email'] === 'blank'): ?>
              <p class="c-form__group__box__error">メールアドレスを入力してください</p>
            <?php elseif (!empty($error['email']) && $error['email'] === 'type'): ?>
              <p class="c-form__group__box__error">メールアドレスを半角英数字で正しく入力してください</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- ライフスタイル -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">現在のライフスタイルを教えてください</p>
          </div>
          <div class="c-form__group__box__value">    
            <div class="radio__wrap">
              <input type="radio" id="lifestyle1" value="専業主婦・主夫" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === '専業主婦・主夫') ? 'checked' : ''; ?>>
              <label for="lifestyle1" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                専業主婦・主夫
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="lifestyle2" value="フルタイム勤務" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === 'フルタイム勤務') ? 'checked' : ''; ?>>
              <label for="lifestyle2" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                フルタイム勤務
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="lifestyle3" value="パート・アルバイト勤務" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === 'パート・アルバイト勤務') ? 'checked' : ''; ?>>
              <label for="lifestyle3" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                パート・アルバイト勤務
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="lifestyle4" value="定年退職した（早期退職した）" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === '定年退職した（早期退職した）') ? 'checked' : ''; ?>>
              <label for="lifestyle4" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                定年退職した（早期退職した）
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="lifestyle5" value="自営業" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === '自営業') ? 'checked' : ''; ?>>
              <label for="lifestyle5" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                自営業
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="lifestyle6" value="その他" name="lifestyle"
                <?= (isset($post['lifestyle']) && $post['lifestyle'] === 'その他') ? 'checked' : ''; ?>>
              <label for="lifestyle6" class="c-form__group__box__radio <?= !empty($error['lifestyle']) ? 'input-error' : (!empty($post['lifestyle']) ? 'input-valid' : '') ?>">
                その他
              </label>
            </div>
            
            <?php if (!empty($error['lifestyle']) && $error['lifestyle'] === 'blank'): ?>
              <p class="c-form__group__box__error">選択してください</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- 医療保険やがん保険にご加入 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">
              医療保険やがん保険にご加入されていますか？(複数選択可)
            </p>
          </div>

          <div class="checkbox__wrap">

            <div class="checkbox__item
              <?= !empty($error['insurance_join']) ? 'input-error' : (!empty($post['insurance_join']) ? 'input-valid' : '') ?>
            ">
              <input
                type="checkbox"
                name="insurance_join[]"
                id="insurance_join_medical"
                value="医療保険に加入している"
                <?= (isset($post['insurance_join']) && is_array($post['insurance_join']) && in_array('医療保険に加入している', $post['insurance_join'], true)) ? 'checked' : '' ?>
              >
              <label for="insurance_join_medical">医療保険に加入している</label>
            </div>

            <div class="checkbox__item
              <?= !empty($error['insurance_join']) ? 'input-error' : (!empty($post['insurance_join']) ? 'input-valid' : '') ?>
            ">
              <input
                type="checkbox"
                name="insurance_join[]"
                id="insurance_join_cancer"
                value="がん保険に加入している"
                <?= (isset($post['insurance_join']) && is_array($post['insurance_join']) && in_array('がん保険に加入している', $post['insurance_join'], true)) ? 'checked' : '' ?>
              >
              <label for="insurance_join_cancer">がん保険に加入している</label>
            </div>

            <div class="checkbox__item
              <?= !empty($error['insurance_join']) ? 'input-error' : (!empty($post['insurance_join']) ? 'input-valid' : '') ?>
            ">
              <input
                type="checkbox"
                name="insurance_join[]"
                id="insurance_join_none"
                value="加入していない"
                <?= (isset($post['insurance_join']) && is_array($post['insurance_join']) && in_array('加入していない', $post['insurance_join'], true)) ? 'checked' : '' ?>
              >
              <label for="insurance_join_none">加入していない</label>
            </div>

            <div class="checkbox__item
              <?= !empty($error['insurance_join']) ? 'input-error' : (!empty($post['insurance_join']) ? 'input-valid' : '') ?>
            ">
              <input
                type="checkbox"
                name="insurance_join[]"
                id="insurance_join_unknown"
                value="わからない"
                <?= (isset($post['insurance_join']) && is_array($post['insurance_join']) && in_array('わからない', $post['insurance_join'], true)) ? 'checked' : '' ?>
              >
              <label for="insurance_join_unknown">わからない</label>
            </div>

            <?php if (!empty($error['insurance_join']) && $error['insurance_join'] === 'blank'): ?>
              <p class="c-form__group__box__error">1つ以上選択してください</p>
            <?php endif; ?>

          </div>
        </div>

        <!-- 毎月の保険料 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">毎月の保険料を教えてください</p>
          </div>
          <div class="c-form__group__box__value">    
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium1" value="〜5,000円" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === '〜5,000円') ? 'checked' : ''; ?>>
              <label for="insurance_premium1" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                〜5,000円
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium2" value="5,001〜10,000円" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === '5,001〜10,000円') ? 'checked' : ''; ?>>
              <label for="insurance_premium2" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                5,001〜10,000円
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium3" value="10,001〜15,000円" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === '10,001〜15,000円') ? 'checked' : ''; ?>>
              <label for="insurance_premium3" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                10,001〜15,000円
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium4" value="15,001〜20,000円" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === '15,001〜20,000円') ? 'checked' : ''; ?>>
              <label for="insurance_premium4" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                15,001〜20,000円
              </label>
            </div>
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium5" value="20,000円〜" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === '20,000円〜') ? 'checked' : ''; ?>>
              <label for="insurance_premium5" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                20,000円〜
              </label>
            </div>
            
            <div class="radio__wrap">
              <input type="radio" id="insurance_premium6" value="わからない" name="insurance_premium"
                <?= (isset($post['insurance_premium']) && $post['insurance_premium'] === 'わからない') ? 'checked' : ''; ?>>
              <label for="insurance_premium6" class="c-form__group__box__radio <?= !empty($error['insurance_premium']) ? 'input-error' : (!empty($post['insurance_premium']) ? 'input-valid' : '') ?>">
                わからない
              </label>
            </div>

            <?php if (!empty($error['insurance_premium']) && $error['insurance_premium'] === 'blank'): ?>
              <p class="c-form__group__box__error">選択してください</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- 保険について最も当てはまるもの（複数選択） -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">
              保険について最も当てはまるものを選んでください（複数選択）
            </p>
          </div>

          <div class="checkbox__wrap">

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_1"
                value="今の保険より良い条件のものがあれば知りたい"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('今の保険より良い条件のものがあれば知りたい', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_1">今の保険より良い条件のものがあれば知りたい</label>
            </div>

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_2"
                value="持病があっても入れる保険商品が有るか知りたい"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('持病があっても入れる保険商品が有るか知りたい', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_2">持病があっても入れる保険商品が有るか知りたい</label>
            </div>

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_3"
                value="保険料を節約したい"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('保険料を節約したい', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_3">保険料を節約したい</label>
            </div>

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_4"
                value="更新が近いので見直してみたい"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('更新が近いので見直してみたい', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_4">更新が近いので見直してみたい</label>
            </div>

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_5"
                value="自分や家族の病気やケガを保障してくれる保険が知りたい"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('自分や家族の病気やケガを保障してくれる保険が知りたい', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_5">自分や家族の病気やケガを保障してくれる保険が知りたい</label>
            </div>

            <div class="checkbox__item <?= !empty($error['insurance_concern']) ? 'input-error' : (!empty($post['insurance_concern']) ? 'input-valid' : '') ?>">
              <input type="checkbox" name="insurance_concern[]" id="insurance_concern_6"
                value="保険について困っていることはない"
                <?= (isset($post['insurance_concern']) && is_array($post['insurance_concern']) && in_array('保険について困っていることはない', $post['insurance_concern'], true)) ? 'checked' : '' ?>>
              <label for="insurance_concern_6">保険について困っていることはない</label>
            </div>

            <?php if (!empty($error['insurance_concern']) && $error['insurance_concern'] === 'blank'): ?>
              <p class="c-form__group__box__error">1つ以上選択してください</p>
            <?php endif; ?>

          </div>
        </div>


        <!-- ご利用上の注意事項 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">ご利用上の注意事項</p>
          </div>
          <div class="checkbox__wrap">
            <div class="checkbox__item <?= !empty($error['terms']) ? 'input-error' : (!empty($post['terms']) ? 'input-valid' : '') ?>">
              <input
                type="checkbox"
                name="terms"
                id="terms"
                value="同意"
                <?= ($post['terms'] ?? '') === '同意' ? 'checked' : '' ?>
              >
              <label for="terms">
                <a href="https://zenb-agency.co.jp/mama-campaign/" class="-link" target="_blank">
                  ご利用上の注意事項
                </a>に同意する
              </label>
            </div>
            <?php if (!empty($error['terms']) && $error['terms'] === 'blank'): ?>
              <p class="c-form__group__box__error">同意が必要になります</p>
            <?php endif; ?>
          </div>

        </div>


        <!-- 個人情報の取扱い -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">個人情報の取扱い</p>
          </div>
          <div class="checkbox__wrap">
            <div class="checkbox__item <?= !empty($error['privacy']) ? 'input-error' : (!empty($post['privacy']) ? 'input-valid' : '') ?>">
              <input
                type="checkbox"
                name="privacy"
                id="agree_privacy"
                value="同意"
                <?= ($post['privacy'] ?? '') === '同意' ? 'checked' : '' ?>
              >
              <label for="agree_privacy">
                <a href="https://zenb-agency.co.jp/announce/" class="-link" target="_blank">
                  個人情報の取扱い
                </a>に同意する
              </label>
            </div>
            <?php if (!empty($error['privacy']) && $error['privacy'] === 'blank'): ?>
              <p class="c-form__group__box__error">同意が必要になります</p>
            <?php endif; ?>
          </div>

        </div>

        <!-- お電話にてご本人様確認ができた方が抽選の対象 -->
        <div class="c-form__group__box">
          <div class="c-form__group__box__item">
            <p class="c-form__group__box__title -required">お電話にてご本人様確認ができた方が抽選の対象</p>
          </div>
          <div class="checkbox__wrap">
            <div class="checkbox__item <?= !empty($error['lottery']) ? 'input-error' : (!empty($post['lottery']) ? 'input-valid' : '') ?>">
              <input
                type="checkbox"
                name="lottery"
                id="agree_lottery"
                value="同意"
                <?= ($post['lottery'] ?? '') === '同意' ? 'checked' : '' ?>
              >
              <label for="agree_lottery">
                  抽選の対象に同意する
              </label>
            </div>
            <?php if (!empty($error['lottery']) && $error['lottery'] === 'blank'): ?>
              <p class="c-form__group__box__error">同意が必要になります</p>
            <?php endif; ?>
          </div>

        </div>

        <div class="c-form__group -submit">
          <div class="c-form__button">
            <button type="submit" name="confirm">確認画面へ進む</button>
          </div>
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
<script src="https://ajaxzip3.github.io/ajaxzip3.js"></script>
<script src="./js/form.js"></script>
</body>
</html>