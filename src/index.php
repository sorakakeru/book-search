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

  //Twig
  require_once __DIR__. '/_modules/vendor/autoload.php';
  $loader = new \Twig\Loader\FilesystemLoader(__DIR__. '/_modules/tmpl');
  $twig = new \Twig\Environment($loader, []);
  $template = $twig->load('index.html.twig');
  
	//include
	require_once __DIR__. '/_modules/fnc_inc/config.php';
	require_once __DIR__. '/_modules/fnc_inc/functions.php';
  
  //default
  $total = 0;
  $total_ebook = 0;


  //ファイルの存在チェック
  $logFileExists = file_exists($log_file);

  if ($logFileExists) {
    //jsonファイル読み込み
    $data = loadBooks($log_file);

    //データの存在チェック
    $dataExists = !empty($data);
    
    //処理
    if ($dataExists) {
      $total = count($data); //全冊数
      $total_ebook = count(array_filter($data, fn($b) => !empty($b['ebook']))); //電子書籍数
    }
    
  }

  // Twigに渡してレンダリング
  echo $template->render([
    'title' => $title,
    'logFileExists' => $logFileExists,
    'dataExists' => $dataExists,
    'total' => $total,
    'totalEbook' => $total_ebook
  ]);
?>
