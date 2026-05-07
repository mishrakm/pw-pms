-- Metrics/Key Statistics Table (separate from performance_returns)
CREATE TABLE IF NOT EXISTS key_metrics (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  metric_key VARCHAR(100) NOT NULL UNIQUE,
  metric_label VARCHAR(255) NOT NULL,
  metric_value VARCHAR(100) NOT NULL,
  benchmark_value VARCHAR(100) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_metric_key (metric_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM key_metrics;

INSERT INTO key_metrics (
  metric_key,
  metric_label,
  metric_value,
  benchmark_value,
  is_active
) VALUES
(
  'max_drawdown',
  'Max drawdown',
  '−5.07%',
  '−5.71%',
  1
);
