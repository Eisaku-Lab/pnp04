<?php
//データベース接続
require_once('function.php');

//POSTかどうか
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    //データ取得
    $data = file_get_contents('php://input');
    $data2 = json_decode($data, true);

    //セッションID
    $sid = uniqid('session_', true);

    //判定結果を入れる配列
    $result = array();

    //ものづくり補助金の判定
    $score1 = 0;
    $reasons1 = array();
    $name1 = 'ものづくり補助金';

    //従業員数チェック
    if ($data2['company']['employees_count'] >= 1) {
        $score1 = $score1 + 10;
        $reasons1[] = '従業員がいる企業です';
    }
    if ($data2['company']['employees_count'] >= 20) {
        $score1 = $score1 + 10;
        $reasons1[] = '従業員20人以上の企業です';
    }

    //業種チェック
    if ($data2['company']['industry'] == 'manufacturing') {
        $score1 = $score1 + 15;
        $reasons1[] = '製造業です';
    }

    //新規性チェック
    if ($data2['project']['novelty_product'] == 'yes') {
        $score1 = $score1 + 20;
        $reasons1[] = '新規性のある製品・サービスです';
    }
    if ($data2['project']['novelty_market'] == 'yes') {
        $score1 = $score1 + 15;
        $reasons1[] = '新規市場開拓が期待できます';
    }

    //設備投資チェック
    if ($data2['investment']['includes_machine_or_system'] == 'yes') {
        $score1 = $score1 + 20;
        $reasons1[] = '設備・システム投資が含まれています';
    }

    //投資額チェック
    if ($data2['investment']['total_amount_yen'] >= 1000000) {
        $score1 = $score1 + 10;
        $reasons1[] = '投資額が100万円以上です';
    }

    //評価を決める
    $eval1 = '';
    if ($score1 >= 70) {
        $eval1 = '非常に適合';
    } else if ($score1 >= 50) {
        $eval1 = '適合';
    } else if ($score1 >= 30) {
        $eval1 = 'やや適合';
    } else {
        $eval1 = '要検討';
    }

    //結果を配列に入れる
    $result[] = array(
        'subsidy_id' => 'monozukuri',
        'subsidy_name' => $name1,
        'score' => $score1,
        'evaluation' => $eval1,
        'reasons' => $reasons1
    );

    //事業再構築補助金の判定
    $score2 = 0;
    $reasons2 = array();
    $name2 = '事業再構築補助金';

    //従業員数チェック
    if ($data2['company']['employees_count'] >= 1) {
        $score2 = $score2 + 10;
        $reasons2[] = '従業員がいる企業です';
    }
    if ($data2['company']['employees_count'] >= 50) {
        $score2 = $score2 + 10;
        $reasons2[] = '従業員50人以上の中規模企業です';
    }

    //再構築タイプチェック
    if ($data2['rebuild']['restructuring_type'] == 'new_field') {
        $score2 = $score2 + 20;
        $reasons2[] = '新分野展開による再構築です';
    }
    if ($data2['rebuild']['restructuring_type'] == 'business_shift') {
        $score2 = $score2 + 25;
        $reasons2[] = '事業転換による再構築です';
    }
    if ($data2['rebuild']['restructuring_type'] == 'industry_shift') {
        $score2 = $score2 + 25;
        $reasons2[] = '業種転換による再構築です';
    }

    //売上比率チェック
    if ($data2['rebuild']['new_business_sales_ratio_band'] == 'gt_30') {
        $score2 = $score2 + 20;
        $reasons2[] = '新事業の売上比率が30%超を計画しています';
    }

    //投資額チェック
    if ($data2['investment']['total_amount_yen'] >= 5000000) {
        $score2 = $score2 + 10;
        $reasons2[] = '投資額が500万円以上です';
    }

    //評価を決める
    $eval2 = '';
    if ($score2 >= 70) {
        $eval2 = '非常に適合';
    } else if ($score2 >= 50) {
        $eval2 = '適合';
    } else if ($score2 >= 30) {
        $eval2 = 'やや適合';
    } else {
        $eval2 = '要検討';
    }

    //結果を配列に入れる
    $result[] = array(
        'subsidy_id' => 'rebuild',
        'subsidy_name' => $name2,
        'score' => $score2,
        'evaluation' => $eval2,
        'reasons' => $reasons2
    );

    //レスポンス作成
    $res = array('session_id' => $sid, 'candidates' => $result);

    //データベースに保存
    $pdo = db_conn();
    $sql = "INSERT INTO gm_hojokin_table (session_id,input_json,result_json,created_at) VALUES (:sid,:input,:result,NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':sid', $sid, PDO::PARAM_STR);
    $stmt->bindValue(':input', $data, PDO::PARAM_STR);
    $stmt->bindValue(':result', json_encode($res, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
    $stmt->execute();

    //JSON出力
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>補助金マッチング</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ ProN W3", "Noto Sans JP", Meiryo, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #E3F2FD 0%, #F5F8FA 100%);
            line-height: 1.7;
            min-height: 100vh;
        }

        .container {
            background: white;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 61, 165, 0.12);
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0066CC 0%, #00A0E9 50%, #0066CC 100%);
        }

        h1 {
            color: #003D82;
            border-bottom: 4px solid #0066CC;
            padding-bottom: 15px;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            letter-spacing: 0.5px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100px;
            height: 4px;
            background: #00A0E9;
        }

        h2 {
            color: #003D82;
            font-size: 22px;
            font-weight: 600;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 5px solid #0066CC;
            position: relative;
            background: linear-gradient(90deg, rgba(0, 102, 204, 0.05) 0%, transparent 100%);
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
        }

        p {
            color: #555;
            margin-bottom: 25px;
            font-size: 15px;
            line-height: 1.8;
        }

        .form-group {
            margin-bottom: 28px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #003D82;
            font-size: 15px;
            position: relative;
            padding-left: 12px;
        }

        label::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background: #0066CC;
            border-radius: 2px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #D0E4FF;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        input:hover,
        select:hover,
        textarea:hover {
            border-color: #A0C8F0;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0066CC;
            box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.1);
            transform: translateY(-1px);
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230066CC' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
            appearance: none;
        }

        button {
            background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
            color: white;
            padding: 16px 50px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 17px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.35);
            position: relative;
            overflow: hidden;
            margin-top: 15px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        button:hover::before {
            left: 100%;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0, 102, 204, 0.45);
        }

        button:active {
            transform: translateY(-1px);
        }

        button:disabled {
            background: linear-gradient(135deg, #CCCCCC 0%, #999999 100%);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        button:disabled::before {
            display: none;
        }

        #results {
            margin-top: 50px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            padding: 35px;
            margin-bottom: 30px;
            border-radius: 16px;
            border: 2px solid #D0E4FF;
            box-shadow: 0 8px 24px rgba(0, 61, 165, 0.12);
            border-left: 8px solid #0066CC;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .result-card:hover {
            box-shadow: 0 12px 36px rgba(0, 102, 204, 0.2);
            transform: translateY(-4px);
            border-left-width: 12px;
        }

        .result-card h3 {
            color: #003D82;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .score {
            font-size: 64px;
            font-weight: 800;
            color: #0066CC;
            margin: 20px 0;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 8px rgba(0, 102, 204, 0.15);
            line-height: 1;
        }

        .score::after {
            content: '点';
            font-size: 24px;
            margin-left: 8px;
            font-weight: 600;
        }

        .evaluation {
            font-size: 22px;
            font-weight: 700;
            margin: 18px 0;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            position: relative;
            z-index: 1;
        }

        .eval-excellent {
            color: #0066CC;
            background: rgba(0, 102, 204, 0.1);
            border: 2px solid #0066CC;
        }

        .eval-good {
            color: #00A0E9;
            background: rgba(0, 160, 233, 0.1);
            border: 2px solid #00A0E9;
        }

        .eval-fair {
            color: #FF9800;
            background: rgba(255, 152, 0, 0.1);
            border: 2px solid #FF9800;
        }

        .eval-poor {
            color: #F57C00;
            background: rgba(245, 124, 0, 0.1);
            border: 2px solid #F57C00;
        }

        .reasons {
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        .reasons strong {
            color: #003D82;
            font-size: 16px;
            display: block;
            margin-bottom: 12px;
        }

        .reasons ul {
            list-style-type: none;
            padding: 0;
        }

        .reasons li {
            margin: 12px 0;
            padding: 15px 20px;
            background: white;
            border-radius: 10px;
            border-left: 4px solid #0066CC;
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            position: relative;
            padding-left: 45px;
        }

        .reasons li::before {
            content: '✓';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            background: #0066CC;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }

        .reasons li:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
        }

        .nav-link {
            display: inline-block;
            color: #0066CC;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s;
            border-radius: 8px;
            margin-top: 30px;
            border: 2px solid #0066CC;
        }

        .nav-link:hover {
            background: #0066CC;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }

        #loading {
            display: none;
            color: #0066CC;
            font-weight: 600;
            margin-top: 20px;
            font-size: 16px;
            padding: 15px;
            background: rgba(0, 102, 204, 0.05);
            border-radius: 8px;
            border-left: 4px solid #0066CC;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 26px;
            }

            h2 {
                font-size: 20px;
            }

            .score {
                font-size: 48px;
            }

            button {
                padding: 14px 40px;
                font-size: 16px;
            }
        }

        </head><body><div class="container"><h1>補助金マッチング判定</h1><p>事業内容を入力して、補助金との適合度をスコアで判定します。</p><form id="evalForm"><h2>企業情報</h2><div class="form-group"><label>組織形態</label><select name="company.org_type"><option value="">選択してください</option><option value="corporation">株式会社</option><option value="sole_prop">個人事業主</option><option value="npo">NPO法人</option><option value="other">その他</option></select></div><div class="form-group"><label>業種</label><select name="company.industry"><option value="">選択してください</option><option value="manufacturing">製造業</option><option value="construction">建設業</option><option value="logistics">物流業</option><option value="wholesale">卸売業</option><option value="retail">小売業</option><option value="service">サービス業</option><option value="it">IT業</option><option value="other">その他</option></select></div><div class="form-group"><label>従業員数</label><input type="number" name="company.employees_count" min="0" placeholder="例: 25"></div><h2>事業計画</h2><div class="form-group"><label>現在の事業内容（簡潔に）</label><textarea name="project.current_business" rows="2" placeholder="例: 自動車部品の製造"></textarea></div><div class="form-group"><label>新しい取り組み（簡潔に）</label><textarea name="project.new_initiative" rows="2" placeholder="例: IoTセンサーを活用した品質管理システムの導入"></textarea></div><div class="form-group"><label>製品・サービスの新規性</label><select name="project.novelty_product"><option value="unknown">不明</option><option value="yes">新規性あり</option><option value="no">新規性なし</option></select></div><div class="form-group"><label>市場の新規性</label><select name="project.novelty_market"><option value="unknown">不明</option><option value="yes">新規市場</option><option value="no">既存市場</option></select></div><h2>投資内容</h2><div class="form-group"><label>総投資額（円）</label><input type="number" name="investment.total_amount_yen" min="0" placeholder="例: 5000000"></div><div class="form-group"><label>設備・システム投資を含む</label><select name="investment.includes_machine_or_system"><option value="unknown">不明</option><option value="yes">含む</option><option value="no">含まない</option></select></div><h2>事業再構築（該当する場合）</h2><div class="form-group"><label>再構築タイプ</label><select name="rebuild.restructuring_type"><option value="unknown">該当なし</option><option value="new_field">新分野展開</option><option value="business_shift">事業転換</option><option value="industry_shift">業種転換</option><option value="other">その他</option></select></div><div class="form-group"><label>新事業の売上比率（計画）</label><select name="rebuild.new_business_sales_ratio_band"><option value="unknown">不明</option><option value="lt_10">10%未満</option><option value="10_30">10〜30%</option><option value="gt_30">30%超</option></select></div><button type="submit">判定する</button><div id="loading">判定中...</div></form><div id="results"></div><a href="select.php" class="nav-link">判定履歴を見る</a></div><script>document.getElementById('evalForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                //ローディング表示
                document.getElementById('loading').style.display='block';
                document.querySelector('button[type="submit"]').disabled=true;

                //フォームデータを収集
                var formData=new FormData(e.target);

                var input= {
                    company: {}

                    ,
                    project: {}

                    ,
                    investment: {}

                    ,
                    rebuild: {}
                }

                ;

                for (var pair of formData.entries()) {
                    var key=pair[0];
                    var value=pair[1];
                    var keys=key.split('.');

                    if (keys.length==2) {
                        var section=keys[0];
                        var field=keys[1];

                        //数値変換
                        if (field.includes('count') || field.includes('amount') || field.includes('yen')) {
                            input[section][field]=value ? parseInt(value) : null;
                        }

                        else {
                            input[section][field]=value || 'unknown';
                        }
                    }
                }

                //デフォルト値設定
                input.project.novelty_market=input.project.novelty_market || 'unknown';
                input.rebuild.restructuring_type=input.rebuild.restructuring_type || 'unknown';
                input.rebuild.new_business_sales_ratio_band=input.rebuild.new_business_sales_ratio_band || 'unknown';

                //API呼び出し
                try {
                    var response=await fetch('index.php', {

                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }

                        ,
                        body: JSON.stringify(input)
                    });

                var result=await response.json();

                //ローディング非表示
                document.getElementById('loading').style.display='none';
                document.querySelector('button[type="submit"]').disabled=false;

                showResults(result);

            }

            catch (error) {
                document.getElementById('loading').style.display='none';
                document.querySelector('button[type="submit"]').disabled=false;
                alert('エラーが発生しました: ' + error.message);
            }
        });

        function showResults(result) {
            var resultsDiv=document.getElementById('results');
            var html='<h2>判定結果</h2>';

            for (var i=0; i < result.candidates.length; i++) {
                var candidate=result.candidates[i];
                //評価に応じてクラスを決定
                var evalClass='eval-poor';

                if (candidate.score >=70) {
                    evalClass='eval-excellent';
                }

                else if (candidate.score >=50) {
                    evalClass='eval-good';
                }

                else if (candidate.score >=30) {
                    evalClass='eval-fair';
                }

                html+='<div class="result-card">';
                html+='<h3>'+candidate.subsidy_name+'</h3>';
                html+='<div class="score">'+candidate.score+' 点</div>';
                html+='<div class="evaluation '+evalClass+'">評価: '+candidate.evaluation+'</div>';
                html+='<div class="reasons"><strong>スコア獲得理由:</strong><ul>';

                for (var j=0; j < candidate.reasons.length; j++) {
                    html+='<li>'+candidate.reasons[j]+'</li>';
                }

                html+='</ul></div></div>';
            }

            resultsDiv.innerHTML=html;

            //結果にスクロール
            resultsDiv.scrollIntoView({
                behavior: 'smooth'
            });
        }

        </script></body></html>