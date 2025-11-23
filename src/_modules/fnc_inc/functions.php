<?php
  /**
   * book-search
   * https://github.com/sorakakeru/book-search
   * 
   * Copyright (c) 2025 Yamatsu
   * Released under the MIT license
   * https://github.com/sorakakeru/book-search/blob/main/LICENSE
   */
  
  //CSRFトークン生成
  function generate_token() {
    return bin2hex(random_bytes(32));
  }

  //CSRFトークン検証
  function validate_token($token) {
    //送信されてきた$tokenが生成したハッシュと一致するか
    return isset($_SESSION['token']) && hash_equals($_SESSION['token'], $token);
  }

  //XSS対策
  function h($str) {
    return htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
  }

  //エラーメッセージ表示
  function errorMsg($msg, $title = '') {
    echo '<p class="error">';
    if ($title !== '') { echo '『' .h($title). '』'; }
    echo h($msg);
    echo '</p>';
  }
?>