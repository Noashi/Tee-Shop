<?php
/*
*  ログインページ
*/
require_once '../../include/conf/const.php';
require_once '../../include/model/function.php';
// セッション開始
session_start();
// セッション変数からログイン済みか確認
if (isset($_SESSION['user_id']) === TRUE && $_SESSION['user_id'] === '1') {
    $user_id = $_SESSION['user_id'];
} else {
    // 非ログインの場合、ログインページへリダイレクト
    header('Location: login.php');
    exit;
}
// データベース接続
$link = get_db_connect();
// ユーザーデータ取得
$user_data = get_user_data($link);
// データベース切断
close_db_connect($link);
// 特殊文字をHTMLエンティティに変換
$user_data = entity_assoc_array($user_data);
include_once '../../include/view/admin_user.php';
?>