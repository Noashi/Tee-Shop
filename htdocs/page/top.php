<?php
/*
*  トップページ
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
//データがPOSTされていた場合
if (get_request_method() === 'POST') {
    if ($_POST['sql_kind'] === 'insert_cart') {
        $item_id = $_POST['item_id'];
    }
}
// データベース接続
$link = get_db_connect();
//データがPOSTされていた場合
if (get_request_method() === 'POST') {
    //オートコミットオフ
    turn_off_autocommit($link);
    // カートに入れる商品の在庫数を取得
    $data = get_stock($link, $item_id);
    if (isset($data[0]['stock']) === TRUE) {
        $stock = (int) $data[0]['stock'];
        // 現時点での注文数を取得
        $data = get_current_amount($link, $item_id, $user_id);
        if (empty($data[0]['amount']) === FALSE) {
            $amount = (int) $data[0]['amount'];
        } else {
            //$dataが空（＝まだ注文されていない）なので、数量＝０
            $amount = 0; 
        }
        // 現在の在庫数が変更後の数量より多ければ、数量変更
        if($stock >= ($amount + 1)) {
            if (insert_drink_to_cart($link, $item_id, $user_id) === TRUE) {
                // 商品挿入成功メッセージ
                $success_msg[] = 'カートに登録しました';
            } else {
                $err_msg[] = 'カート登録失敗';
            }
        } else {
            $err_msg[] = '在庫数が足りません。在庫数' . $stock;
        }
    } else {
        $err_msg[] = '在庫がありません。';
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
//商品一覧取得
if (($drink_data = get_drink_data($link)) === '') {
    $err_msg[] = '商品一覧取得エラー';
}
// データベース切断
close_db_connect($link);
// 特殊文字をHTMLエンティティに変換
$drink_data = entity_assoc_array($drink_data);
include_once '../../include/view/top.php';
?>