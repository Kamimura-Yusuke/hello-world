<?php
	//sql実行
	function sql_execute($pdo,$sql,$name,$comment,$date,$post_pass){
		$sql = $pdo -> prepare($sql);
		$sql -> bindParam(':name', $name, PDO::PARAM_STR);
		$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
		$sql -> bindParam(':date', $date, PDO::PARAM_STR);
		$sql -> bindParam(':pass', $post_pass, PDO::PARAM_STR);
		$sql -> execute();
	}
	
	//特定行または全てのデータの取得
	function select_and_get($pdo,$id){
		if($id==null) {
			$sql = 'SELECT * FROM keiziban2';
			$stmt = $pdo->query($sql); //変数含まない(入力がない)場合→query()
		}else {
			$sql = 'SELECT * FROM keiziban2 WHERE id=:id';
			$stmt = $pdo->prepare($sql);                  //変数含む(入力がある)場合→prepare()  ＊prepare()を使わずに直接代入する事もできるが、SQLインジェクションを防ぐため、推奨されない
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		}
		$results = $stmt->fetchAll(); //sqlを配列として返す
		return $results;
	}

	//DB接続
	$dsn = 'mysql:dbname=*******;host=localhost';
	$user = '******';
	$password = '******';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS keiziban2"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"
	. "name TEXT,"
	. "comment TEXT,"
	. "date TEXT,"
	. "pass TEXT"
	.");";
	$pdo->query($sql);
	
	//データベースに保存,編集処理
	if((isset($_POST["name"]))&&(isset($_POST["comment"]))&&(isset($_POST["post_pass"]))){
	$name = htmlspecialchars($_POST["name"]);
	$comment = htmlspecialchars($_POST["comment"]);
	$post_pass=htmlspecialchars($_POST["post_pass"]);
	if(strpos($comment,PHP_EOL)) $comment=str_replace(PHP_EOL,' ',$comment);
	$date=date("Y/m/d H:i:s");
	
	if($_POST["edit_id2"]!=""){
		$edit_id2=$_POST["edit_id2"];
		$results=select_and_get($pdo,$edit_id2);
		foreach($results as $get_results){
		if($post_pass==$get_results['pass']){
		$sql = 'UPDATE keiziban2 SET name=:name,comment=:comment ,date=:date, pass=:pass where id='.$edit_id2;
		sql_execute($pdo,$sql,$name,$comment,$date,$post_pass);
		}else echo "パスワードが違います"."<br>";
		}}else{
		$sql = 'INSERT INTO keiziban2 (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)';
		sql_execute($pdo,$sql,$name,$comment,$date,$post_pass);
		}
	}
	
	//コメントの削除処理
	if((isset($_POST["delete_id"]))&&(isset($_POST["delete_pass"]))){
	$delete_id=$_POST["delete_id"];
	$delete_pass=htmlspecialchars($_POST["delete_pass"]);
	$results=select_and_get($pdo,$delete_id);
	foreach($results as $get_results){
		if($delete_pass==$get_results['pass']){
			$sql = 'DELETE FROM keiziban2 WHERE id=:id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
			$stmt->execute();
			}else echo "パスワードが違います"."<br>";
		}
	}
	
	//編集対象取得
	if((isset($_POST["edit_id"]))&&(isset($_POST["edit_pass"]))){
	$edit_id=$_POST["edit_id"];
	$edit_pass=htmlspecialchars($_POST["edit_pass"]);
	$results=select_and_get($pdo,$edit_id); //取得されるのは一行だけだが、配列で返さないとなぜか上手くいかない…
	foreach($results as $get_results){
		if($edit_pass==$get_results['pass']){
				$get_name=$get_results['name'];
				$get_comment=$get_results['comment'];
			}else echo "パスワードが違います"."<br>";
		}
	}
?>

<html>
<meta charset="utf-8">
<form method="POST" action="mission_5-1.php">
	名前：<input type="text" name="name" value="<?php if(isset($get_name))echo $get_name;?>" required><br>
	コメント：<textarea name="comment" required><?php if(isset($get_comment))echo $get_comment;?></textarea><br>
	パスワード：<input type="text" name="post_pass"  required><br>
	<input type="hidden" name="edit_id2" value="<?php if(isset($edit_id))echo $edit_id;?>" >
	<input type="submit" value="送信">
</form>
<form method="POST" action="mission_5-1.php">
	削除対象番号：<input type="text" name="delete_id" required><br>
	パスワード：<input type="text" name="delete_pass"  required><br>
	<input type="submit" value="削除">
</form>
<form method="POST" action="mission_5-1.php">
	編集対象番号：<input type="text" name="edit_id" required><br>
	パスワード：<input type="text" name="edit_pass"  required><br>
	<input type="submit" value="編集">
</form>
<hr>
</html>

<?php
	if(isset($pdo)){
	$null_id=null;
	$results = select_and_get($pdo,$null_id);
	foreach ($results as $row){
		echo "ID:{$row['id']}  名前:{$row['name']}  コメント:{$row['comment']}  日付:{$row['date']}"."<br>";
	 }
	 $pdo=null; //接続切断
	}
?>