CREATE TABLE IF NOT EXISTS performance_returns (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  month_year DATE NOT NULL,
  strategy VARCHAR(500) NOT NULL,
  one_month VARCHAR(32) DEFAULT NULL,
  three_month VARCHAR(32) DEFAULT NULL,
  six_month VARCHAR(32) DEFAULT NULL,
  one_year VARCHAR(32) DEFAULT NULL,
  two_year VARCHAR(32) DEFAULT NULL,
  three_year VARCHAR(32) DEFAULT NULL,
  four_year VARCHAR(32) DEFAULT NULL,
  five_year VARCHAR(32) DEFAULT NULL,
  since_inception VARCHAR(32) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_active_month_order (is_active, month_year, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM performance_returns;

INSERT INTO performance_returns (
  month_year,
  strategy,
  one_month,
  three_month,
  six_month,
  one_year,
  two_year,
  three_year,
  four_year,
  five_year,
  since_inception,
  is_active,
  display_order
) VALUES
(
  '2026-01-01',
  'PlusWealth Fusion',
  '4.77',
  '3.5',
  '-',
  '-',
  '-',
  '-',
  '-',
  '-',
  '7.95%',
  1,
  1
),
(
  '2026-01-01',
  'Benchmark: NSE Multi Asset Index 2\n(50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)',
  '0.65',
  '-0.76',
  '-',
  '-',
  '-',
  '-',
  '-',
  '-',
  '1.51%',
  1,
  2
);
