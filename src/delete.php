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

  //Twig
  require_once __DIR__. '/_modules/vendor/autoload.php';
  $loader = new \Twig\Loader\FilesystemLoader(__DIR__. '/_modules/tmpl');
  $twig = new \Twig\Environment($loader, []);
  $template = $twig->load('delete.html.twig');
  
	//include
	require_once __DIR__. '/_modules/fnc_inc/config.php';
	require_once __DIR__. '/_modules/fnc_inc/functions.php';


  //default
  $validateToken = false;
  $deleteTitle = isset($_POST['title']) ? $_POST['title'] : '';
  $deleteSuccess = false;
  $error = [];


  //ファイルの存在チェック
  $logFileExists = file_exists($log_file);

  if ($logFileExists) {
    //jsonファイル読み込み
    $data = loadBooks($log_file);

    //データの存在チェック
    $dataExists = !empty($data);

    //処理
    if ($dataExists) {
      //token確認
      $token = isset($_POST['token']) ? $_POST['token'] : '';
      $validateToken = validate_token($token);

      if (!$validateToken) {
        $error[] = '不正な操作を検出したため削除できませんでした';
      } else {
        //IDを取得
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $idExists = !empty($id);

        if (!$idExists) {
          $error[] = '該当IDの蔵書データが見つかりません';
        } else {
          //該当IDのデータを取得
          $key = array_search($id , array_column($data, 'id'));

          if ($key === false) {
            $error[] = '該当IDの蔵書データが見つかりません';
          } else {
            //データ削除
            unset($data[$key]);
            $data = array_values($data);

            //ファイル書き込み
            $deleteSuccess = file_put_contents($log_file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
          }
        }

      }

    }

  }

  // Twigに渡してレンダリング
  echo $template->render([
    'title' => $title,
    'validateToken' => $validateToken,
    'logFileExists' => $logFileExists,
    'dataExists' => $dataExists,
    'deleteTitle' => $deleteTitle,
    'deleteSuccess' => $deleteSuccess,
    'error' => $error
  ]);
?>
