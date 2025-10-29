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
    return hash('sha256', session_id());
  }

  //CSRFトークン検証
  function validate_token($token) {
    return $token === generate_token();
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