
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>購入完了ページ</title>
  <link type="text/css" rel="stylesheet" href="../css/EC.css">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="./top.php">
        <p class="logo">Tee-Shop</p>
      </a>
      <a class="nemu" href="./logout.php">ログアウト</a>
      <a href="./cart.php" class="cart"></a>
      <p class="nemu">ユーザー名：sasasa</p>
    </div>
  </header>
  <div class="content">
<?php foreach ($err_msg as $value) { ?>
    <p class="err-msg"><?php echo $value; ?></p>
<?php } ?>
<?php if (empty($cart_data) === TRUE) { ?>
    <p class="err-msg">カートは空です。</p>
<?php } ?>
<!--トランザクションが成功したときのみ、メッセージと購入内容を表示-->
<?php if (empty($cart_data) === FALSE && count($err_msg) === 0) { ?>
    <div class="finish-msg">ご購入ありがとうございました。</div>
    <div class="cart-list-title">
      <span class="cart-list-price">価格</span>
      <span class="cart-list-num">数量</span>
    </div>
      <ul class="cart-list">
<?php foreach ($cart_data as $value) { ?>
          <li>
            <div class="cart-item">
              <img class="cart-item-img" src="../image/uploaded_images/<?php echo $value['img']; ?>">
              <span class="cart-item-name"><?php echo $value['name']; ?></span>
              <span class="cart-item-price"><?php echo $value['price']; ?>円</span>
              <span class="finish-item-price"><?php echo $value['amount']; ?></span>
            </div>
          </li>
<?php } ?>
      </ul>
    <div class="buy-sum-box">
      <span class="buy-sum-title">合計</span>
      <span class="buy-sum-price"><?php echo amount_price($cart_data); ?>円</span>
    </div>
<?php } ?>
  </div>
</body>
</html>
