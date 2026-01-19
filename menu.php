<div style="margin-bottom: 20px;">
    <a href="index.php" style="margin-right: 15px; color: #824cafff; text-decoration: none;">判定画面</a>
    <a href="select.php" style="margin-right: 15px; color: #824cafff; text-decoration: none;">判定履歴</a>
    <?php if ($_SESSION["kanri_flg"] == "1"): ?>
    <a href="user.php" style="margin-right: 15px; color: #824cafff; text-decoration: none;">ユーザー登録</a>
    <a href="user_select.php" style="margin-right: 15px; color: #824cafff; text-decoration: none;">ユーザー一覧</a>
    <?php endif; ?>
    <a href="logout.php" style="color: #824cafff; text-decoration: none;">ログアウト</a>
</div>
