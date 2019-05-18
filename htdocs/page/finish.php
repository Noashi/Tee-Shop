<?php
/*
*  トップページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
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
// セッション変数からuser_name取得
if (isset($_SESSION['user_name']) === TRUE) {
    $user_name = $_SESSION['user_name'];
} else {
   // ユーザー名が取得できない場合、ログインページへリダイレクト
  header('Location: login.php');
  exit;
}
if (get_request_method() === 'POST') {
    // データベース接続
    $link = get_db_connect();
    //カートに入っている商品データを取得する
    $cart_data = get_cart_data($user_id, $link);
    // 特殊文字をHTMLエンティティに変換
    $cart_data = entity_assoc_array($cart_data);
    //購入処理の準備
    $cart_row_num = count($cart_data); //$cart_dataの行数を取得（商品データの数）
    $i = 0; //変数
    //オートコミットオフ
    turn_off_autocommit($link);
    //商品ごとの在庫数から、購入数を減らす処理
    while ($i < $cart_row_num) {
        //購入処理に必要な変数を格納
        $item_id = $cart_data[$i]['item_id'];
        $item_name = $cart_data[$i]['name'];
        $amount = $cart_data[$i]['amount'];
        $date = date('Y-m-d H:i:s');
        //item_idからステータス取得
        if ($result = get_current_status($link, $item_id)) {
            $status = $result[0]['status'];
            //ステータスが公開状態になっているか確認
            if ($status === '1') {
                //カート内商品の在庫数を取得
                if ($result = get_stock($link, $item_id)) {
                    $stock = $result[0]['stock'];
                    //在庫数が注文数を上回っているか確認
                    if ($stock >= $amount) {
                        $new_stock = $stock - $amount; //在庫数から購入数を減らした数
                        //SQL:stock数を更新
                        if (update_new_stock($link, $new_stock, $item_id, $date) !== TRUE) {
                            $err_msg[] = '在庫数更新エラー';
                        }
                    } else {
                        $err_msg[] = $item_name . 'の在庫数が足りません。在庫数:' . $stock;
                    }
                } else {
                    $err_msg[] = '在庫数取得エラー';
                }
            } else {
                $err_msg[] = $item_name . 'は購入できません。';
            }
        } else {
            $err_msg[] = 'ステータス取得エラー。';
        }
        $i++;
    }
    //購入した注文番号(cart_id)を削除
    if (delete_bought_items($link, $user_id) !== TRUE) {
        $err_msg[] = '購入処理エラー:DELETE';
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
include_once '../../include/view/finish.php';
?>