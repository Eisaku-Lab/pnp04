<?php
require_once('function.php');
$pdo = db_conn();

// データ取得SQL
$sql = "SELECT id, session_id, LEFT(input_json, 100) as input_preview, memo, created_at 
        FROM gm_hojokin_table 
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$status = $stmt->execute();

if ($status === false) {
    sql_error($stmt);
}

$values = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>判定履歴一覧</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ ProN W3", "Noto Sans JP", Meiryo, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #F5F8FA;
            line-height: 1.6;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #003D82;
            border-bottom: 4px solid #0066CC;
            padding-bottom: 12px;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
        }

        th,
        td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #E1E8ED;
        }

        th {
            background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
            color: white;
            font-weight: 600;
            font-size: 15px;
            text-align: left;
        }

        tbody tr {
            transition: all 0.3s;
        }

        tbody tr:hover {
            background-color: #F5F8FA;
        }

        td {
            color: #333;
        }

        a {
            color: #0066CC;
            text-decoration: none;
            margin-right: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }

        a:hover {
            color: #004C99;
            text-decoration: underline;
        }

        .nav-link {
            display: inline-block;
            margin-bottom: 25px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
            text-decoration: none;
            color: white;
        }

        .delete-link {
            color: #DC3545;
            font-weight: 600;
        }

        .delete-link:hover {
            color: #C82333;
        }

        p {
            color: #666;
            font-size: 15px;
            padding: 20px;
            background: #F5F8FA;
            border-radius: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>判定履歴一覧</h1>
        
        <a href="index.php" class="nav-link">新規判定に戻る</a>
        
        <?php if (count($values) === 0): ?>
            <p>判定履歴がありません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>セッションID</th>
                        <th>メモ</th>
                        <th>登録日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($values as $v): ?>
                        <tr>
                            <td><?= h($v['id']) ?></td>
                            <td><?= h($v['session_id']) ?></td>
                            <td><?= h($v['memo'] ?? '') ?></td>
                            <td><?= h($v['created_at']) ?></td>
                            <td>
                                <a href="detail.php?id=<?= h($v['id']) ?>">詳細</a>
                                <a href="delete.php?id=<?= h($v['id']) ?>" class="delete-link" onclick="return confirm('本当に削除しますか？')">削除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
