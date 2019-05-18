<?php
/*
*  新規登録ページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
$err_msg = array(); //エラーメッセージ
$success_msg = array(); //成功メッセージ
//リクエストメソッドを取得
if (get_request_method() === 'POST') {
    //POST値・時間を格納
    $user_name = get_post_data('user_name');
    $password = get_post_data('password');
    $date = date('Y-m-d H:i:s');
    //エラーメッセージ格納
    if (($result = get_err_msg($user_name, 'ユーザー名', 6, 20)) !== '') {
        $err_msg[] = $result;
    }
    if (($result = get_err_msg($password, 'パスワード', 6, 20)) !== '') {
        $err_msg[] = $result;
    }
    //エラーメッセージがなければDB接続へ
    if (count($err_msg) === 0) {
        //同じユーザー名のユーザーがいるか確認する
        // データベース接続
        $link = get_db_connect();
        //登録されたユーザー名を同じユーザーがいたら取得する
        if (($result = get_user_name($link, $user_name)) === NULL) {
            $err_msg[] = 'ユーザー名取得失敗';
        } 
        //登録されたユーザー名を同じユーザーがいなければ、新しいユーザーデータを挿入へ
        if (empty($result)) {
            //挿入情報まとめ
            $data = array(
               'user_name' => $user_name,
               'password'  => $password,
               'created_at' => $date,
               'updated_at' => $date
           );
            // SQL実行
            if (insert_db($link, $data) === TRUE) {
                $success_msg[] = 'アカウント作成を完了しました';
            } else {
                $err_msg[] = 'user_table: insertエラー';
            }
        } else {
            $err_msg[] = '既に同じ名前のユーザーが存在します。';
        }
        // データベース切断
        close_db_connect($link);
    }
}

include_once '../../include/view/register.php';
?>