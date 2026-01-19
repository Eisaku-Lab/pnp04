<?php
//XSS対応（echoする場所で使用！それ以外はNG）
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//DB接続関数：db_conn()
function db_conn()
{
    try {
        // 本番環境パス
        $db_name = ;    //データベース名
        $db_id   = ";      //アカウント名
        $db_pw   = "";          //パスワード：XAMPPはパスワード無し or MAMPはパスワード"root"に修正してください。
        $db_host = "; //DBホスト

        // local環境パス
        // $db_name = "";    //データベース名
        // $db_id   = "t";      //アカウント名
        // $db_pw   = "";          //パスワード：XAMPPはパスワード無し or MAMPはパスワード"root"に修正してください。
        // $db_host = ""; //DBホスト
        return new PDO('mysql:dbname=' . $db_name . ';charset=utf8;host=' . $db_host, $db_id, $db_pw);
    } catch (PDOException $e) {
        exit('DB Connection Error:' . $e->getMessage());
    }
}

//SQLエラー関数：sql_error($stmt)
function sql_error($stmt)
{
    $error = $stmt->errorInfo();
    exit("SQLError:" . $error[2]);
}

//リダイレクト関数: redirect($file_name)
function redirect($file_name)
{
    header("Location: " . $file_name);
    exit();
}

//SessionCheck(スケルトン)
function sschk(){
if(!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"]!=session_id()){
   exit("login.php");
}else{
   session_regenerate_id(true); //session_idを振り直す
   $_SESSION["chk_ssid"] = session_id();//新しいIDを入れ替える
}
}