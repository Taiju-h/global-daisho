CREATE TABLE IF NOT EXISTS crm_gpt_changes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    request_hash CHAR(64) NOT NULL,
    entity VARCHAR(20) NOT NULL,
    record_id BIGINT UNSIGNED NULL,
    before_json MEDIUMTEXT,
    after_json MEDIUMTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY dedup (user_id, request_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
