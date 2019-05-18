<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>商品一覧ページ</title>
  <link type="text/css" rel="stylesheet" href="../css/EC.css">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="./top.php">
        <p class="logo">Tee-Shop</p>
      </a>
      <a class="nemu" href="./logout.php">ログアウト</a>
      <a href="cart.php" class="cart"></a>
      <p class="nemu">ユーザー名：<?php echo $user_name;?></p>
    </div>
  </header>
  <div class="content">
<?php foreach ($success_msg as $value) {?>
<p class="success-msg"><?php echo $value; ?></p>
<?php } ?>
<?php foreach ($err_msg as $value) { ?>
    <p class="err-msg"><?php echo $value; ?></p>
<?php } ?>
    <ul class="item-list">
<?php foreach ($drink_data as $value) { ?>
      <li>
        <div class="item">
          <form action="./top.php" method="post">
            <img class="item-img" src="<?php echo '../image/uploaded_images/' . $value['img'];?>" >
            <div class="item-info">
              <span class="item-name"><?php echo $value['name']?></span>
              <span class="item-price">¥<?php echo $value['price']?></span>
            </div>
<?php if ($value['stock'] > 0) { ?>
            <input class="cart-btn" type="submit" value="カートに入れる">
<?php } else if ($value['stock'] <= 0) { ?>
            <p class="sold-out">売り切れ</p>
<?php } ?>
            <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
            <input type="hidden" name="sql_kind" value="insert_cart">
          </form>
        </div>
      </li>
<?php } ?>
    </ul>
  </div>
</body>
</html>
