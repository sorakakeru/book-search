<?php
  /**
   * book-search
   * https://github.com/sorakakeru/book-search
   * 
   * Copyright (c) 2025 Yamatsu
   * Released under the MIT license
   * https://github.com/sorakakeru/book-search/blob/main/LICENSE
   */
  
	//include
	require_once __DIR__. '/fnc_inc/config.php';
	require_once __DIR__. '/fnc_inc/functions.php';

  //token
  $token = h(generate_token());
  $_SESSION['token'] = $token;
?>

<?php include_once __DIR__. '/tmpl/header.html'; ?>

<?php

  //蔵書一覧出力
  function bookList($disp_data, $token) {
    foreach($disp_data as $value) {
      $id = h($value['id']);
      $title = h($value['title']);
      $author = (!empty($value['author'])) ? h($value['author']). '｜' : '';
      $publisher = (!empty($value['publisher'])) ? h($value['publisher']) : '';
      $ebook = (!empty($value['ebook'])) ? '　※電子書籍' : '';
      echo '<li>';
      echo '<form action="delete.php" method="post">';
      echo '<input type="hidden" name="id" value="' .$id. '">';
      echo '<input type="hidden" name="token" value="' .$token. '">';
      echo '<mark>' .$title. '</mark>' .$ebook. '<button type="submit" onclick="return confirm(\'削除する？\');">削除</button><br>' .$author.$publisher;
      echo '</form>';
      echo '</li>';
    }
  }

  if (!file_exists($log_file)) { //$log_fileファイルの存在チェック
    errorMsg('データ保存用jsonファイルを作成してください');
  } else {

    //jsonファイル読み込み
    $data = json_decode(file_get_contents($log_file), true) ?: [];

    if (!empty($data)) {

      //検索単語フィルタリング
      $search = isset($_GET['search']) && is_string($_GET['search']) ? mb_substr($_GET['search'], 0, 100) : '';
      $result = array_filter($data, function($f) use ($search) {
        return (strpos($f['title'], $search) !== false || strpos($f['author'], $search) !== false || strpos($f['publisher'], $search) !== false);
      });
      $result = array_values($result); //index振り直し
      $result_ebook = count(array_keys(array_column($result, 'ebook'), true)); //電子書籍数

      if (!empty($result)) { //検索ヒットした場合
        //ページャー設定
        $total = count($result);
        $total_pages = ceil($total / $page);
        $pager = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $start_page = ($pager - 1) * $page;
        $disp_data = array_slice($result, $start_page, $page, true);

        //検索結果
        if ($search === '') {
          echo '<p>蔵書一覧（<strong>全' .$total. '冊</strong> / 電子書籍' .$result_ebook. '冊を含む）</p>' ."\n";
        } else {
          echo '<p><strong>「' .h($search). '」</strong>を検索した結果、<strong>' .$total. '冊</strong>';
          if ($result_ebook !== 0) echo '（電子書籍' .$result_ebook. '冊を含む）';
          echo '見つかりました</p>' ."\n";
        }
        echo '<hr>' ."\n";

        //蔵書一覧
        echo '<ul class="book_list">' ."\n";
        bookList($disp_data, $token);
        echo '</ul>' ."\n";

        //ページャー
        if ($pager > $total_pages) {
          errorMsg('ページが存在しません');
        } else {
          $range = ($pager === 1 || $pager === $total_pages) ? 4 : (($pager === 2 || $pager === $total_pages - 1) ? 3 : 2);
          echo '<nav class="navigation">';

          if ($pager >= 2) {
            echo '<div class="nav prev"><a href="./search.php?search=' .h($search). '&page=' .($pager - 1). '">←</a></div>';
          }
          echo '<ol>';
          for ($i=1; $i<=$total_pages; $i++) {
            if ($i >= $pager - $range && $i <= $pager + $range) {
              if ($i == $pager) {
                echo '<li><span class="current">' .$i. '</span></li>';
              } else {
                echo '<li><a href="./search.php?search=' .h($search). '&page=' .$i. '">' .$i. '</a></li>';
              }
            }
          }
          echo '</ol>';
          if ($pager < $total_pages) {
            echo '<div class="nav next"><a href="./search.php?search=' .h($search). '&page=' .($pager + 1). '">→</a></div>';
          }

          echo '</nav>';
        }
        
      } else { //検索ヒットしなかった場合
        echo '<p>「' .h($search). '」を検索した結果、蔵書は見つかりませんでした</p>' ."\n";
      }

    } else {
      echo '<p>蔵書はまだ登録されていません</p>';
    }
    
  }

?>

  <hr>

	<?php include_once __DIR__. '/tmpl/search.html'; ?>

<?php include_once __DIR__. '/tmpl/footer.html'; ?>
