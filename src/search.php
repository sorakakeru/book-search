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
  $template = $twig->load('search.html.twig');
  
	//include
	require_once __DIR__. '/_modules/fnc_inc/config.php';
	require_once __DIR__. '/_modules/fnc_inc/functions.php';


  //default
  $token = '';
  $search_word = '';
  $books = [];
  $result = [];
  $total = 0;
  $result_ebook = 0;
  $pager = 1;
  $total_pages = 1;
  
  
  //ファイルの存在チェック
  $logFileExists = file_exists($log_file);

  if ($logFileExists) {
    //jsonファイル読み込み
    $data = loadBooks($log_file);

    //データの存在チェック
    $dataExists = !empty($data);
  
    //処理
    if ($dataExists) {
      //token生成
      if (empty($_SESSION['token'])) {
        $_SESSION['token'] = generate_token();
      }
      $token = $_SESSION['token'];

      //$data逆順に
      $data = array_reverse($data);

      //検索単語フィルタリング
      $search_word = isset($_GET['search']) && is_string($_GET['search']) ? mb_substr($_GET['search'], 0, 100) : '';
      $result = array_filter($data, function($f) use ($search_word) {
        return (strpos($f['title'], $search_word) !== false || strpos($f['author'], $search_word) !== false || strpos($f['publisher'], $search_word) !== false);
      });
      $result = array_values($result); //index振り直し
      $result_ebook = count(array_filter($result, fn($b) => !empty($b['ebook']))); //電子書籍数
    }

    //ページャー設定
    $total = count($result);
    $total_pages = (int)ceil($total / $page);
    $pager = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $start_page = ($pager - 1) * $page;
    $books = array_slice($result, $start_page, $page, true);
    
  }

  // Twigに渡してレンダリング
  echo $template->render([
    'title' => $title,
    'token' => $token,
    'logFileExists' => $logFileExists,
    'dataExists' => $dataExists,
    'searchWord' => $search_word,
    'books' => $books,
    'resultTotal' => $total,
    'resultEbook' => $result_ebook,
    'pager' => $pager,
    'totalPages' => $total_pages
  ]);

?>
