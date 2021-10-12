<?php
/* 
【機能】
出荷で入力された個数を表示する。出荷を実行した場合は対象の書籍の在庫数から出荷数を
引いた数でデータベースの書籍の在庫数を更新する。

【エラー一覧（エラー表示：発生条件）】
なし
*/

//①セッションを開始する

function getByid($id,$con){
	/* 
	 * ②書籍を取得するSQLを作成する実行する。
	 * その際にWHERE句でメソッドの引数の$idに一致する書籍のみ取得する。
	 * SQLの実行結果を変数に保存する。
	 */
	$sql = "select * from books where id = " .$id;
	$stmt = $con->query( $sql );

	//⑫実行した結果から1レコード取得し、returnで値を返す。
	return $stmt->fetch( PDO::FETCH_ASSOC );
}

function updateByid($id,$con,$total){
	/*
	 * ④書籍情報の在庫数を更新するSQLを実行する。
	 * 引数で受け取った$totalの値で在庫数を上書く。
	 * その際にWHERE句でメソッドの引数に$idに一致する書籍のみ取得する。
	 */
	$stmt = $con->prepare('UPDATE books SET stock = :stock WHERE id = :id');
	$stmt->bindValue(':stock', $total);
    $stmt->bindValue(':id', $id);
	$stmt->execute();
}

//②SESSIONの「login」フラグがfalseか判定する。「login」フラグがfalseの場合はif文の中に入る。
if ($_SESSION["login"]==false){
	//③SESSIONの「error2」に「ログインしてください」と設定する。
	$_SESSION["error2"]="ログインしてください";
	//④ログイン画面へ遷移する。
	header("Location:login.php");
}

//⑧データベースへ接続し、接続情報を変数に保存する
$db_type = "mysql";	// データベースの種類
$db_host = "localhost";	// ホスト名
$db_name = "zaiko2021_yse";	// データベース名
$db_dsn = "$db_type:host=$db_host;dbname=$db_name;charset=utf8";// DSN
$db_user = "zaiko2021_yse";	// ユーザー名
$db_pass = "2021zaiko";	// パスワード

$pdo = new PDO($db_dsn,$db_user,$db_pass);
//⑦データベースで使用する文字コードを「UTF8」にする

//⑩書籍数をカウントするための変数を宣言し、値を0で初期化する

$bookcnt = 0;

//⑪POSTの「books」から値を取得し、変数に設定する。
$books = $_POST['books'];
$newStocks = $_POST['stock'];
foreach($books as $_book){
	/*
	 * ⑫POSTの「stock」について⑩の変数の値を使用して値を取り出す。
	 * 半角数字以外の文字が設定されていないかを「is_numeric」関数を使用して確認する。
	 * 半角数字以外の文字が入っていた場合はif文の中に入る。
	 */
	//if (/* ⑫の処理を書く */) {
		//⑬SESSIONの「error」に「数値以外が入力されています」と設定する。
		array_push($error_message, '数値以外が入力されています');
		//⑭「include」を使用して「syukka.php」を呼び出す。
		include"syukka.php";
		//⑮「exit」関数で処理を終了する。
		exit('プログラムを終了します');
	//}

	//⑯「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数に⑪の処理で取得した値と⑧のDBの接続情報を渡す。
	$bookId = getByid($_book,$pdo);

	//⑰ ⑯で取得した書籍の情報の「stock」と、⑩の変数を元にPOSTの「stock」から値を取り出して書籍情報の「stock」から値を引いた値を変数に保存する。
	$total = $bookId['stock'] + $newStocks[$bookcnt];

	//⑱ ⑰の値が0未満か判定する。0未満の場合はif文の中に入る。
	//if(/* ⑱の処理を行う */$total<=0){
		//⑲SESSIONの「error」に「出荷する個数が在庫数を超えています」と設定する。
		array_push($error_message, '出荷する個数が在庫数を超えています');
		//⑳「include」を使用して「syukka.php」を呼び出す。
		include"syukka.php";
		//㉑「exit」関数で処理を終了する。
		exit('プログラムを終了します');
	//}
	
	//㉒ ⑩で宣言した変数をインクリメントで値を1増やす。
	$bookcnt++;
}

/*
 * ㉓POSTでこの画面のボタンの「add」に値が入ってるか確認する。
 * 値が入っている場合は中身に「ok」が設定されていることを確認する。
 */
if(isset($_POST['add'])&&$_POST['add'] == "ok"){
	//㉔書籍数をカウントするための変数を宣言し、値を0で初期化する。
	$bookcnt = 0;
	//㉕POSTの「books」から値を取得し、変数に設定する。
	$books = $_POST['books'];
	$newStocks = $_POST['stock'];
	foreach($books as $_book){
		//㉖「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数に㉕の処理で取得した値と⑧のDBの接続情報を渡す。
		$bookId = getByid($_book,$pdo);
		//㉗ ㉖で取得した書籍の情報の「stock」と、㉔の変数を元にPOSTの「stock」から値を取り出して書籍情報の「stock」から値を引いた値を変数に保存する。
		$total = $bookId['stock'] - $newStocks[$bookcnt];
		//㉘「updateByid」関数を呼び出す。その際に引数に㉕の処理で取得した値と⑧のDBの接続情報と㉗で計算した値を渡す。
		updateByid($_book,$pdo,$total);
		//㉙ ㉔で宣言した変数をインクリメントで値を1増やす。
		$bookcnt++;
	}

	//㉚SESSIONの「success」に「入荷が完了しました」と設定する。
	array_push($success, '入荷が完了しました');
	//㉛「header」関数を使用して在庫一覧画面へ遷移する。
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>出荷確認</title>
<link rel="stylesheet" href="css/ichiran.css" type="text/css" />
</head>
<body>
<div id="header">
	<h1>出荷確認</h1>
</div>
<form action="syukka_kakunin.php" method="post" id="test">
	<div id="pagebody">
		<div id="center">
			<table>
				<thead>
					<tr>
						<th id="book_name">書籍名</th>
						<th id="stock">在庫数</th>
						<th id="stock">出荷数</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					//㉜書籍数をカウントするための変数を宣言し、値を0で初期化する。
					$bookcnt = 0;
					//㉝POSTの「books」から値を取得し、変数に設定する。
					$books = $_POST['books'];
					$newStocks = $_POST['stock'];
					foreach($books as $_book){
						//㉞「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数に㉜の処理で取得した値と⑧のDBの接続情報を渡す。
						$bookId = getByid($_book,$pdo);
					?>
					<tr>
						<td><?php echo	"{$bookId['title']}";?></td>
						<td><?php echo	"{$bookId['stock']}";?></td>
						<td><?php echo	"{$newStocks[$bookcnt]}";?></td>
					</tr>
					<input type="hidden" name="books[]" value="<?php echo $_book;?>">
					<input type="hidden" name="stock[]" value='<?php echo $newStocks[$bookcnt];?>'>
					<?php
						//㊵ ㉜で宣言した変数をインクリメントで値を1増やす。
						$bookcnt++;
					}
					?>
				</tbody>
			</table>
			<div id="kakunin">
				<p>
					上記の書籍を出荷します。<br>
					よろしいですか？
				</p>
				<button type="submit" id="message" formmethod="POST" name="add" value="ok">はい</button>
				<button type="submit" id="message" formaction="syukka.php">いいえ</button>
			</div>
		</div>
	</div>
</form>
<div id="footer">
	<footer>株式会社アクロイト</footer>
</div>
</body>
</html>
