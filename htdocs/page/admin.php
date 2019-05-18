<?php
/*
*  ログインページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
$msg = array(); //メッセージ
$err_msg = array(); //エラーメッセージ
// セッション開始
session_start();
// セッション変数に保存されたuser_idが1であるときのみuser_id取得
if (isset($_SESSION['user_id']) === TRUE && $_SESSION['user_id'] === '1') {
    $user_id = $_SESSION['user_id'];
} else {
    // user_idが1でない（＝管理人でない）場合、ログインページへリダイレクト
    header('Location: login.php');
    exit;
}
if (get_request_method() === 'POST') {
    //商品登録処理・ポストされた変数を格納
    if ((isset($_POST['sql_kind']) === TRUE) && ($_POST['sql_kind'] === 'insert')) {
        if (isset($_POST['new_name']) === TRUE) {
            $new_name = $_POST['new_name'];
        }
        if (isset($_POST['new_price']) === TRUE) {
            $new_price = $_POST['new_price'];
        }
        if (isset($_POST['new_stock']) === TRUE) {
            $new_stock = $_POST['new_stock'];
        }
        if (isset($_POST['new_status']) === TRUE) {
            $new_status = $_POST['new_status'];
        }
        //画像ファイル追加
        //もし一時的なファイル名の$_FILES['new_img']がPOSTでアップロードされていたら
        if (is_uploaded_file ($_FILES['new_img'] ['tmp_name'])) {
            //画像の一時ファイル名取得
            $tmp_img_name = $_FILES['new_img']['tmp_name'];
            //uploaded_imagesフォルダがなければ、フォルダ作成
            create_new_folder();
            //日付と擬似乱数を組み合わせて、画像の新しいファイル名を生成
            $new_img_name = date("YmdHis");
            $new_img_name .= mt_rand();
            switch (exif_imagetype ($_FILES['new_img']['tmp_name'])) {
                case IMAGETYPE_JPEG :
                    $new_img_name .= '.jpg';
                    break;
                case IMAGETYPE_PNG :
                    $new_img_name .= '.png';
                    break;
                default :
                    $err_msg[] = '画像ファイルはjpg、もしくはpngファイルのものを選んでください。';
                    break;
            }
            //続きの処理はデータをSQLへインサートした後で
        } else {
            $err_msg[] = 'ファイルを選択してください。';
        }
        //「ファイル選択」以外の項目に関するエラーメッセージを格納
        if (($result = check_name($new_name)) !== '') {
            $err_msg[] = $result;
        }
        if (($result = check_price($new_price)) !== '') {
            $err_msg[] = $result;
        }
        if (($result = check_stock($new_stock)) !== '') {
            $err_msg[] = $result;
        }
        if (($result = check_status($new_status)) !== '') {
            $err_msg[] = $result;
        }
    }
    if ((isset($_POST['sql_kind']) === TRUE) && ($_POST['sql_kind'] === 'update')) {
        if (isset($_POST['item_id']) === TRUE) {
            $item_id = $_POST['item_id'];
        }
        if (isset($_POST['update_stock']) === TRUE) {
            $update_stock = $_POST['update_stock'];
        }
        // エラーメッセージを格納
        if (($result = check_stock($update_stock)) !== '') {
            $err_msg[] = $result;
        }
    }
    if ($_POST['sql_kind'] === 'change') {
        if (isset($_POST['item_id']) === TRUE) {
            $item_id = $_POST['item_id'];
        }
        if (isset($_POST['change_status']) === TRUE) {
            $change_status = $_POST['change_status'];
        }
        // エラーメッセージを格納
        if (($result = check_status($change_status)) !== '') {
            $err_msg[] = $result;
        }
    }
    if ((isset($_POST['sql_kind']) === TRUE) && ($_POST['sql_kind'] === 'delete')) {
        if (isset($_POST['item_id']) === TRUE) {
            $item_id = $_POST['item_id'];
        }
    }
}
// データベース接続
$link = get_db_connect();
//フォームが投稿されている場合
if (get_request_method() === 'POST' && count($err_msg) === 0) {
    //新商品データ挿入
    if ($_POST['sql_kind'] === 'insert') {
        //日時を取得
        $date = date('Y-m-d H:i:s');
        //トランザクション開始(オートコミットをオフ）
        turn_off_autocommit($link);
        //挿入する商品データの連想配列を取得
        $data = array(
            'name' => $new_name,
            'price' => $new_price,
            'img' => $new_img_name,
            'status' => $new_status,
            'created_at' => $date,
            'updated_at' => $date
        );
        if (insert_new_drink_to_item_table($link, $data) === TRUE) {
            // A_Iを取得
            $item_id = mysqli_insert_id($link);
            // 挿入する商品データの連想配列を取得
            $data = array(
                'item_id' => $item_id,
                'stock' => $new_stock,
                'created_at' => $date,
                'updated_at' => $date
            );
            if (insert_new_drink_to_stock_table($link, $data) !== TRUE) {
                $err_msg[] = 'インサートエラー:EC_stock_table';
            }
        } else {
            $err_msg[] = 'インサートエラー:EC_item_table';
        }
        //インサート成功したら一時フォルダの画像ファイルをuploadフォルダへ移動させる
        if (count($err_msg) === 0) {
            //画像を一時ファイルからuploadフォルダへ移動
            if (move_uploaded_file ($tmp_img_name, '../image/uploaded_images/' . $new_img_name) === TRUE) {
            // if (move_uploaded_file ($tmp_img_name, '../../../vending_machine/upload/' . $new_img_name) === TRUE) {

                $msg[] = '商品追加成功';
            } else {
                $err_msg[] = 'アップロードに失敗しました。';
            }
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
    //在庫数変更
    if($_POST['sql_kind'] === 'update') {
        //日時を取得
        $date = date('Y-m-d H:i:s');
        //商品の在庫数を変更
        if (update_stock($link, $item_id, $update_stock, $date) === TRUE) {
            $msg[] = '在庫数変更成功';
        } else {
            $err_msg[] = '在庫数変更エラー';
        }
    }
    //ステータス変更
    if($_POST['sql_kind'] === 'change') {
        //日時を取得
        $date = date('Y-m-d H:i:s');
        //商品の在庫数を変更
        if (change_status($link, $item_id, $change_status, $date) === TRUE) {
            $msg[] = 'ステータス変更成功';
        } else {
            $err_msg[] = 'ステータス変更エラー';
        }
    }
    //商品削除
    if($_POST['sql_kind'] === 'delete') {
        // オートコミットオフ
        turn_off_autocommit($link);
        // EC_item_tableより商品を削除
        if (delete_item_from_item_table($link, $item_id) === TRUE) {
            // EC_stock_tableより商品を削除
            if(delete_item_from_stock_table($link, $item_id)) {
                $msg[] = '削除成功';
            } else {
                $err_msg[] = '削除エラー：stock_table';
            }
        } else {
            $err_msg[] = '削除エラー：item_table';
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
//商品一覧データ取得
//商品データを取得する関数
$drink_data = get_drink_data_in_admin($link);
// データベース切断
close_db_connect($link);
// 特殊文字をHTMLエンティティに変換
$drink_data = entity_assoc_array($drink_data);
include_once '../../include/view/admin.php';
?>
