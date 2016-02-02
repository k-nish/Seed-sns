<?php 
  $dsn = 'mysql:dbname=seed_sns;host=localhost';
   // 接続するためのユーザー情報
  $user = 'root';
  $password = '';
  // DB接続オブジェクトを作成
  $dbh = new PDO($dsn,$user,$password);
 // 接続したDBオブジェクトで文字コードutf8を使うように指定
  $dbh->query('SET NAMES utf8');
  ?>	