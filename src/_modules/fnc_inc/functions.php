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

  //jsonファイル読み込み
  function loadBooks($file) {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
  }
  
  //CSRFトークン生成
  function generate_token() {
    return bin2hex(random_bytes(32));
  }

  //CSRFトークン検証
  function validate_token($token) {
    //送信されてきた$tokenが生成したハッシュと一致するか
    return isset($_SESSION['token']) && hash_equals($_SESSION['token'], $token);
  }

  //ISBNコードチェック
  function validateCheckIsbn($isbn) {
    return is_numeric($isbn) && preg_match('/^\d{13}$/', $isbn);
  }

  //書籍データ配列作成
  function makeBookData($id, $row) {
    return [
      'id' => $id,
      'isbn' => !empty($row[0]) && validateCheckIsbn($row[0]) ? $row[0] : '0000000000000',
      'title' => mb_substr($row[1], 0, 255),
      'author' => mb_substr($row[2], 0, 255),
      'publisher' => mb_substr($row[3], 0, 255),
      'date' => date('Y-m-d'),
      'ebook' => (isset($row[4]) && (int)$row[4] === 1)
    ];
  }

  /*
  //XSS対策
  function h($str) {
    return htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
  }
  */

?>