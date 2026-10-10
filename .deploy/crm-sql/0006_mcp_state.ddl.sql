CREATE TABLE IF NOT EXISTS crm_mcp_state (
 state_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 kind VARCHAR(20) NOT NULL,
 user_id BIGINT UNSIGNED NULL,
 auth_version INT NOT NULL DEFAULT 0,
 expires_at BIGINT NOT NULL,
 payload MEDIUMTEXT NOT NULL,
 KEY cleanup(expires_at),
 KEY user_kind(user_id,kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
