<?php
require_once('function.php');

// GETデータ取得
$id = $_GET["id"] ?? null;

if (!$id) {
    redirect("select.php");
}

// DB接続
$pdo = db_conn();

// 削除SQL作成
$stmt = $pdo->prepare("DELETE FROM gm_hojokin_table WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// 処理後
if ($status === false) {
    sql_error($stmt);
} else {
    redirect("select.php");
}
