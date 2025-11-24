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
  $template = $twig->load('register.html.twig');

	//include
	require_once __DIR__. '/_modules/fnc_inc/config.php';
	require_once __DIR__. '/_modules/fnc_inc/functions.php';
  

  //default
  $token = '';
  $addSuccess = false;
  $error = [];

  //ファイルの存在チェック
  $logFileExists = file_exists($log_file);

  if ($logFileExists) {
    //token生成
    if (empty($_SESSION['token'])) {
      $_SESSION['token'] = generate_token();
    }
    $token = $_SESSION['token'];
  }


  //フォーム送信処理
  if (isset($_POST['import'])) {

    //token確認
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $validateToken = validate_token($token);

    if (!$validateToken) {
      $error[] = '不正な操作を検出したため登録できませんでした';
    } else {

      if ($_FILES['csv_file']['size'] === 0 && empty($_POST['title'])) { //CSVファイルもタイトル欄も入力されていない場合
        $error[] = 'CSVファイルの指定またはタイトルは入力必須です';
      } else {

        //jsonファイル読み込み
        $data = loadBooks($log_file);

        //最大ID（登録データの末尾ID）を取得
        $base_id = (!empty($data)) ? $data[array_key_last($data)]['id'] : 0;
        $count = 0;

        //CSVファイルがセットされている場合
        if ($_FILES['csv_file']['size'] !== 0) {

          if ($_FILES['csv_file']['size'] >= 1048576) { //CSVファイルは1MBまで
            $error[] = 'CSVファイルは1MBまでしかアップロードできません';
          } elseif (is_uploaded_file($_FILES['csv_file']['tmp_name']) && $_FILES['csv_file']['error'] === 0) { //HTTP POSTでリクエストされている＆CSVファイルにエラーがなければ
            try {
              setlocale(LC_ALL, 'ja_JP.UTF-8');
              
              $fp = fopen($_FILES['csv_file']['tmp_name'], 'rb');
              while ($row = fgetcsv($fp)) {
                if ($row === [null]) continue; //空行はスキップ
                if (count($row) !== 5) { //カラム数が異なる無効なフォーマット
                  $error[] = 'CSVファイルのカラム数が違います';
                }
                
                if (!empty($row[0]) && !validateCheckIsbn($row[0])) {
                  $error[] = '『' .$row[1]. '』ISBNコードは13桁の数字で入力してください';
                  continue;
                }
                if (empty($row[1])) {
                  $error[] = 'タイトルの入力は必須です';
                  continue;
                }
                $data[] = makeBookData($base_id + 1 + $count, $row);
                $count++;
              }
              if (!feof($fp)) { //ファイルポインタが終端に達していなければエラー
                $error[] = 'CSVパースエラー';
              }
              fclose($fp);

            } catch (Exception $e) {
              $error[] = 'CSVファイルの読み込み時にエラーが発生しました';
            }
          } else {
            $error[] = '登録エラー：CSVファイルを確認してください';
          }

        } elseif (!empty($_POST['title'])) { //フォームに入力された内容を取得して登録（CSVファイルがある場合はそちらが優先）
          $row = [
            $_POST['isbn'] ?? '',
            $_POST['title'],
            $_POST['author'] ?? '',
            $_POST['publisher'] ?? '',
            isset($_POST['ebook']) ? 1 : 0
          ];
          if (!empty($row[0]) && !validateCheckIsbn($row[0])) {
            $error[] = '『' .$row[1]. '』ISBNコードは13桁の数字で入力してください';
          } else {
            $data[] = makeBookData($base_id + 1, $row);
          }
        } else {
          $error[] = 'タイトルの入力は必須です';
        }
  
        //ファイル書き込み
        $addSuccess = file_put_contents($log_file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;

      }

    }

  }

  // Twigに渡してレンダリング
  echo $template->render([
    'title' => $title,
    'token' => $token,
    'logFileExists' => $logFileExists,
    'addSuccess' => $addSuccess,
    'error' => $error,
  ]);
?>
