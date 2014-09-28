<?php
error_reporting(-1);
require_once 'config.php';
/**
 * MySQLデータベースと接続し操作するクラス
 */
Class ConnectMySQL {
  var $dbh;

  /**
   * 初回MySQLデータベースコネクションを取る
   */
  public function ConnectMySQL(){
    try {
      $dsn = 'mysql:dbname='.SCHEMA.';host='.HOST;
      $options = array(
		       PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		       //					PDO::ATTR_PERSISTENT => true
		       );
      try {
	$this->dbh = new PDO($dsn, USER, PASSWORD, $options);
      } catch (Exception $e) {
	print_r("<script type=\"text/javascript\">
				<!--
				console.log(\"pdo construct warning?\");
				//-->
				</script>");
      }
      if ($this->dbh == null){
	print('データベースへの接続ができませんでした');
	die();
      }
    } catch (PDOException $e){
      print ('Error:' . $e->getMessage());
      die();
    }
  }

  /**
   * 複数の(update)sql文を実行する
   */
  private function doUpdate($sql){
    try {
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $this->dbh->beginTransaction();
      for($i=0; $i<count($sql); $i++){
	print_r("<script type=\"text/javascript\">
				<!--
				console.log(\"".$sql[$i]."\");
				//-->
				</script>");
	$this->dbh->exec($sql[$i]);
      }
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->dbh->rollBack();
      echo "Error: " . $e->getMessage();
      die();
    }
  }

  /**
   * (select)sql文を実行する
   */
  private function doSelect($sql){
    try {
      $stmt = $this->dbh->query($sql);
      return $stmt;
    } catch ( PDOException $e ) {
      print ('Error:' . $e->getMessage());
      die();
    }
  }

  /**
   * データベースとのコネクションを消す
   */
  public function close(){
    $dbh = null;
  }

  /**
   * 公開するid配列と非公開にするid配列を受け取り、複数のsql文組立て、
   * sqlを実行する。
   */
  public function updateMulti($visible, $invisible){
    $idx = 0;
    for($i=0; $i<count($visible); $i++){
      $sql[$idx++] = 'UPDATE '.TABLE.' SET published=b\'1\' WHERE id='.$visible[$i];
    }
    for($i=0; $i<count($invisible); $i++){
      $sql[$idx++] = 'UPDATE '.TABLE.' SET published=b\'0\' WHERE id='.$invisible[$i];
    }
    return $this->doUpdate($sql);
  }

  /**
   * 指定された親idを持つレコードをselect実行する
   */
  public function selectL($l){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE.' WHERE parentId='.$l;
    return $this->doSelect($sql);
  }

  /**
   * 指定された親id配列の要素いずれかを持つレコードをselect実行する
   */
  public function multiSelectL($l){
    $sql = 'SELECT id,name,typeStr,published FROM '.TABLE.' WHERE parentId='.$l[0]['id'];
    for($i=1; $i<count($l); $i++){
      $sql = $sql . ' or parentId=' . $l[$i]['id'];
    }
    return $this->doSelect($sql);
  }

  /**
   * 指定されたid配列の要素いずれかを持つレコードをselect実行する
   */
  public function multiSelect($l){
    $sql = 'SELECT id,name,typeStr,published FROM '.TABLE.' WHERE id='.$l[0]['id'];
    for($i=1; $i<count($l); $i++){
      $sql = $sql . ' or id=' . $l[$i]['id'];
    }
    return $this->doSelect($sql);
  }

  /**
   * 指定されたidを持つレコードをselect実行する
   */
  public function selectLnode($l){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE.' WHERE id='.$l;
    return $this->doSelect($sql);
  }

  /**
   * テーブルすべてのレコードをselect実行する
   */
  public function selectAll(){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE;
    return $this->doSelect($sql);
  }

  /**
   * 引数の公開設定がされているレコードをselect実行する
   */
  public function selectVisible($flag){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE.' WHERE published=b\''.$flag.'\'';
    return $this->doSelect($sql);
  }

  /**
   * 引数の公開設定がされているノードレコードをselect実行する
   */
  public function selectNodeVisible($flag){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE.' WHERE  typeStr="NODE" AND published=b\''.$flag.'\'';
    return $this->doSelect($sql);
  }

  /**
   * 引数の問題設定がされているノードではないレコードをselect実行する
   */
  public function selectPageVisible($flag){
    $sql = 'SELECT id,parentId,name,typeStr,published FROM '.TABLE.' WHERE  typeStr<>"NODE" AND published=b\''.$flag.'\'';
    return $this->doSelect($sql);
  }

  /**
   * sql結果から、公開設定されているidとnameを配列で取得する
   */
  function getIDs($stmt){
    $idx = 0;
    if($stmt->rowCount()==0) return -1;
    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
      if(ord($result['published'])>1)
	$result['published'] = ord($result['published'])-48;
      else $result['published'] = ord($result['published']);
      if($result['published']){
	$answer[$idx++] = array("id"=>$result['id'],"name"=>$result['name']);
      }
    }
    if(!isset($answer)) $answer = null;
    return $answer;
  }

  /**
   * sql結果から、idとnameを配列で取得する
   */
  function getTitle($stmt){
    $idx = 0;
    if($stmt->rowCount()==0) return -1;
    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
      $answer[$idx++] = array("id"=>$result['id'],"name"=>$result['name']);
    }
    return $answer;
  }

  /**
   * sql結果と、公開するレコード配列、非公開にするレコード配列を用いて
   * 実際にその階層で可視状態であるレコードのidとnameを配列で取得する
   */
  function getIDs_compare($stmt, $visible, $invisible){
    $idx = 0;
    if($stmt->rowCount()==0) return -1;
    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
      if($visible!=null&&in_array($result['id'], $visible)){
	$answer[$idx++] = array("id"=>$result['id'],"name"=>$result['name']);
      }
      else if($invisible!=null&&in_array($result['id'], $invisible)){
	continue;
      }
      else{
	if(ord($result['published'])>1)
	  $result['published'] = ord($result['published'])-48;
	else $result['published'] = ord($result['published']);
	if($result['published']){
	  $answer[$idx++] = array("id"=>$result['id'],"name"=>$result['name']);
	}
      }
    }
    if(!isset($answer)) $answer = null;
    return $answer;
  }
}

/**
 * htmlページの出力をサポートするクラス
 */
Class Page{

  /**
   * sqlの結果を表に出力する。
   * flagを立てると、強制的にディレクトリのリンクを貼らない
   * チェックボックスが出力できるので、id.php画面で使用する
   */
  public function printSelect($stmt, $flag){
    if($stmt->rowCount()==0) return;
    print("<table border=\"1\">");
    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
      print("<tr>");
      // 			if((int)bin2hex($result['published'])==1) print("<tr bgcolor=\"AFEEEE\">");
      // 			if((int)bin2hex($result['published'])==0) print("<tr bgcolor=\"EFA18F\">");
      // 			var_dump(ord($result['published']));
      if(ord($result['published'])>1)
	$result['published'] = ord($result['published'])-48;
      else $result['published'] = ord($result['published']);
      print_r("<script type=\"text/javascript\">
			<!--
			console.log(\"".$result['published']."\");
			//-->
			</script>");
      if($result['published']) print("<tr bgcolor=\"AFEEEE\">");		//blue
      if(!$result['published']) print("<tr bgcolor=\"EFA18F\">");		//red
      print("<td>");	print($result['id']);			print("</td>\n");
      print("<td>");
      if(!$flag&&$result['typeStr']=="NODE"){
	print "<a href=\"./id.php?parentId=";
	print ($result['id']);
	print "\">";
      }
      print($result['name']);	print "</a>";		print("</td>\n");
      print("<td>on");	print("<input type=\"checkbox\" name=\"visible[]\" value=\"".$result['id']."\""
			      .($result['published']?" disabled":"").">");
      print("</td>\n");
      print("<td>off");	print("<input type=\"checkbox\" name=\"invisible[]\" value=\"".$result['id']."\""
			      .(!$result['published']?" disabled":"").">");
      print("</td>\n");
      print("</tr>\n");
      $res = $result['parentId'];
    }
    print("</table>");
    return $res;
  }

  /**
   * sqlの結果を表に出力する。
   * visibleFlagが立てば、背景を青(意味:可視,公開)で出力する。
   * confirm.phpの確認画面で使用する
   */
  public function printConfirm($array, $visibleFlag){
    if(count($array)==0){
      print "<p>何もありません。</p>";
      return;
    }
    print("<table border=\"1\">");
    for($i=0; $i<count($array); $i++){
      print("<tr>");
      if($visibleFlag) print("<tr bgcolor=\"AFEEEE\">");
      if(!$visibleFlag) print("<tr bgcolor=\"EFA18F\">");
      print("<td>");	print($array[$i]['id']);			print("</td>");
      print("<td>");
      print($array[$i]['name']);
      print("</td>");
      print("</tr>\n");
    }
    print("</table>");
  }

  /**
   * html先頭のhead記述をする
   */
  public function printOpenHeader(){
    print_r("<html>\n");
    print_r("<head>\n");
    print_r("<title>MAX/C問題公開ツール</title>\n");
    print_r("<meta name=\"Author\" content=\"Koike Tatsuya\">\n");
    print_r("<meta name=\"robots\" content=\"noindex,nofollow\">\n");
    print_r("<meta name=\"description\" content=\"MAX/C問題の公開を簡単にするためのツール\">\n");
    print_r("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n");
  }

  /**
   * html先頭のheadを閉じる出力をする
   */
  public function printCloseHeader(){
    print_r("</head>\n");
  }
}
