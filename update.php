<?php
require_once('function.php');

// POSTデータ取得
$id = $_POST["id"] ?? null;
$memo = $_POST["memo"] ?? null;

if (!$id) {
    redirect("select.php");
}

// DB接続
$pdo = db_conn();

// 更新SQL作成
$sql = "UPDATE gm_hojokin_table SET memo = :memo WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':memo', $memo, PDO::PARAM_STR);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// 処理後
if ($status === false) {
    sql_error($stmt);
} else {
    redirect("select.php");
}
