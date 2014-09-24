<?php
/**
 * セッション破棄によるログアウト
 * 【移動先】再び認証できるよう index.php
 */
session_start();

require_once 'method.php';
$srv = new Page();
$srv->printOpenHeader();
$srv->printCloseHeader();

$_SESSION['login'] = array();
$_SESSION['visible'] = array();
$_SESSION['invisible'] = array();
$_SESSION['parentId'] = array();
if(isset($_COOKIE['login'])){
  setcookie("login",'',time()-86400,'/');
}
session_destroy();
print "<p>ログアウトしました</p>";
print "<p><a href=\"./index.php\">認証へ</a></p>";
exit();