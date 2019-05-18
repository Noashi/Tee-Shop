<?php

/**
 *
 * ------------------------common------------------------
 *
**/

/**
* 特殊文字をHTMLエンティティに変換する
*/
function entity_str($str) {
    return htmlspecialchars($str, ENT_QUOTES, HTML_CHARACTER_SET);
}

/**
* 特殊文字をHTMLエンティティに変換する(2次元配列の値)
*/
function entity_assoc_array($assoc_array) {

    foreach ($assoc_array as $key => $value) {

        foreach ($value as $keys => $values) {
            // 特殊文字をHTMLエンティティに変換
            $assoc_array[$key][$keys] = entity_str($values);
        }

    }

    return $assoc_array;

}

/**
* DBハンドルを取得
* @return obj $link DBハンドル
*/
function get_db_connect() {

    // コネクション取得
    if (!$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWD, DB_NAME)) {
        die('error: ' . mysqli_connect_error());
    }

    // 文字コードセット
    mysqli_set_charset($link, DB_CHARACTER_SET);

    return $link;
}

/**
* DBとのコネクション切断
* @param obj $link DBハンドル
*/
function close_db_connect($link) {
    // 接続を閉じる
    mysqli_close($link);
}

/**
* クエリを実行しその結果を配列で取得する
*
*/
function get_as_array($link, $sql) {

    // 返却用配列
    $data = array();

    // クエリを実行する
    if ($result = mysqli_query($link, $sql)) {

        if (mysqli_num_rows($result) > 0) {

            // １件ずつ取り出す
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }

        }

        // 結果セットを開放
        mysqli_free_result($result);

    }

    return $data;

}

/**
* insertを実行する
*
* @param obj $link DBハンドル
* @param str SQL文
* @return bool
*/
function insert_db($link, $data) {
    //ユーザー名・パスワード・時間をインサートするSQL
    $sql = 'INSERT INTO EC_user_table(user_name, password, created_at, updated_at) VALUES(\'' . implode('\',\'', $data) . '\')';

    // クエリを実行する
    if (mysqli_query($link, $sql) === TRUE) {
       return TRUE;
    } else {
       return FALSE;
    }
}

/**
* リクエストメソッドを取得
* @return str GET/POST/PUTなど
*/
function get_request_method() {
   return $_SERVER['REQUEST_METHOD'];
}

/**
* POSTデータを取得
*/
function get_post_data($key) {
   $str = '';
   if (isset($_POST[$key]) === TRUE) {
       $str = $_POST[$key];
   }
   // 特殊文字をHTMLエンティティに変換
   return entity_str($str);
}

/**
* オートコミットオフ
*/
function turn_off_autocommit($link) {

    return mysqli_autocommit($link, false);

}

/*------------------------register------------------------*/

/**
* （ユーザー新規登録）エラーメッセージを格納
*/
function get_err_msg($str, $label, $minNum, $maxNum) {
    $msg = '';
    if(trim($str) === '') {
        $msg = $label . 'を入力してください。';
    } else if (mb_strlen($str) > $maxNum || mb_strlen($str) < $minNum) {
        $msg = $label . 'は' . $minNum . '文字以上' . $maxNum . '文字以内で入力してください。';
    } else if (preg_match("/^[a-zA-Z0-9]+$/", $str) !== 1) {
        $msg = $label . 'は半角英数字で入力してください。';
    }
    return $msg;
}

/**
* （ユーザー新規登録）登録されたユーザー名を同じユーザーがいたら取得する
*/
function get_user_name($link, $user_name) {

    //データを取得するSQL
    $sql = 'SELECT user_name FROM EC_user_table WHERE user_name = "' . $user_name . '"';

    //登録データを配列で取得
    return get_as_array($link, $sql);

}

/*------------------------login------------------------*/

/**
* （ログイン）メールアドレスとパスワードからuser_idを取得する
*/
function get_user_id($link, $user_name, $password) {
    //データを取得するSQL
    $sql = 'SELECT user_id FROM EC_user_table WHERE user_name =\'' . $user_name . '\' AND password =\'' . $password . '\'';

    //登録データを配列で取得
    return get_as_array($link, $sql);

}




/*------------------------top------------------------*/

/**
* （商品一覧）商品データを取得する
*/

function get_drink_data($link) {

    //商品データを取得するSQL
    $sql = 'SELECT EC_item_table.item_id, EC_item_table.name, EC_item_table.price, EC_stock_table.stock, EC_item_table.img, EC_item_table.status, EC_item_table.created_at, EC_item_table.updated_at FROM EC_item_table JOIN EC_stock_table ON EC_item_table.item_id = EC_stock_table.item_id WHERE EC_item_table.status = 1';

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}

/**
* （商品一覧）ドリンクをカートに追加
*/

