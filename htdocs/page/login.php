<?php
/*
*  ログインページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
$err_msg = array(); //エラーメッセージ
// セッション開始
session_start();
// セッション変数からログイン済みか確認
if (isset($_SESSION['user_id']) === TRUE) {
    // ログイン済みの場合、ホームページへリダイレクト
    header('Location: top.php');
    exit;
}
// リクエストメソッド確認
if (get_request_method() === 'POST') {
    // POST値取得
    $user_name = get_post_data('user_name');  // ユーザー名
    $password = get_post_data('password'); // パスワード
    // データベース接続
    $link = get_db_connect();
    // メールアドレスとパスワードからuser_idを取得する
    if (($data = get_user_id($link, $user_name, $password)) === '') {
        $err_msg[] = 'ユーザーID取得失敗';
    };
    // データベース切断
    close_db_connect($link);
    // 登録データを取得できたか確認
    if (isset($data[0]['user_id'])) {
        // セッション変数にuser_idを保存
        $_SESSION['user_id'] = $data[0]['user_id'];
        $_SESSION['user_name'] = $user_name;
        //user_idが1であれば、管理用ページへ
        if ($_SESSION['user_id'] === '1') {
            // 管理ページへリダイレクト
            header('Location: admin.php');
            exit;    
        } else {
            // ログイン済みユーザのホームページへリダイレクト
            header('Location: top.php');
            exit;
        }
    } else {
       // セッション変数にログインのエラーフラグを保存
       $_SESSION['login_err_flag'] = TRUE;
       // ログインページへリダイレクト
      header('Location: login.php');
      exit;
    }
}
// セッション変数からログインエラーフラグを確認
if (isset($_SESSION['login_err_flag']) === TRUE) {
   // ログインエラーフラグ取得
   $login_err_flag = $_SESSION['login_err_flag'];
   // エラー表示は1度だけのため、フラグをFALSEへ変更
   $_SESSION['login_err_flag'] = FALSE;
} else {
   // セッション変数が存在しなければエラーフラグはFALSE
   $login_err_flag = FALSE;
}
include_once '../../include/view/login.php';
?>