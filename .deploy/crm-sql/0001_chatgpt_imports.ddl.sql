-- Existing installations may already have this table from the import screen.
CREATE TABLE IF NOT EXISTS crm_chatgpt_imports (
    user_id BIGINT UNSIGNED NOT NULL,
    record_hash CHAR(64) NOT NULL,
    activity_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, record_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