function insert_drink_to_cart ($link, $item_id, $user_id) {
    $amount = 1;
    $date = date('Y-m-d H:i:s');
    //同じユーザーが既に同じ商品を買っていないか検索するSQL
    $sql = 'SELECT cart_id FROM EC_cart_table WHERE user_id = ' . $user_id . ' AND item_id = ' . $item_id;
    // SQLを実行し商品データを配列で取得
    $data = get_as_array($link, $sql);
    // 既に同じ商品を買っていた場合、商品の入っているカートのIDを取得
    if (isset($data[0]['cart_id'])) {
        $cart_id = $data[0]['cart_id'];
        //在庫チェック
        // このカートIDのカート在庫を1増やす
        $sql = 'UPDATE EC_cart_table SET amount = amount + 1 WHERE user_id = ' . $user_id . ' AND item_id = ' . $item_id;
        if (mysqli_query($link, $sql)) {
            //カートに商品を追加できたらTRUEを返す
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        $data = array(
           'user_id' => $user_id,
           'item_id' => $item_id,
           'amount'  => $amount,
           'created_at' => $date,
           'updated_at' => $date
        );
        // カートに商品を挿入するsql
        $sql = 'INSERT INTO EC_cart_table(user_id,item_id, amount, created_at, updated_at) VALUES(\'' . implode('\',\'', $data) . '\')';
        // sqlを実行
        if (mysqli_query($link, $sql)) {
            //カートに商品を追加できたらTRUEを返す
            return TRUE;
        } else {
            return FALSE;
        }
    }
}



/*------------------------cart------------------------*/

/**
* カート内商品の注文数を取得
*/
function get_current_amount($link, $item_id, $user_id) {

    $sql = 'SELECT amount FROM EC_cart_table WHERE item_id = ' . $item_id . ' AND user_id = ' . $user_id;

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}

/**
* カート内の商品を取得
*/
function get_cart_data($user_id, $link) {

    //カートに入っている商品データを取得するSQL
    $sql = 'SELECT EC_cart_table.cart_id, EC_cart_table.item_id, EC_item_table.name, EC_item_table.price, EC_item_table.img, EC_cart_table.amount FROM EC_cart_table JOIN EC_item_table ON EC_cart_table.item_id = EC_item_table.item_id WHERE EC_cart_table.user_id = ' . $user_id;

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}

/**
* カートに入っている商品数の合計を出す
*/
function amount_price($cart_data) {
    $sum = 0;
    $i = 0;
    $cart_row_num = count($cart_data); //$cart_dataの行数（商品データの数）
    //カラムのキーナンバー$iが商品データの数より少ないとき
    while ($i < $cart_row_num) {
        //1つの商品データの合計数（価格×個数）
        $item_price = $cart_data[$i]['price'] * $cart_data[$i]['amount'];
        $sum += $item_price;
        $i++;
    }
    return $sum;
}

/**
* （購入処理）item_idからステータスを取得
*/
function get_current_status($link, $item_id) {

    $sql = 'SELECT status FROM EC_item_table WHERE item_id = ' . $item_id;

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}


/**
* 注文数チェック
*/
function check_new_amount($new_amount) {
    $msg = ''; //エラーを返す変数
    if (trim($new_amount) === '') {
        $msg = '個数を入力してください';
    } else if (ctype_digit($new_amount) !== TRUE) {
        $msg = '個数は半角数字で入力してください。';
    } else if ((int)$new_amount < 1) {
        $msg = '個数は1以上の整数で入力してください。';
    }
    return $msg;
}

/**
* カートから商品を削除
*/
function delete_item_from_cart ($link, $cart_id) {
    $sql = 'DELETE FROM EC_cart_table WHERE cart_id = ' . $cart_id;
    if (mysqli_query($link, $sql) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
* カート内の商品の数量を変更
*/

function update_amount($link, $new_amount, $cart_id, $date) {

    $sql = 'UPDATE EC_cart_table SET amount = ' . $new_amount . ', updated_at = "' . $date . '" WHERE cart_id = ' . $cart_id;

    if (mysqli_query($link, $sql) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}
/*------------------------finish------------------------*/

/**
* （購入処理）カート内商品の在庫数を取得
*/
function get_stock($link, $item_id) {

    $sql = 'SELECT stock FROM EC_stock_table WHERE item_id = ' . $item_id;

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}
/**
* （購入処理）新しい在庫数をEC_stock_tableへ挿入
*/
function update_new_stock($link, $new_stock, $item_id, $date) {

    $sql = 'UPDATE EC_stock_table SET stock = ' . $new_stock . ', updated_at = "' . $date . '" WHERE item_id = ' . $item_id;

    if (mysqli_query($link, $sql) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}


/**
* 購入処理：購入した商品をEC_cart_tableから削除
*/
function delete_bought_items($link, $user_id) {

    $sql = 'DELETE FROM EC_cart_table WHERE user_id = ' . $user_id;
    if (mysqli_query($link, $sql) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }

}
/**
* 画像を格納するフォルダがなければ作成
*/
function create_new_folder() {

    if (! file_exists ('../image/uploaded_images')) {
        mkdir ('../image/uploaded_images');
    }

}


/*------------------------admin------------------------*/

/**
* 新商品挿入：エラーチェック
*/
function check_name($new_name) {

    $msg = ''; //エラーを返す変数
    if (trim($new_name) === '') {
        $msg = '名前を入力してください';
    } else if (mb_strlen($new_name) > 20) {
        $msg = '名前は20文字以内で入力してください';
    }
    return $msg;
}

function check_price($new_price) {

    $msg = ''; //エラーを返す変数
    if (trim($new_price) === '') {
        $msg = '値段を入力してください';
    } else if (ctype_digit($new_price) !== TRUE) {
        $msg = '価格は半角数字で入力してください。';
    } else if ((int)$new_price < 0) {
        $msg = '価格は0以上の整数で入力してください。';
    } else if ((int)$new_price > 10000) {
        $msg = '価格は10000円以下の価格を入力してください。';
    }
    return $msg;

}

function check_stock($new_stock) {
    $msg = ''; //エラーを返す変数
    if (trim($new_stock) === '') {
        $msg = '個数を入力してください';
    } else if (ctype_digit($new_stock) !== TRUE) {
        $msg = '個数は半角数字で入力してください。';
    } else if ((int)$new_stock < 0) {
        $msg = '個数は0以上の整数で入力してください。';
    } else if ((int)$new_stock > 100) {
        $msg = '個数は100以下の個数を入力してください。';
    }
    return $msg;
}

function check_status($new_status) {
    $msg = ''; //エラーを返す変数
    if (ctype_digit($new_status) !== TRUE) {
        $msg = '公開情報は数字で入力してください。';
    } else if ($new_status !== "1" && $new_status !== "0") {
        $msg = '公開情報は1、または0の数字で入力してください。';
    }
    return $msg;
}

/**
* 新商品挿入：EC_item_tableへ商品データをインサート
*/
function insert_new_drink_to_item_table($link, $data) {

    $sql = 'INSERT INTO EC_item_table(name, price, img, status, created_at, updated_at) VALUES(\'' . implode('\',\'', $data) . '\')';

    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }

}

/**
* 新商品挿入：EC_stock_tableへ商品データをインサート
*/
function insert_new_drink_to_stock_table($link, $data) {

    $sql = 'INSERT INTO EC_stock_table(item_id, stock, created_at, updated_at) VALUES(\'' . implode('\',\'', $data) . '\')';

    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
* 商品アップデート：在庫数変更
*/

function update_stock($link, $item_id, $update_stock, $date) {

    $sql = 'UPDATE EC_stock_table AS S JOIN EC_item_table AS I ON S.item_id = I.item_id SET S.stock = ' . $update_stock . ', S.updated_at = "' . $date . '", I.updated_at = "' . $date . '" WHERE S.item_id = ' . $item_id;

    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
* 商品アップデート：ステータス変更
*/
function change_status($link, $item_id, $change_status, $date) {

    $sql = 'UPDATE EC_item_table SET status = ' . $change_status . ', updated_at = "' . $date . '" WHERE item_id = ' . $item_id;

    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
* 商品削除
*/
function delete_item_from_item_table($link, $item_id) {

    $sql = 'DELETE FROM EC_item_table WHERE item_id = ' . $item_id;
    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }


}

function delete_item_from_stock_table($link, $item_id) {

    $sql = 'DELETE FROM EC_stock_table WHERE item_id = ' . $item_id;
    if (mysqli_query($link, $sql)) {
        return TRUE;
    } else {
        return FALSE;
    }

}

/**
* （管理ページ）商品データを取得する
*/

function get_drink_data_in_admin($link) {

    //商品データを取得するSQL
    $sql = 'SELECT EC_item_table.item_id, EC_item_table.name, EC_item_table.price, EC_stock_table.stock, EC_item_table.img, EC_item_table.status, EC_item_table.created_at, EC_item_table.updated_at FROM EC_item_table JOIN EC_stock_table ON EC_item_table.item_id = EC_stock_table.item_id';

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}

/**
* （管理ページ）ユーザーデータを取得する
*/

function get_user_data($link) {

    //商品データを取得するSQL
    $sql = 'SELECT user_name, created_at FROM EC_user_table';

    // SQL実行し登録データを配列で取得
    return get_as_array($link, $sql);

}
