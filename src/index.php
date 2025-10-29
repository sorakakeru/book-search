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
?>

<?php include_once __DIR__. '/tmpl/header.html'; ?>

  <?php include_once __DIR__. '/tmpl/search.html'; ?>

  <?php
    if (!file_exists($log_file)) { //$log_fileファイルの存在チェック
      errorMsg('データ保存用jsonファイルを作成してください');
    } else {

      //jsonファイル読み込み
      $data = json_decode(file_get_contents($log_file), true) ?: [];

      if (!empty($data)) {
        $total = count($data); //全冊数
        $total_ebook = count(array_keys(array_column($data, 'ebook'), true)); //電子書籍数

        echo '<p>蔵書数：' .$total. '冊（電子書籍' .$total_ebook. '冊を含む）</p>';
      } else {
        echo '<p>蔵書はまだ登録されていません</p>';
      }

    }
  ?>

<?php include_once __DIR__. '/tmpl/footer.html'; ?>
