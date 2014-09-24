<?php
/**
 * データベースへの変更(update)実行
 * 【遷移前】confirm.php
 * 【移動先】confirm.phpの前に開いていたid.php
 */
session_start();

require_once 'method.php';
$srv = new Page();
$srv->printOpenHeader();
$srv->printCloseHeader();

if($_SESSION['login']==""||!isset($_SESSION['login'])){
  // ログインセッションが実行されていない
  print "<p>認証されていません</p>";
  print "<p><a href=\"./index.php\">認証へ</a></p>";
  $_SESSION['login'] = array();
  $_SESSION['visible'] = array();
  $_SESSION['invisible'] = array();
  $_SESSION['parentId'] = array();
  session_destroy();
  exit();
}
else if($_SESSION['parentId']==null){
  // データベースへの変更後、不適切な遷移に対する処理
  // (一度change.phpが実行されるとsession変数のparentIdが除去される仕掛け)
  print "<p>戻るボタンなどでリロードはできません</p>";
  $_SESSION['visible'] = array();
  $_SESSION['invisible'] = array();
  print "<a href=\"./id.php\">戻る</a>";
  exit();
}

// セッション変数から更新idの取り出しとセッション変数の初期化
$visible = $_SESSION['visible'];
$invisible = $_SESSION['invisible'];
$_SESSION['visible'] = array();
$_SESSION['invisible'] = array();

// アップデートの実行
$a = new ConnectMySQL();
$a->updateMulti($visible, $invisible);

print "<p>変更が適用されました。</p>";
print "<a href=\"./id.php?parentId=".$_SESSION['parentId']."\">戻る</a>";
// 不適切な遷移対策のための初期化
$_SESSION['parentId'] = null;