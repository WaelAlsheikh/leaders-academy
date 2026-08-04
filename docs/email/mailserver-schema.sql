-- Mailserver virtual mailbox schema for Postfix + Dovecot
-- Apply on the dedicated `mailserver` MariaDB database on the VPS.

CREATE DATABASE IF NOT EXISTS mailserver CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mailserver;

CREATE TABLE IF NOT EXISTS virtual_domains (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS virtual_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  domain_id INT UNSIGNED NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  quota_mb INT UNSIGNED NOT NULL DEFAULT 1024,
  active TINYINT(1) NOT NULL DEFAULT 1,
  maildir VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_virtual_users_domain FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS virtual_aliases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  domain_id INT UNSIGNED NOT NULL,
  source VARCHAR(255) NOT NULL,
  destination VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_virtual_aliases_source_dest (source, destination),
  CONSTRAINT fk_virtual_aliases_domain FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO virtual_domains (name, created_at, updated_at)
VALUES ('leaders-academy.net', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);
