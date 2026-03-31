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
INSERT INTO accounts (owner_name, currency)
VALUES ('Alice Johnson', 'USD'),
    ('Bob Smith', 'EUR'),
    ('Charlie Davis', 'GBP'),
    ('Diana Prince', 'USD'),
    ('Evan Lee', 'EUR');
-- Assuming account IDs: 1, 2, 3, 4, 5
-- Transactions for Alice Johnson (account_id = 1)
INSERT INTO transactions (
        account_id,
        type,
        amount,
        description,
        balance_after
    )
VALUES (
        1,
        'deposit',
        1000.00,
        'Initial deposit',
        1000.00
    ),
    (
        1,
        'withdrawal',
        200.00,
        'ATM withdrawal',
        800.00
    ),
    (1, 'deposit', 500.00, 'Salary deposit', 1300.00);
-- Transactions for Bob Smith (account_id = 2)
INSERT INTO transactions (
        account_id,
        type,
        amount,
        description,
        balance_after
    )
VALUES (
        2,
        'deposit',
        1500.00,
        'Initial deposit',
        1500.00
    ),
    (
        2,
        'withdrawal',
        300.00,
        'Grocery shopping',
        1200.00
    );
-- Transactions for Charlie Davis (account_id = 3)
INSERT INTO transactions (
        account_id,
        type,
        amount,
        description,
        balance_after
    )
VALUES (
        3,
        'deposit',
        2000.00,
        'Initial deposit',
        2000.00
    ),
    (
        3,
        'withdrawal',
        250.00,
        'Online shopping',
        1750.00
    );
-- Transactions for Diana Prince (account_id = 4)
INSERT INTO transactions (
        account_id,
        type,
        amount,
        description,
        balance_after
    )
VALUES (4, 'deposit', 500.00, 'Initial deposit', 500.00),
    (4, 'withdrawal', 50.00, 'Coffee shop', 450.00),
    (4, 'deposit', 200.00, 'Gift', 650.00);
-- Transactions for Evan Lee (account_id = 5)
INSERT INTO transactions (
        account_id,
        type,
        amount,
        description,
        balance_after
    )
VALUES (
        5,
        'deposit',
        3000.00,
        'Initial deposit',
        3000.00
    ),
    (5, 'withdrawal', 500.00, 'Rent payment', 2500.00);