<?php
/**
 * トップページでパスワードを受け取りログイン認証へ飛ばす
 * 【移動先】auth.php
 */
session_start();
// セッションの初期化
$_SESSION['login'] = array();
$_SESSION['visible'] = array();
$_SESSION['invisible'] = array();
session_destroy();

require_once 'method.php';
$srv = new Page();
$srv->printOpenHeader();
$srv->printCloseHeader();
print_r("<body>\n");
print_r("<h2>MAX/C問題公開ツール</h2>\n");
print_r("<p>Version 2.11 (2014/09/25)</p><br>\n");
print_r("<form action=\"auth.php\" method=\"post\">\n");
print_r("<p>アプリケーションパスワードを入力してください。</p>\n");
print_r("<input type=\"password\" name=\"pw\"><br><br>\n");
print_r("<button type=\"submit\">Login</button></form>\n");