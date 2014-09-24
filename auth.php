<?php
/**
 * パスワード正当性確認とログインセッション開始
 * 【遷移前】index.php
 * 【リダイレクト】id.php
 */
session_start();

require_once('config.php');
require_once 'method.php';

$srv = new Page();
$srv->printOpenHeader();
$srv->printCloseHeader();

// パスワードのエスケープ
$pw = htmlentities(htmlspecialchars($_POST['pw']));

// 空パスワード処理
if(empty($pw)||$pw==''){
  $_SESSION['login'] = array();
  session_destroy();
  print "<p>パスワードが入力されていません</p>";
  exit();
}

if(APP_PASSWORD==$pw){
  // パスワードの一致
  $_SESSION['login'] = "user";
  echo "<meta http-equiv=refresh content=0;URL='./id.php'>\n";
}
else{
  // パスワードの不一致
  $_SESSION['login'] = array();
  session_destroy();
  print "<p>パスワードが違います</p>";
}