<?php
  /**
   * book-search
   * https://github.com/sorakakeru/book-search
   * 
   * Copyright (c) 2025 Yamatsu
   * Released under the MIT license
   * https://github.com/sorakakeru/book-search/blob/main/LICENSE
   * 
   * This script uses the Twig template engine (BSD-3-Clause License).
   * For details about Twig's license, please refer to LICENSE_TWIG.
   */
  
  session_start();
  
	//include
	require_once __DIR__. '/_modules/fnc_inc/config.php';
	require_once __DIR__. '/_modules/fnc_inc/functions.php';
?>

<?php include_once __DIR__. '/_modules/tmpl/header.html'; ?>

<?php

  if (!file_exists($log_file)) { //$log_fileファイルの存在チェック
    errorMsg('データ保存用jsonファイルを作成してください');
  } else {

    //token確認
    if (!validate_token($_POST['token'])) {
      errorMsg('不正な操作を検出したため登録できませんでした');

    } else {
      //jsonファイル読み込み
      $data = json_decode(file_get_contents($log_file), true) ?: [];

      if (!empty($data)) {

        //IDを取得
        $id = isset($_POST['id']) ? (int)$_POST['id'] : '';

        if(empty($id)) {
          errorMsg('該当IDの蔵書データが見つかりません');
        } else {
          //該当IDのデータを取得
          $key = array_search($id , array_column($data, 'id'));
          if ($key === false) {
            errorMsg('該当IDの蔵書データが見つかりません');
          } else {  
            //削除表示
            echo '<p>『' .h($data[$key]['title']). '』の登録データを削除しました</p>' ."\n";

            //データ削除
            unset($data[$key]);
            $data = array_values($data);

            //ファイル書き込み
            if (file_put_contents($log_file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
              errorMsg('Error: 送信内容の保存に失敗しました');
            }

          }

        }

      } else {
        errorMsg('蔵書はまだ登録されていません');
      }

    }
    
  }

?>

  <hr>

	<?php include_once __DIR__. '/_modules/tmpl/search.html'; ?>

<?php include_once __DIR__. '/_modules/tmpl/footer.html'; ?>
