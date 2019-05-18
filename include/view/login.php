<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ログインページ</title>
  <link type="text/css" rel="stylesheet" href="../css/EC.css">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="top.php">
        <p class="logo">Tee-Shop</p>
      </a>
      <a href="./cart.php" class="cart"></a>
    </div>
  </header>
  <div class="content">
<?php foreach ($err_msg as $value) { ?>
    <p class="err-msg"><?php echo $value; ?></p>
<?php }?>
    <div class="login">
      <form method="post" action="./login.php">
        <div><input type="text" name="user_name" placeholder="ユーザー名"></div>
        <div><input type="password" name="password" placeholder="パスワード">
        <div><input type="submit" value="ログイン">
      </form>
<?php if ($login_err_flag === true) { ?>
      <p class="err-msg">ユーザー名あるいはパスワードが違います</p>
<?php } ?>
      <div class="account-create">
        <a href="register.php">ユーザーの新規作成</a>
      </div>
    </div>
  </div>
</body>
</html>
