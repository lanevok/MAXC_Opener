<?php
/**
 * データベースへの変更前確認
 * 【遷移前】id.php
 * 【移動先】実行の場合 change.php
 */
session_start();
// error_reporting(-1);
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

// id.phpのpostデータ受け取り
$visible = null;
$invisible = null;
if(isset($_POST['visible'])) $visible = $_POST['visible'];
if(isset($_POST['invisible'])) $invisible = $_POST['invisible'];

// change.php引き継ぐため、session変数へのデータ格納
$_SESSION['visible'] = $visible;
$_SESSION['invisible'] = $invisible;

// 変更がチェックされているか確認
if(count($visible)+count($invisible)==0){
  print "何も選択されていません";
  exit(0);
}

$a = new ConnectMySQL();
$b = new Page();

$idx = 0;
// onチェックidのコピー
for($i=0; $i<count($visible); $i++){
  $request[$idx++] = array("id"=>$visible[$i]);
}
// offチェックidのコピー
for($i=0; $i<count($invisible); $i++){
  $request[$idx++] = array("id"=>$invisible[$i]);
}
// 変更idのタイトル取得
$ans2 = $a->multiSelect($request);
$ans2 = $a->getTitle($ans2);

print "<p>確認画面です。以下の情報が正しいことを確認し、最下部の変更ボタンをクリックください。</p>\n<br>";
print_r("①　↓onにチェックした公開されるレコード<br>
	※ここに明示されているレコードについて、同じレコードが④になければ、
	上位レコードが公開されていないため、実際には見えるようになりません。<br><br>\n");
// onチェックidとタイトルの吐き出し
$array_idx = 0;
$print_array = null;
for($i=0; $i<count($visible); $i++){
  foreach($ans2 as $data) {
    if ($data['id']==$visible[$i]) {
      $print_array[$array_idx++] = array("id"=>$visible[$i],"name"=>$data['name']);
    }
  }
}
$b->printConfirm($print_array, false);

print "<hr>②　↓offにチェックした非公開にされるレコード<br><br>\n";
// offチェックidとタイトルの吐き出し
$array_idx = 0;
$print_array = null;
for($i=0; $i<count($invisible); $i++){
  foreach($ans2 as $data) {
    if ($data['id']==$invisible[$i]) {
      $print_array[$array_idx++] = array("id"=>$invisible[$i],"name"=>$data['name']);
    }
  }
}
$b->printConfirm($print_array, true);

// 変更前の可視id取得
// print "<hr>before<br><br>";
$idx1 = 0;
$req[0] = array("id"=>0,"name"=>"");
while(true){
  $res = $a->multiSelectL($req);
  $req = null;
  $req = $a->getIDs($res);
  if($req==-1) break;
  for($i=0; $i<count($req); $i++){
    $a_res[$idx1++] = $req[$i];
  }
  if($req==null) break;
}
if($req!=null){
  sort($a_res);
  // 	for($i=0; $i<count($a_res); $i++){
  // 		print "[".$a_res[$i]['id']."] ".$a_res[$i]['title']."<br>";
  // 	}
}

// 変更後の可視id取得
// print "<hr>after<br><br>";
$idx2 = 0;
$req = null;
$req[0] = array("id"=>0,"name"=>"");
while(true){
  $res = $a->multiSelectL($req);
  $req = null;
  $req = $a->getIDs_compare($res, $visible, $invisible);
  if($req==-1) break;
  for($i=0; $i<count($req); $i++){
    $b_res[$idx2++] = $req[$i];
  }
  if($req==null) break;
}
if($req!=null){
  sort($b_res);
  // 	for($i=0; $i<count($b_res); $i++){
  // 		print "[".$b_res[$i]['id']."] ".$b_res[$i]['title']."<br>";
  // 	}
}

print "<hr>③　↓この変更の影響でcloseされる(見えなくなる)レコード<br><br>\n";
// 変更前のidが変更後集合になければ、そのidは不可視になる
$array_idx = 0;
$print_array = null;
for($i=0; $i<count($a_res); $i++){
  if($b_res==null||!in_array($a_res[$i], $b_res)){
    $print_array[$array_idx++] = array("id"=>$a_res[$i]['id'],"name"=>$a_res[$i]['name']);
  }
}
$b->printConfirm($print_array, false);

print "<hr>④　↓この変更の影響でopenされる(見えるようになる)レコード<br><br>\n";
// 変更後のidが変更前集合になければ、そのidは可視になる
$array_idx = 0;
$print_array = null;
for($i=0; $i<count($b_res); $i++){
  if($a_res==null||!in_array($b_res[$i], $a_res)){
    $print_array[$array_idx++] = array("id"=>$b_res[$i]['id'],"name"=>$b_res[$i]['name']);
  }
}
$b->printConfirm($print_array, true);

print_r("<br><br>\n");
print_r("<form action=\"change.php\" method=\"post\">");
print_r("<button type=\"submit\">変更</button>");
print_r("</form>");
print_r("<a href=\"./id.php?parentId=".$_SESSION['parentId']."\">戻る</a>");