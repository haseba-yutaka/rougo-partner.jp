
//テキスト・テキストエリアのバリデーション解除
$('input[type="text"], input[type="email"], input[type="tel"], textarea').on('input', function () {
  const val = $(this).val().trim();
  if (val !== '') {
    $(this).removeClass('input-error').addClass('input-valid');
    $(this).siblings('.c-form__group__box__error').hide();
  }
});

//セレクトボックスのバリデーション解除
$('select').on('change', function () {
  const val = $(this).val();
  if (val !== '') {
    $(this).removeClass('input-error').addClass('input-valid');
    $(this).closest('.c-form__group__box').find('.c-form__group__box__error').hide();
  }
});

// 商品チェックのエラー表示解除
$('input[name="product_dummy[]"]').on('change', function () {
  const $checkboxes = $('input[name="product_dummy[]"]');
  const $checked = $checkboxes.filter(':checked');

  if ($checked.length > 0) {
    $checkboxes.removeClass('input-error');
    $checkboxes.closest('.product-item').removeClass('input-error');
    $('.product-list').find('.c-form__group__box__error').hide();
  }
});

//ラジオボタンのバリデーション解除
$('input[type="radio"]').on('change', function () {
  const name = $(this).attr('name');
  $(`input[name="${name}"]`).each(function () {
    const label = $(`label[for="${$(this).attr('id')}"]`);
    label.removeClass('input-error').addClass('input-valid');
  });
  $(this).closest('.c-form__group__box').find('.c-form__group__box__error').hide();
});

//チェックボックスのバリデーション解除(単体)
$('input[type="checkbox"]:not([name$="[]"])').on('change', function () {
  const $item = $(this).closest('.checkbox__item');
  const $wrap = $(this).closest('.checkbox__wrap');

  if ($(this).is(':checked')) {
    $item.removeClass('input-error').addClass('input-valid');
    $(this).removeClass('input-error').addClass('input-valid');
    $wrap.find('.c-form__group__box__error').hide();
  } else {
    // 任意：外したらvalid外す（運用に合わせて）
    $item.removeClass('input-valid');
    $(this).removeClass('input-valid');
  }
});

//チェックボックスのバリデーション解除(複数選択)
$('input[type="checkbox"][name$="[]"]').on('change', function () {
  const name = $(this).attr('name'); // 例: insurance_concern[]
  const $checkboxes = $(`input[type="checkbox"][name="${name}"]`);
  const $wrap = $(this).closest('.checkbox__wrap');
  const hasChecked = $checkboxes.is(':checked');

  if (hasChecked) {
    // グループ全体のエラー解除
    $checkboxes.removeClass('input-error').addClass('input-valid');
    $checkboxes.closest('.checkbox__item').removeClass('input-error').addClass('input-valid');
    $wrap.find('.c-form__group__box__error').hide();
  } else {
    // 任意：全部外れたらvalid外す（運用に合わせて）
    $checkboxes.removeClass('input-valid');
    $checkboxes.closest('.checkbox__item').removeClass('input-valid');
  }
});




// 商品チェックのバリデーション解除（product[]用）
// $('input[name="product[]"]').on('change', function () {
//   const $checkboxes = $('input[name="product[]"]');
//   const $checked = $checkboxes.filter(':checked');

//   if ($checked.length > 0) {
//     $checkboxes.removeClass('input-error');
//     $checkboxes.closest('.product-item').removeClass('input-error');
//     $('.product-list').find('.c-form__group__box__error').hide(); // エラー文非表示
//   }
// });

//入力済みでフォントを太くする
$(function () {
  const toggleFontWeight = function () {
    const val = $(this).val().trim();
    $(this).css('font-weight', val === '' || val === $(this).find('option:first').val() ? '400' : '500');
  };

  // 適用対象
  const $fields = $('input[type="text"], input[type="email"], input[type="tel"], textarea, select');

  // 初期化＋イベントバインド
  $fields.each(toggleFontWeight).on('input change', toggleFontWeight);
});

// 郵便番号 → 住所自動入力（ZipCloud API）
$('input[name="zip"]').on('blur', function () {
  const postalCode = $(this).val().replace(/[^0-9]/g, ''); // 数字だけ抽出
  const apiUrl = `https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`;

  if (postalCode.length === 7) {
    $.ajax({
      url: apiUrl,
      dataType: 'jsonp',
      success: function (data) {
        if (data.results && data.results.length > 0) {
          const result = data.results[0];

          // 自動入力
          $('select[name="prefecture"]').val(result.address1).trigger('change');
          $('input[name="city"]').val(result.address2 + result.address3).trigger('input');

          // エラーメッセージ解除
          $('select[name="prefecture"]').removeClass('input-error').addClass('input-valid');
          $('select[name="prefecture"]').closest('.c-form__group__box__value-inner').find('.c-form__group__box__error').hide();

          $('input[name="city"]').removeClass('input-error').addClass('input-valid');
          $('input[name="city"]').siblings('.c-form__group__box__error').hide();
        } else {
          alert('該当する住所が見つかりません。郵便番号を確認してください。');
        }
      },
      error: function () {
        alert('住所検索に失敗しました。時間をおいて再度お試しください。');
      }
    });
  } else {
    alert('正しい郵便番号を入力してください。（例: 1234567）');
  }
});

// //保険のご検討について「その他」選択時に入力欄表示
// $(function () {
//   const $select = $('#insurance_plan_select');
//   const $otherBox = $('.insurance-other-box');

//   const toggleOtherBox = function () {
//     if ($select.val() === 'その他') {
//       $otherBox.stop(true, true).fadeIn(200); 
//     } else {
//       $otherBox.stop(true, true).fadeOut(200);
//     }
//   };

//   toggleOtherBox(); // 初期表示時
//   $select.on('change', toggleOtherBox);
// });

//商品選択に応じたビジュアルクラス制御
$(function () {
  $('.product-item input[type="checkbox"]').on('change', function () {
    const $label = $(this).closest('.product-item');
    if ($(this).is(':checked')) {
      $label.addClass('-checked');
    } else {
      $label.removeClass('-checked');
    }
  });

  $('.product-item input[type="checkbox"]:checked').each(function () {
    $(this).closest('.product-item').addClass('-checked');
  });
});

//連打防止
$(function () {
  $('#send-form').on('submit', function (e) {
    const $submitBtn = $(this).find('input[type="submit"], button[type="submit"]');

    if ($submitBtn.prop('disabled')) {
      e.preventDefault(); // 二重送信を防止
      return false;
    }

    $submitBtn.prop('disabled', true); // ボタン無効化

    // 表示を「送信中…」に変更
    if ($submitBtn.is('button')) {
      $submitBtn.text('送信中…');
    } else if ($submitBtn.is('input')) {
      $submitBtn.val('送信中…');
    }

    // ★オプション：表示を反映する時間を確保（0.1秒遅延）
    setTimeout(() => {
      this.submit();
    }, 100);

    e.preventDefault(); // 元の submit を一旦止める（遅延送信用）
  });
});
