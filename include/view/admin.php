<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>商品管理ページ</title>
  <link type="text/css" rel="stylesheet" href="../css/admin.css">
</head>
<body>
  <h1>Tee-Shop 管理ページ</h1>
  <div>
    <a class="nemu" href="./logout.php">ログアウト</a>
    <a href="./admin_user.php">ユーザ管理ページ</a>
  </div>
<?php foreach ($msg as $value) { ?>
<p class="success-msg"><?php echo $value; ?></p>
<?php }?>
<?php foreach ($err_msg as $value) { ?>
<p class="err-msg"><?php echo $value; ?></p>
<?php }?>
  <section>
    <h2>商品の登録</h2>
    <form method="post" enctype="multipart/form-data">
      <div><label>商品名: <input type="text" name="new_name" value=""></label></div>
      <div><label>値　段: <input type="text" name="new_price" value=""></label></div>
      <div><label>個　数: <input type="text" name="new_stock" value=""></label></div>
      <div><label>商品画像:<input type="file" name="new_img"></label></div>
      <div><label>ステータス:
        <select name="new_status">
          <option value="0">非公開</option>
          <option value="1" selected>公開</option>
        </select>
        </label>
      </div>
      <input type="hidden" name="sql_kind" value="insert">
      <div><input type="submit" value="商品を登録する"></div>
    </form>
  </section>
  <section>
    <h2>商品情報の一覧・変更</h2>
    <table>
      <tr>
        <th>商品画像</th>
        <th>商品名</th>
        <th>価　格</th>
        <th>在庫数</th>
        <th>ステータス</th>
        <th>操作</th>
      </tr>
<?php foreach ($drink_data as $value) { ?>
<?php if ($value['status'] === '0') {?>
      <tr class="status_false">
<?php } else if ($value['status'] === '1') { ?>
      <tr>
<?php } ?>
        <form method="post">
          <td><img class="img_size" src="../image/uploaded_images/<?php echo $value['img']; ?>"></td>
          <td class="name_width"><?php echo $value['name']; ?></td>
          <td class="text_align_right"><?php echo $value['price']; ?>円</td>
          <td><input type="text"  class="input_text_width text_align_right" name="update_stock" value="<?php echo $value['stock']; ?>">個&nbsp;&nbsp;<input type="submit" value="変更する"></td>
          <input type="hidden" name="item_id" value="<?php echo $value['item_id'] ?>;">
          <input type="hidden" name="sql_kind" value="update">
        </form>
        <form method="post">
          <td>
<?php if ($value['status'] === '0') { ?>
                        <input type="submit" value="非公開→公開">
                        <input type="hidden" name="change_status" value="1">
<?php } else if ($value['status'] === '1') { ?>
                        <input type="submit" value="公開→非公開">
                        <input type="hidden" name="change_status" value="0">
<?php } ?>
          <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
          <input type="hidden" name="sql_kind" value="change">
        </form>
        <form method="post">
          <td><input type="submit" value="削除する"></td>
          <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
          <input type="hidden" name="sql_kind" value="delete">
        </form>
      </tr>
<?php } ?>
    </table>
  </section>
</body>
</html>
