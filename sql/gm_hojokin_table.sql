-- 判定ログテーブル
CREATE TABLE IF NOT EXISTS `gm_hojokin_table` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) NOT NULL,
  `input_json` text NOT NULL,
  `result_json` text NOT NULL,
  `memo` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
