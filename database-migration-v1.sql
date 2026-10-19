-- ============================================================================
-- KYNOS administration migration v1.0.0
-- Controlled phpMyAdmin step. Run ONCE on the production database BEFORE the
-- new admin dashboard code is enabled. Safe to re-run: statements are guarded.
--
-- What this does:
--   1. Creates admin_users, login_attempts, settings and audit_log.
--   2. Turns existing solar_assessments rows into customer/enquiry records by
--      renaming the table to customer_enquiries.
--   3. Leaves an updatable compatibility VIEW named solar_assessments so the
--      currently deployed site keeps working until the new code is deployed.
--      (Drop the view after the new dashboard is confirmed live.)
--   4. Seeds allowlisted settings with the site's current published values.
--   5. Makes audit_log append-only at the database level.
-- ============================================================================

-- 1. Accounts ----------------------------------------------------------------

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','staff') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login throttling: failures recorded per account and per IP hash.
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    account_key VARCHAR(190) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_login_attempts_account (account_key, attempted_at),
    KEY idx_login_attempts_ip (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Settings ----------------------------------------------------------------

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    updated_by VARCHAR(190) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Audit trail (append-only) -----------------------------------------------

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id INT UNSIGNED DEFAULT NULL,
    actor_email VARCHAR(190) NOT NULL DEFAULT '',
    actor_role VARCHAR(20) NOT NULL DEFAULT '',
    action VARCHAR(60) NOT NULL,
    entity VARCHAR(40) NOT NULL,
    entity_id VARCHAR(64) NOT NULL DEFAULT '',
    details TEXT DEFAULT NULL,
    ip_hash CHAR(64) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_log_created (created_at),
    KEY idx_audit_log_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS audit_log_block_update;
DROP TRIGGER IF EXISTS audit_log_block_delete;
DELIMITER $$
CREATE TRIGGER audit_log_block_update BEFORE UPDATE ON audit_log FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only';
END$$
CREATE TRIGGER audit_log_block_delete BEFORE DELETE ON audit_log FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_log is append-only';
END$$
DELIMITER ;

-- 4. Assessments become customer/enquiry records -----------------------------
-- Guarded so a re-run does not fail when the rename already happened.

SET @schema := DATABASE();
SET @rename_sql := (
    SELECT IF(
        (SELECT COUNT(*) FROM information_schema.tables
          WHERE table_schema = @schema AND table_name = 'customer_enquiries') = 0
        AND
        (SELECT COUNT(*) FROM information_schema.tables
          WHERE table_schema = @schema AND table_name = 'solar_assessments') = 1,
        'RENAME TABLE solar_assessments TO customer_enquiries',
        'DO 0'
    )
);
PREPARE rename_stmt FROM @rename_sql;
EXECUTE rename_stmt;
DEALLOCATE PREPARE rename_stmt;

-- Compatibility view for the currently deployed code; drop after new deploy:
--   DROP VIEW IF EXISTS solar_assessments;
CREATE OR REPLACE VIEW solar_assessments AS SELECT * FROM customer_enquiries;

-- 5. Seed allowlisted settings with current published values -----------------

INSERT IGNORE INTO settings (setting_key, setting_value, updated_by) VALUES
('company_name', 'KYNOS Solar Systems', 'migration'),
('phone', '053 040 9118', 'migration'),
('whatsapp', '233530409118', 'migration'),
('email', 'info@kynossolutions.com', 'migration'),
('address', 'Accra & Tema, Ghana', 'migration'),
('social_facebook', '', 'migration'),
('social_instagram', '', 'migration'),
('social_youtube', '', 'migration'),
('social_x', '', 'migration'),
('social_linkedin', '', 'migration'),
('social_tiktok', '', 'migration'),
('page_home_hero_intro', 'Say goodbye to sky-high electricity bills and hello to clean, reliable energy with KYNOS SOLAR SYSTEMS. We provide premium solar solutions for homes, offices and businesses.', 'migration'),
('page_home_solutions_intro', 'Everything you need to go solar: high quality products, professional installation and ongoing support.', 'migration'),
('page_home_why_intro', 'We are committed to providing reliable, affordable and long-lasting solar solutions for homes, offices and businesses.', 'migration'),
('page_home_projects_intro', 'Take a look at some of our recent solar installations for homes, offices and businesses.', 'migration'),
('page_home_cta_heading', 'Ready to Go Solar?', 'migration'),
('page_home_cta_text', 'Contact us today for a free assessment and get the right solar solution for your home, office or business.', 'migration'),
('page_about_intro', 'KYNOS Solar Systems helps homes, offices and businesses move toward reliable, affordable and sustainable energy.', 'migration'),
('page_contact_intro', 'Tell us what you want to power, where the property is and when you would like to get started.', 'migration'),
('footer_blurb', 'Reliable solar solutions for homes, offices and businesses in Ghana.', 'migration'),
('packages_json', '{"1":{"name":"Bronze","price":"GHC 39,000","items":["5 × 620W solar panels","1 × 24V 100Ah lithium battery","1 × 4.2kW hybrid inverter"]},"2":{"name":"Silver","price":"GHC 55,400","items":["6 × 620W solar panels","1 × 5.12kWh lithium battery","1 × 6kW hybrid inverter"]},"3":{"name":"Gold","price":"GHC 82,000","items":["10 × 620W solar panels","1 × 10.24kWh lithium battery","1 × 6kW hybrid inverter"]}}', 'migration');

-- After the new dashboard is verified live, remove the compatibility view:
--   DROP VIEW IF EXISTS solar_assessments;
