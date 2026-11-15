CREATE DATABASE IF NOT EXISTS fintrack
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fintrack;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  icon VARCHAR(20) NOT NULL DEFAULT '•',
  color VARCHAR(20) NOT NULL DEFAULT '#64748b',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_category_type (name, type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('income','expense') NOT NULL,
  category_id INT UNSIGNED NULL,
  title VARCHAR(160) NOT NULL,
  amount DECIMAL(14,2) UNSIGNED NOT NULL,
  transaction_date DATE NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_transaction_date (transaction_date),
  INDEX idx_transaction_type (type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  month CHAR(7) NOT NULL COMMENT 'فرمت YYYY-MM',
  amount DECIMAL(14,2) UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_budgets_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE CASCADE,
  UNIQUE KEY unique_budget (category_id, month)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name,type,icon,color) VALUES
('حقوق و دستمزد','income','💼','#16a34a'),
('هدیه و پاداش','income','🎁','#0d9488'),
('سایر درآمدها','income','✨','#0891b2'),
('خوراک و مواد غذایی','expense','🛒','#f97316'),
('حمل‌ونقل','expense','🚕','#eab308'),
('قبوض و خدمات','expense','💡','#8b5cf6'),
('خرید و پوشاک','expense','🛍️','#ec4899'),
('تفریح و سرگرمی','expense','🎮','#06b6d4'),
('سلامت','expense','❤️','#ef4444'),
('سایر هزینه‌ها','expense','📦','#64748b');
