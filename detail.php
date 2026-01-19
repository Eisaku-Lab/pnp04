<?php
require_once('function.php');
$pdo = db_conn();

// IDを取得
$id = $_GET["id"] ?? null;

if (!$id) {
    redirect("select.php");
}

// データ取得SQL
$sql = "SELECT * FROM gm_hojokin_table WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

if ($status === false) {
    sql_error($stmt);
}

$v = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$v) {
    echo "データが見つかりません。";
    exit;
}

// JSONをデコード
$input_data = json_decode($v['input_json'], true);
$result_data = json_decode($v['result_json'], true);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>判定詳細</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ ProN W3", "Noto Sans JP", Meiryo, sans-serif;
            background-color: #F5F8FA;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #003DA5;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 15px;
            border-bottom: 4px solid #0066CC;
        }
        h2 {
            color: #003DA5;
            font-size: 20px;
            font-weight: 600;
            margin: 30px 0 15px 0;
            padding-left: 12px;
            border-left: 4px solid #0066CC;
        }
        h3 {
            color: #003DA5;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .info-section {
            background: #F0F7FF;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #D0E4FF;
        }
        .info-section p {
            margin: 10px 0;
            color: #333;
        }
        .info-section strong {
            color: #003DA5;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #003DA5;
            font-size: 15px;
        }
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #D0E4FF;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }
        textarea:focus {
            outline: none;
            border-color: #0066CC;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }
        button {
            background: linear-gradient(135deg, #0066CC 0%, #003DA5 100%);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 61, 165, 0.3);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 61, 165, 0.4);
        }
        .nav-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0066CC;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background: #F0F7FF;
            color: #003DA5;
        }
        pre {
            background: #F8F9FA;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid #E1E8ED;
            font-size: 13px;
            line-height: 1.5;
        }
        .result-card {
            background: white;
            padding: 25px;
            margin: 15px 0;
            border-radius: 10px;
            border: 2px solid #D0E4FF;
            box-shadow: 0 2px 8px rgba(0, 61, 165, 0.08);
            transition: all 0.3s;
        }
        .result-card:hover {
            box-shadow: 0 4px 16px rgba(0, 61, 165, 0.15);
            transform: translateY(-2px);
        }
        .result-card h3 {
            color: #003DA5;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .result-card p {
            margin: 10px 0;
            color: #333;
            font-size: 15px;
        }
        .result-card strong {
            color: #003DA5;
            font-weight: 600;
        }
        .result-card ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        .result-card li {
            margin: 8px 0;
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>判定詳細</h1>
        
        <a href="select.php" class="nav-link"> 一覧に戻る</a>
        
        <div class="info-section">
            <p><strong>ID:</strong> <?= h($v['id']) ?></p>
            <p><strong>セッションID:</strong> <?= h($v['session_id']) ?></p>
            <p><strong>登録日時:</strong> <?= h($v['created_at']) ?></p>
        </div>
        
        <h2>判定結果</h2>
        <?php if ($result_data && isset($result_data['candidates'])): ?>
            <?php foreach ($result_data['candidates'] as $candidate): ?>
                <div class="result-card">
                    <h3><?= $candidate['subsidy_id'] === 'monozukuri' ? 'ものづくり補助金' : '事業再構築補助金' ?></h3>
                    <p><strong>ゲート判定:</strong> <?= h($candidate['gate']) ?></p>
                    <p><strong>マッチ度スコア:</strong> <?= h($candidate['score']) ?> / 90</p>
                    <p><strong>判定理由:</strong></p>
                    <ul>
                        <?php foreach ($candidate['reasons'] as $reason): ?>
                            <li><?= h($reason) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <h2>入力データ</h2>
        <pre><?= h(json_encode($input_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
        
        <h2>メモ編集</h2>
        <form method="POST" action="update.php">
            <input type="hidden" name="id" value="<?= h($v['id']) ?>">
            <div class="form-group">
                <label>メモ:</label>
                <textarea name="memo" rows="4"><?= h($v['memo'] ?? '') ?></textarea>
            </div>
            <button type="submit">更新する</button>
        </form>
    </div>
</body>
</html>
