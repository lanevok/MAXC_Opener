<?php
/**
 * ある階層のidとタイトルまた設定状態を出力すると共に、
 * 変更したいidをユーザから受け取れるようチェックボックスを配置
 * 【遷移前】初回は index.php(auth.php)
 * 【移動先】「変更確認へ」クリック時、confirm.php
 */
session_start();
error_reporting(-1);
require_once 'method.php';
$srv = new Page();
$srv->printOpenHeader();

if(!isset($_SESSION['login'])||$_SESSION['login']==""||!isset($_SESSION['login'])){
  // ログインセッションが実行されていない
  print "<p>認証されていません</p>";
  print "<p><a href=\"./index.php\">認証へ</a></p>";
  $_SESSION['login'] = array();
  session_destroy();
  exit();
}

// 処理階層の取得と保存
$id = null;
if(isset($_GET['parentId'])) $id = $_GET['parentId'];
if($id==null) $id="0";
$_SESSION['parentId'] = $id;

print("<script type=\"text/javascript\">
<!--
function autoCheck(formName, checkboxName, bool){
	for(i=0; i<eval('document.'+formName+'.elements[\"'+checkboxName+'\"].length'); i++){
		eval('document.'+formName+'.elements[\"'+checkboxName+'\"]['+i+'].checked = '+bool);
	}
}
-->
</script>\n");
$srv->printCloseHeader();
print "<form name=\"check\" method=\"post\" action=\"./confirm.php\">\n";
$a = new ConnectMySQL();
$b = new Page();

// 処理階層idのselect
$result = $a->selectLnode($id);
// 結果の出力
$res = $b->printSelect($result,true);

print "<br><br>";

// 処理階層idを親としてもつレコードのselect
$result = $a->selectL($id);
// 結果の出力
$b->printSelect($result,false);

print "<br><br>";

if($id!=0){
  print "<a href=\"./id.php?parentId=".$res."\">上の階層へ戻る</a><br><br>";
}
print("<p>◎タイトルのリンクをクリックすることで、その階層を展開できます。</p>\n");
print("<p>◎背景色が現在の状態です。　<b><font color=\"#AFDDFF\">公開中</font>　<font color=\"#FFA18F\">非公開</font></b></p>\n");
print("<p>◎「on」にチェックを入れることで公開、「off」にチェックを入れることで非公開　への候補に入れることができます。</p>\n");
print("<p>　チェックした候補の適用は、下の「変更確認へ」ボタンで行えます。</p>\n");
print("<p>※親が非公開のままでは、公開チェックしても可視化状態が変わりません。確認画面で可視化状態の変化を見ることができます。</p>\n");
if($id!=0){
  print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'visible[]', true);\" VALUE=\"全て on ■\">\n");
  print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'visible[]', false);\" VALUE=\"全て on □\">\n");
  print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'invisible[]', true);\" VALUE=\"全て off ■\">\n");
  print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'invisible[]', false);\" VALUE=\"全て off □\">\n");
  print("<br><br>※個々にチェックできないものは、上のボタンを使ってチェックを入れても、適用されません。\n");
}
if($id==0){
  print "<a href=\"./list.php?pro=2\">全て一覧</a><br>\n";
  print "<a href=\"./list.php?pro=0\">公開一覧</a><br>\n";
  print "<a href=\"./list.php?pro=1\">非公開一覧</a><br>\n";
  print "<a href=\"./list.php?pro=3\">ディレクトリ公開一覧</a><br>\n";
  print "<a href=\"./list.php?pro=4\">ディレクトリ非公開一覧</a><br>\n";
  print "<a href=\"./list.php?pro=5\">ページ公開一覧</a><br>\n";
  print "<a href=\"./list.php?pro=6\">ページ非公開一覧</a><br>\n";
}
print "<br><br><input type=\"submit\" value=\"変更確認へ\"><br><br>\n";
print "<a href=\"./logout.php\">ログアウト</a>\n";
