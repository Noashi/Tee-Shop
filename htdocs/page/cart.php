<?php
/*
*  カートページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
$success_msg = array(); //メッセージ
$err_msg = array(); //エラーメッセージ
//セッション開始
session_start();
// セッション変数からuser_id取得
if (isset($_SESSION['user_id']) === TRUE) {
   $user_id = $_SESSION['user_id'];
} else {
   // 非ログインの場合、ログインページへリダイレクト
  header('Location: login.php');
  exit;
}
if (isset($_SESSION['user_name']) === TRUE) {
    $user_name = $_SESSION['user_name'];
} else {
   // ユーザー名が取得できない場合、ログインページへリダイレクト
  header('Location: login.php');
  exit;
}
// データベース接続
$link = get_db_connect();
if (get_request_method() === 'POST') {
    //カート内商品削除
    if ((isset($_POST['sql_kind']) === TRUE) && ($_POST['sql_kind'] === 'delete_cart')) {
        // 削除するアイテムの注文番号（cart_id)を$delete_cart_idに格納
        $cart_id = $_POST['cart_id'];
        if (delete_item_from_cart($link, $cart_id) === TRUE) {
            $success_msg[] = '商品を削除しました。';
        } else {
            $err_msg[] = '商品削除に失敗しました。';
        }
    }
    //カート内商品の数量変更
    if ((isset($_POST['sql_kind']) === TRUE) && ($_POST['sql_kind'] === 'change_cart')) {
        $new_amount = $_POST['select_amount']; //変更後の数量
        $cart_id = $_POST['cart_id']; //数量変更する注文番号
        $item_id = $_POST['item_id']; //数量変更する商品のid
        $date = date('Y-m-d H:i:s');
        // エラーメッセージを格納
        if (($result = check_new_amount($new_amount)) !== '') {
            $err_msg[] = $result;
        }
        if (count($err_msg) === 0) {
            //オートコミットオフ
            turn_off_autocommit($link);
            // 現在の在庫数を取得
            if ($result = get_stock($link, $item_id)) {
                $stock = (int) $result[0]['stock'];
                // 現在の在庫数が変更後の数量より多ければ、数量変更
                if($stock >= $new_amount) {
                    if (update_amount($link, $new_amount, $cart_id, $date) === TRUE) {
                        $success_msg[] = '数量を変更しました。';
                    } else {
                        $err_msg[] = '数量変更に失敗しました。';
                    }
                } else {
                    $err_msg[] = '在庫数が足りません。在庫数' . $stock;
                }
            } else {
                $err_msg[] = '商品の在庫数が取得できませんでした。';
            }
            // トランザクション成否判定
           if (count($err_msg) === 0) {
               // 処理確定
               mysqli_commit($link);
           } else {
               // 処理取消
               mysqli_rollback($link);
           }
        }
    }
}
//カートに入っている商品データを取得する
$cart_data = get_cart_data($user_id, $link);
// データベース切断
close_db_connect($link);
// 特殊文字をHTMLエンティティに変換
$user_name = entity_str($user_name);
$cart_data = entity_assoc_array($cart_data);
include_once '../../include/view/cart.php';
?>
