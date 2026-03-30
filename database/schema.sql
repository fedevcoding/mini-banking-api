CREATE DATABASE IF NOT EXISTS banking;
USE banking;
--  accounts
CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_name VARCHAR(100) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
--  transactions
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    account_id INT UNSIGNED NOT NULL,
    type ENUM('deposit', 'withdrawal') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    balance_after DECIMAL(15, 2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactions_account FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE
);