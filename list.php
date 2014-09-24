<?php
/**
 * ある条件のレコードをすべて出力すると共に、
 * 変更したいidをユーザから受け取れるようチェックボックスを配置
 * 【遷移前】id.php
 * 【移動先】「変更確認へ」クリック時、confirm.php
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
  session_destroy();
  exit();
}

// 処理モードの取得と処理階層の仮想保存
$pro = $_GET['pro'];
$_SESSION['parentId'] = "0";

print("<script type=\"text/javascript\">
<!--
function autoCheck(formName, checkboxName, bool){
	for(i=0; i<eval('document.'+formName+'.elements[\"'+checkboxName+'\"].length'); i++){
		eval('document.'+formName+'.elements[\"'+checkboxName+'\"]['+i+'].checked = '+bool);
	}
}
-->
</script>\n");
print "<form name=\"check\" method=\"post\" action=\"./confirm.php\">\n";

$a = new ConnectMySQL();
$b = new Page();
print "<br><br>\n";

// 処理モードの場合分け
if($pro==0)
  $result = $a->selectVisible(1);
else if($pro==1)
  $result = $a->selectVisible(0);
else if($pro==2)
  $result = $a->selectAll();
else if($pro==3)
  $result = $a->selectNodeVisible(1);
else if($pro==4)
  $result = $a->selectNodeVisible(0);
else if($pro==5)
  $result = $a->selectPageVisible(1);
else if($pro==6)
  $result = $a->selectPageVisible(0);

$b->printSelect($result,false);

print "<br><br>\n";
print "<a href=\"./id.php\">戻る</a><br><br>\n";
print("<p>◎タイトルのリンクをクリックすることで、その階層を展開できます。</p>\n");
print("<p>◎背景色が現在の状態です。　<b><font color=\"#AFDDFF\">公開中</font>　<font color=\"#FFA18F\">非公開</font></b></p>\n");
print("<p>◎「on」にチェックを入れることで公開、「off」にチェックを入れることで非公開　への候補に入れることができます。</p>\n");
print("<p>　チェックした候補の適用は、下の「変更確認へ」ボタンで行えます。</p>\n");
print("<p>※親が非公開のままでは、公開チェックしても可視化状態が変わりません。確認画面で可視化状態の変化を見ることができます。</p>\n");
print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'visible[]', true);\" VALUE=\"全て on ■\">\n");
print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'visible[]', false);\" VALUE=\"全て on □\">\n");
print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'invisible[]', true);\" VALUE=\"全て off ■\">\n");
print("<INPUT TYPE=\"button\" onClick=\"autoCheck('check', 'invisible[]', false);\" VALUE=\"全て off □\">\n");
print("<br><br>※個々にチェックできないものは、上のボタンを使ってチェックを入れても、適用されません。\n");
print "<br><br><input type=\"submit\" value=\"変更確認へ\"><br><br>\n";
print "<a href=\"./logout.php\">ログアウト</a>\n";
