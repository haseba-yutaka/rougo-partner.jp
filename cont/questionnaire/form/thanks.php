<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (empty($_SESSION['form'])) {
  header('Location: index.php');
  exit;
}

// セッション破棄
unset($_SESSION['form']);
unset($_SESSION['post']);
unset($_SESSION['error']);
unset($_SESSION['referer']);
?>

<!-- head -->
<?php include 'inc/head.php'; ?>
<!-- head end -->
<body>
<!-- header -->
<?php include 'inc/header.php'; ?>
<!-- header end -->
<div class="thanks form" id="thanks">
  <div class="form__inner">
    <div class="form__wrap">
        <h2 class="c-ttl">
          キャンペーンご応募ありがとうございます
        </h2>
        <ul class="step">
            <li class="step__item">入力</li>
            <li class="step__item">確認</li>
            <li class="step__item step__item-active">完了</li>
        </ul>
        <div class="thanks__note">
          <p class="thanks__note__wrap">
          まだこのページを<br class="is-sp">閉じないでください！
          </p>
        </div>
        <p class="thanks__txt -large">
          <span>お電話</span>で<span>ご本人様確認</span>が<br class="is-sp">できた方のみ<br><span>抽選対象</span>となります。
        </p>
        <p class="thanks__txt">
          以下の電話番号からお電話があります。
        </p>
        <p class="thanks__txt -large">
          0120-990-832
        </p>
        <p class="thanks__txt">
          万が一出られなかった場合は折り返しお電話ください。
        </p>
        <p class="thanks__txt">
          お急ぎの場合やお返事がない場合はお手数ですが下記の連絡先までご連絡ください。
        </p>
        <p class="thanks__txt">
          <a href="mailto:mama-all@zenb-agency.co.jp">mama-all@zenb-agency.co.jp</a>
        </p>
        <div class="thanks__btn">
            <a href="./">
              フォームに戻る
            </a>
        </div>
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
