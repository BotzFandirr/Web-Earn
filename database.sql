CREATE DATABASE IF NOT EXISTS cuantask CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cuantask;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    points INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Optional: akun admin awal (ganti hash password sesuai kebutuhan)
-- INSERT INTO users (name, email, password_hash, role, is_verified, points, created_at)
-- VALUES ('Admin CuanTask', 'admin@cuantask.local', '$2y$10$replace_with_password_hash', 'admin', 1, 0, NOW());
