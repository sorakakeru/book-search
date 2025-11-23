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
    
  //token
  if (empty($_SESSION['token'])) {
    $_SESSION['token'] = generate_token();
  }
  $token = h($_SESSION['token']);
?>

<?php include_once __DIR__. '/_modules/tmpl/header.html'; ?>

	<div class="form_area register">

    <h2>蔵書登録</h2>

    <?php if (!file_exists($log_file)): //$log_fileファイルの存在チェック ?>
      <p class="error">データ保存用jsonファイルを作成してください</p>
    <?php else: ?>
      <p>一括登録する場合はCSVファイルを指定、1冊ずつ登録する場合はテキストフォームに入力してください</p>

      <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        
        <h3>CSV Import</h3>
        <p>1MBファイルまでアップロードできます</p>
        <p><input type="file" name="csv_file" id="csv_file" accept=".csv"></p>

        <hr>

        <h3>手入力で登録する</h3>
        <ul>
          <li>CSVファイルがセットされている場合は、CSVファイルでの登録が優先されます</li>
          <li>タイトルは入力必須です</li>
          <li>電子書籍にチェックが入っていない場合はISBNコードの入力必須です</li>
          <li>ISBNコードが未入力の場合は、登録時に13桁の0の数字が代入されます</li>
          <li>タイトル・著者・出版社はそれぞれ255文字までしか登録されません</li>
        </ul>

        <dl>
          <dt><label for="isbn">ISBNコード（数字13桁）</label></dt>
          <dd><input type="text" name="isbn" id="isbn" placeholder="9784..."></dd>
          <dt><label for="title">タイトル <strong title="必須入力">*</strong></label></dt>
          <dd><input type="text" name="title" id="title"></dd>
          <dt><label for="author">著者</label></dt>
          <dd><input type="text" name="author" id="author"></dd>
          <dt><label for="publisher">出版社</label></dt>
          <dd><input type="text" name="publisher" id="publisher"></dd>
        </dl>

        <p><label for="ebook"><input type="checkbox" name="ebook" id="ebook">電子書籍の場合はチェック</label></p>

        <div class="btn_area">
          <button type="submit" name="import">登録</button>
        </div>
      </form>
      <script src="./js/lib.js"></script>

    <?php endif; ?>

	</div>

<?php

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

  if (isset($_POST['import'])) {

    //token確認
    if (!validate_token($_POST['token'])) {
      errorMsg('不正な操作を検出したため登録できませんでした');

    } else {

      if ($_FILES['csv_file']['size'] === 0 && empty($_POST['title'])) { //CSVファイルもタイトル欄も入力されていない場合
        errorMsg('CSVファイルの指定またはタイトルは入力必須です');
      } else {

        //jsonファイル読み込み
        $data = json_decode(file_get_contents($log_file), true) ?: [];

        //最大ID（登録データの末尾ID）を取得
        $base_id = (!empty($data)) ? $data[array_key_last($data)]['id'] : 0;
        $count = 0;

        //CSVファイルがセットされている場合
        if ($_FILES['csv_file']['size'] !== 0) {

          if ($_FILES['csv_file']['size'] >= 1048576) { //CSVファイルは1MBまで
            errorMsg('CSVファイルは1MBまでしかアップロードできません');
          } elseif (is_uploaded_file($_FILES['csv_file']['tmp_name']) && $_FILES['csv_file']['error'] === 0) { //HTTP POSTでリクエストされている＆CSVファイルにエラーがなければ
            try {
              setlocale(LC_ALL, 'ja_JP.UTF-8');
              
              $fp = fopen($_FILES['csv_file']['tmp_name'], 'rb');
              while ($row = fgetcsv($fp)) {
                if ($row === [null]) continue; //空行はスキップ
                if (count($row) !== 5) throw new RuntimeException('CSVファイルのカラム数が違います'); //カラム数が異なる無効なフォーマット
                
                if (!empty($row[0]) && !validateCheckIsbn($row[0])) {
                  errorMsg('ISBNコードは13桁の数字で入力してください', $row[1] ?? '');
                  continue;
                }
                if (empty($row[1])) {
                  errorMsg('タイトルの入力は必須です', $row[1] ?? '');
                  continue;
                }
                $data[] = makeBookData($base_id + 1 + $count, $row);
                $count++;
              }
              if (!feof($fp)) throw new RuntimeException('CSVパースエラー'); //ファイルポインタが終端に達していなければエラー
              fclose($fp);

            } catch (Exception $e) {
              errorMsg('CSVファイルの読み込み時にエラーが発生しました');
            }
          } else {
            errorMsg('登録エラー：CSVファイルを確認してください');
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
            errorMsg('ISBNコードは13桁の数字で入力してください', $row[1] ?? '');
          } else {
            $data[] = makeBookData($base_id + 1, $row);
          }
        } else {
          errorMsg('タイトルは入力必須です');
        }
                  
        //ファイル書き込み
        if (file_put_contents($log_file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
          errorMsg('Error: 送信内容の保存に失敗しました');
        } else {
          echo '<p class="success">蔵書登録を完了しました</p>';
        }

      }

    }
  }
?>

<?php include_once __DIR__. '/_modules/tmpl/footer.html'; ?>
