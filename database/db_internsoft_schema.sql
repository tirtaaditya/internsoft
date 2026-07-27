CREATE DATABASE IF NOT EXISTS db_internsoft
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_internsoft;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  wa_number VARCHAR(30) NOT NULL,
  is_wa_verified TINYINT(1) NOT NULL DEFAULT 0,
  password_hash VARCHAR(255) NOT NULL,
  otp_code_hash VARCHAR(255) NULL,
  otp_expires_at DATETIME NULL,
  otp_last_sent_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_email (email),
  UNIQUE KEY uk_users_wa_number (wa_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS domains (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  domain_url VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_status ENUM('UP','DOWN','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  last_checked_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_domains_user_domain (user_id, domain_url),
  KEY idx_domains_user_id (user_id),
  KEY idx_domains_is_active (is_active),
  CONSTRAINT fk_domains_user_id FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS domain_contacts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  domain_id BIGINT UNSIGNED NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_contacts_domain_phone (domain_id, phone_number),
  KEY idx_contacts_domain_id (domain_id),
  CONSTRAINT fk_contacts_domain_id FOREIGN KEY (domain_id) REFERENCES domains(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS domain_checks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  domain_id BIGINT UNSIGNED NOT NULL,
  checked_at DATETIME NOT NULL,
  status ENUM('UP','DOWN') NOT NULL,
  http_code INT NULL,
  response_time_ms INT NULL,
  error_message TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_checks_domain_time (domain_id, checked_at),
  CONSTRAINT fk_checks_domain_id FOREIGN KEY (domain_id) REFERENCES domains(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outage_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  domain_id BIGINT UNSIGNED NOT NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  duration_seconds INT NULL,
  is_acknowledged TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_outage_domain_started (domain_id, started_at),
  KEY idx_outage_open (domain_id, ended_at),
  CONSTRAINT fk_outage_domain_id FOREIGN KEY (domain_id) REFERENCES domains(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
