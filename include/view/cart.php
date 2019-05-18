<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ショッピングカートページ</title>
  <link type="text/css" rel="stylesheet" href="../css/EC.css">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="top.php">
        <p class="logo">Tee-Shop</p>
      </a>
      <a class="nemu" href="logout.php">ログアウト</a>
      <a href="cart.php" class="cart"></a>
      <p class="nemu">ユーザー名：<?php echo $user_name; ?></p>
    </div>
  </header>
  <div class="content">
    <h1 class="title">ショッピングカート</h1>
<?php foreach ($success_msg as $value) { ?>
    <p class="success-msg"><?php echo $value; ?></p>
<?php } ?>
<?php foreach ($err_msg as $value) { ?>
    <p class="err-msg"><?php echo $value; ?></p>
<?php } ?>
<?php if (empty($cart_data) === TRUE) { ?>
    <p class="err-msg">カートは空です。</p>
<?php } ?>
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
          <form class="cart-item-del" action="./cart.php" method="post">
            <input type="submit" value="削除">
            <input type="hidden" name="cart_id" value="<?php echo $value['cart_id']; ?>">
            <input type="hidden" name="sql_kind" value="delete_cart">
          </form>
          <span class="cart-item-price"><?php echo $value['price']; ?>円</span>
          <form class="form_select_amount" id="form_select_amount118" action="cart.php" method="post">
            <input type="text" class="cart-item-num2" min="0" name="select_amount" value="<?php echo $value['amount']; ?>">個&nbsp;<input type="submit" value="変更する">
            <input type="hidden" name="cart_id" value="<?php echo $value['cart_id']; ?>">
            <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
            <input type="hidden" name="sql_kind" value="change_cart">
          </form>
        </div>
      </li>
<?php } ?>
    </ul>
    <div class="buy-sum-box">
      <span class="buy-sum-title">合計</span>
      <span class="buy-sum-price"><?php echo amount_price($cart_data); ?>円</span>
    </div>
    <div>
      <form action="./finish.php" method="post">
        <input class="buy-btn" type="submit" value="購入する">
      </form>
    </div>
  </div>
</body>
</html>
