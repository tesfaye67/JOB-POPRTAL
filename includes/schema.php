<?php

function app_valid_identifier(string $name): bool
{
    return (bool)preg_match('/^[a-zA-Z0-9_]+$/', $name);
}

function app_table_exists(PDO $pdo, string $table): bool
{
    if (!app_valid_identifier($table)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :table_name");
        $stmt->execute(['table_name' => $table]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function app_column_exists(PDO $pdo, string $table, string $column): bool
{
    if (!app_valid_identifier($table) || !app_valid_identifier($column)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
              AND COLUMN_NAME = :column_name
        ");
        $stmt->execute([
            'table_name' => $table,
            'column_name' => $column,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function app_add_column_if_missing(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!app_valid_identifier($table) || !app_valid_identifier($column)) {
        return;
    }

    if (!app_column_exists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

function ensure_app_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            avatar VARCHAR(255) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
            is_active TINYINT(1) DEFAULT 1,
            failed_login_attempts INT NOT NULL DEFAULT 0,
            locked_until DATETIME DEFAULT NULL,
            last_login_at DATETIME DEFAULT NULL,
            last_login_ip VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS company_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fields_of_study (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(220) NOT NULL UNIQUE,
            company_category_id INT DEFAULT NULL,
            logo VARCHAR(255) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_companies_category (company_category_id),
            CONSTRAINT fk_companies_category FOREIGN KEY (company_category_id) REFERENCES company_categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            company_id INT DEFAULT NULL,
            field_id INT DEFAULT NULL,
            company_category_id INT DEFAULT NULL,
            location VARCHAR(150) DEFAULT NULL,
            job_type ENUM('Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance') DEFAULT 'Full Time',
            short_description TEXT NOT NULL,
            full_description LONGTEXT DEFAULT NULL,
            requirements LONGTEXT DEFAULT NULL,
            application_process LONGTEXT DEFAULT NULL,
            application_link VARCHAR(500) NOT NULL,
            vacancy_image VARCHAR(255) DEFAULT NULL,
            is_featured TINYINT(1) DEFAULT 0,
            status ENUM('draft', 'published', 'expired') DEFAULT 'draft',
            source_type ENUM('company', 'agent', 'direct') DEFAULT 'direct',
            posted_date DATE DEFAULT NULL,
            deadline DATE DEFAULT NULL,
            expiry_date DATE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_jobs_company (company_id),
            INDEX idx_jobs_field (field_id),
            INDEX idx_jobs_company_category (company_category_id),
            INDEX idx_jobs_status_expiry (status, expiry_date),
            CONSTRAINT fk_jobs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
            CONSTRAINT fk_jobs_field FOREIGN KEY (field_id) REFERENCES fields_of_study(id) ON DELETE SET NULL,
            CONSTRAINT fk_jobs_company_category FOREIGN KEY (company_category_id) REFERENCES company_categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            site_name VARCHAR(150) NOT NULL DEFAULT 'Tikuse Jobs',
            site_logo VARCHAR(255) DEFAULT NULL,
            hero_badge VARCHAR(150) DEFAULT NULL,
            hero_title VARCHAR(255) DEFAULT NULL,
            hero_highlight VARCHAR(120) DEFAULT NULL,
            hero_subtitle TEXT DEFAULT NULL,
            primary_cta_label VARCHAR(80) DEFAULT NULL,
            secondary_cta_label VARCHAR(80) DEFAULT NULL,
            hero_telegram_label VARCHAR(100) DEFAULT NULL,
            stats_jobs_label VARCHAR(80) DEFAULT NULL,
            stats_companies_label VARCHAR(80) DEFAULT NULL,
            stats_categories_label VARCHAR(80) DEFAULT NULL,
            stats_clicks_label VARCHAR(80) DEFAULT NULL,
            quick_categories_label VARCHAR(120) DEFAULT NULL,
            view_all_jobs_label VARCHAR(80) DEFAULT NULL,
            view_all_categories_label VARCHAR(80) DEFAULT NULL,
            browse_companies_label VARCHAR(80) DEFAULT NULL,
            preview_clicks_label VARCHAR(100) DEFAULT NULL,
            preview_expiry_value VARCHAR(40) DEFAULT NULL,
            preview_expiry_label VARCHAR(100) DEFAULT NULL,
            search_placeholder VARCHAR(160) DEFAULT NULL,
            hero_preview_label VARCHAR(100) DEFAULT NULL,
            hero_preview_title VARCHAR(120) DEFAULT NULL,
            home_about_title VARCHAR(180) DEFAULT NULL,
            home_about_text TEXT DEFAULT NULL,
            home_about_cta_label VARCHAR(80) DEFAULT NULL,
            home_about_point_1 VARCHAR(180) DEFAULT NULL,
            home_about_point_2 VARCHAR(180) DEFAULT NULL,
            home_about_point_3 VARCHAR(180) DEFAULT NULL,
            latest_jobs_title VARCHAR(150) DEFAULT NULL,
            latest_jobs_subtitle VARCHAR(255) DEFAULT NULL,
            categories_title VARCHAR(150) DEFAULT NULL,
            categories_subtitle VARCHAR(255) DEFAULT NULL,
            companies_title VARCHAR(150) DEFAULT NULL,
            companies_subtitle VARCHAR(255) DEFAULT NULL,
            telegram_card_title VARCHAR(150) DEFAULT NULL,
            telegram_card_text TEXT DEFAULT NULL,
            telegram_card_button_label VARCHAR(80) DEFAULT NULL,
            why_title VARCHAR(150) DEFAULT NULL,
            why_point_1 VARCHAR(180) DEFAULT NULL,
            why_point_2 VARCHAR(180) DEFAULT NULL,
            why_point_3 VARCHAR(180) DEFAULT NULL,
            telegram_link VARCHAR(255) DEFAULT NULL,
            facebook_link VARCHAR(255) DEFAULT NULL,
            tiktok_link VARCHAR(255) DEFAULT NULL,
            instagram_link VARCHAR(255) DEFAULT NULL,
            contact_email VARCHAR(150) DEFAULT NULL,
            contact_phone VARCHAR(50) DEFAULT NULL,
            footer_about TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS job_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            job_id INT NOT NULL,
            event_type ENUM('view', 'click') NOT NULL DEFAULT 'view',
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            referrer VARCHAR(500) DEFAULT NULL,
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_job_views_job_event (job_id, event_type),
            CONSTRAINT fk_job_views_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ad_slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slot_key VARCHAR(100) NOT NULL UNIQUE,
            ad_code LONGTEXT DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    app_add_column_if_missing($pdo, 'admins', 'avatar', "VARCHAR(255) DEFAULT NULL AFTER email");
    app_add_column_if_missing($pdo, 'admins', 'is_active', "TINYINT(1) DEFAULT 1 AFTER role");
    app_add_column_if_missing($pdo, 'admins', 'failed_login_attempts', "INT NOT NULL DEFAULT 0 AFTER is_active");
    app_add_column_if_missing($pdo, 'admins', 'locked_until', "DATETIME DEFAULT NULL AFTER failed_login_attempts");
    app_add_column_if_missing($pdo, 'admins', 'last_login_at', "DATETIME DEFAULT NULL AFTER locked_until");
    app_add_column_if_missing($pdo, 'admins', 'last_login_ip', "VARCHAR(45) DEFAULT NULL AFTER last_login_at");
    app_add_column_if_missing($pdo, 'companies', 'updated_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    app_add_column_if_missing($pdo, 'settings', 'hero_badge', "VARCHAR(150) DEFAULT NULL AFTER site_logo");
    app_add_column_if_missing($pdo, 'settings', 'hero_title', "VARCHAR(255) DEFAULT NULL AFTER hero_badge");
    app_add_column_if_missing($pdo, 'settings', 'hero_highlight', "VARCHAR(120) DEFAULT NULL AFTER hero_title");
    app_add_column_if_missing($pdo, 'settings', 'hero_subtitle', "TEXT DEFAULT NULL AFTER hero_highlight");
    app_add_column_if_missing($pdo, 'settings', 'primary_cta_label', "VARCHAR(80) DEFAULT NULL AFTER hero_subtitle");
    app_add_column_if_missing($pdo, 'settings', 'secondary_cta_label', "VARCHAR(80) DEFAULT NULL AFTER primary_cta_label");
    app_add_column_if_missing($pdo, 'settings', 'hero_telegram_label', "VARCHAR(100) DEFAULT NULL AFTER secondary_cta_label");
    app_add_column_if_missing($pdo, 'settings', 'stats_jobs_label', "VARCHAR(80) DEFAULT NULL AFTER hero_telegram_label");
    app_add_column_if_missing($pdo, 'settings', 'stats_companies_label', "VARCHAR(80) DEFAULT NULL AFTER stats_jobs_label");
    app_add_column_if_missing($pdo, 'settings', 'stats_categories_label', "VARCHAR(80) DEFAULT NULL AFTER stats_companies_label");
    app_add_column_if_missing($pdo, 'settings', 'stats_clicks_label', "VARCHAR(80) DEFAULT NULL AFTER stats_categories_label");
    app_add_column_if_missing($pdo, 'settings', 'quick_categories_label', "VARCHAR(120) DEFAULT NULL AFTER stats_clicks_label");
    app_add_column_if_missing($pdo, 'settings', 'view_all_jobs_label', "VARCHAR(80) DEFAULT NULL AFTER quick_categories_label");
    app_add_column_if_missing($pdo, 'settings', 'view_all_categories_label', "VARCHAR(80) DEFAULT NULL AFTER view_all_jobs_label");
    app_add_column_if_missing($pdo, 'settings', 'browse_companies_label', "VARCHAR(80) DEFAULT NULL AFTER view_all_categories_label");
    app_add_column_if_missing($pdo, 'settings', 'preview_clicks_label', "VARCHAR(100) DEFAULT NULL AFTER browse_companies_label");
    app_add_column_if_missing($pdo, 'settings', 'preview_expiry_value', "VARCHAR(40) DEFAULT NULL AFTER preview_clicks_label");
    app_add_column_if_missing($pdo, 'settings', 'preview_expiry_label', "VARCHAR(100) DEFAULT NULL AFTER preview_expiry_value");
    app_add_column_if_missing($pdo, 'settings', 'search_placeholder', "VARCHAR(160) DEFAULT NULL AFTER secondary_cta_label");
    app_add_column_if_missing($pdo, 'settings', 'hero_preview_label', "VARCHAR(100) DEFAULT NULL AFTER search_placeholder");
    app_add_column_if_missing($pdo, 'settings', 'hero_preview_title', "VARCHAR(120) DEFAULT NULL AFTER hero_preview_label");
    app_add_column_if_missing($pdo, 'settings', 'home_about_title', "VARCHAR(180) DEFAULT NULL AFTER hero_preview_title");
    app_add_column_if_missing($pdo, 'settings', 'home_about_text', "TEXT DEFAULT NULL AFTER home_about_title");
    app_add_column_if_missing($pdo, 'settings', 'home_about_cta_label', "VARCHAR(80) DEFAULT NULL AFTER home_about_text");
    app_add_column_if_missing($pdo, 'settings', 'home_about_point_1', "VARCHAR(180) DEFAULT NULL AFTER home_about_cta_label");
    app_add_column_if_missing($pdo, 'settings', 'home_about_point_2', "VARCHAR(180) DEFAULT NULL AFTER home_about_point_1");
    app_add_column_if_missing($pdo, 'settings', 'home_about_point_3', "VARCHAR(180) DEFAULT NULL AFTER home_about_point_2");
    app_add_column_if_missing($pdo, 'settings', 'latest_jobs_title', "VARCHAR(150) DEFAULT NULL AFTER home_about_point_3");
    app_add_column_if_missing($pdo, 'settings', 'latest_jobs_subtitle', "VARCHAR(255) DEFAULT NULL AFTER latest_jobs_title");
    app_add_column_if_missing($pdo, 'settings', 'categories_title', "VARCHAR(150) DEFAULT NULL AFTER latest_jobs_subtitle");
    app_add_column_if_missing($pdo, 'settings', 'categories_subtitle', "VARCHAR(255) DEFAULT NULL AFTER categories_title");
    app_add_column_if_missing($pdo, 'settings', 'companies_title', "VARCHAR(150) DEFAULT NULL AFTER categories_subtitle");
    app_add_column_if_missing($pdo, 'settings', 'companies_subtitle', "VARCHAR(255) DEFAULT NULL AFTER companies_title");
    app_add_column_if_missing($pdo, 'settings', 'telegram_card_title', "VARCHAR(150) DEFAULT NULL AFTER companies_subtitle");
    app_add_column_if_missing($pdo, 'settings', 'telegram_card_text', "TEXT DEFAULT NULL AFTER telegram_card_title");
    app_add_column_if_missing($pdo, 'settings', 'telegram_card_button_label', "VARCHAR(80) DEFAULT NULL AFTER telegram_card_text");
    app_add_column_if_missing($pdo, 'settings', 'why_title', "VARCHAR(150) DEFAULT NULL AFTER telegram_card_button_label");
    app_add_column_if_missing($pdo, 'settings', 'why_point_1', "VARCHAR(180) DEFAULT NULL AFTER why_title");
    app_add_column_if_missing($pdo, 'settings', 'why_point_2', "VARCHAR(180) DEFAULT NULL AFTER why_point_1");
    app_add_column_if_missing($pdo, 'settings', 'why_point_3', "VARCHAR(180) DEFAULT NULL AFTER why_point_2");
    app_add_column_if_missing($pdo, 'settings', 'updated_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    app_add_column_if_missing($pdo, 'job_views', 'event_type', "ENUM('view', 'click') NOT NULL DEFAULT 'view' AFTER job_id");
    app_add_column_if_missing($pdo, 'job_views', 'user_agent', "VARCHAR(255) DEFAULT NULL AFTER ip_address");
    app_add_column_if_missing($pdo, 'job_views', 'referrer', "VARCHAR(500) DEFAULT NULL AFTER user_agent");

    $existingSettings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ((int)$existingSettings === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO settings (
                site_name, hero_badge, hero_title, hero_highlight, hero_subtitle,
                primary_cta_label, secondary_cta_label, hero_telegram_label, stats_jobs_label,
                stats_companies_label, stats_categories_label, stats_clicks_label, quick_categories_label,
                view_all_jobs_label, view_all_categories_label, browse_companies_label,
                preview_clicks_label, preview_expiry_value, preview_expiry_label,
                search_placeholder, hero_preview_label,
                hero_preview_title, home_about_title, home_about_text, home_about_cta_label,
                home_about_point_1, home_about_point_2, home_about_point_3, latest_jobs_title,
                latest_jobs_subtitle, categories_title, categories_subtitle, companies_title,
                companies_subtitle, telegram_card_title, telegram_card_text, telegram_card_button_label,
                why_title, why_point_1, why_point_2, why_point_3, telegram_link, footer_about
            ) VALUES (
                :site_name, :hero_badge, :hero_title, :hero_highlight, :hero_subtitle,
                :primary_cta_label, :secondary_cta_label, :hero_telegram_label, :stats_jobs_label,
                :stats_companies_label, :stats_categories_label, :stats_clicks_label, :quick_categories_label,
                :view_all_jobs_label, :view_all_categories_label, :browse_companies_label,
                :preview_clicks_label, :preview_expiry_value, :preview_expiry_label,
                :search_placeholder, :hero_preview_label,
                :hero_preview_title, :home_about_title, :home_about_text, :home_about_cta_label,
                :home_about_point_1, :home_about_point_2, :home_about_point_3, :latest_jobs_title,
                :latest_jobs_subtitle, :categories_title, :categories_subtitle, :companies_title,
                :companies_subtitle, :telegram_card_title, :telegram_card_text, :telegram_card_button_label,
                :why_title, :why_point_1, :why_point_2, :why_point_3, :telegram_link, :footer_about
            )
        ");
        $stmt->execute([
            'site_name' => 'Tikuse Jobs',
            'hero_badge' => 'Verified jobs from trusted employers',
            'hero_title' => 'Find the right job faster',
            'hero_highlight' => 'with Tikuse Jobs',
            'hero_subtitle' => 'Search fresh opportunities by title, category, company, location, and job type from one clean job portal.',
            'primary_cta_label' => 'Browse Jobs',
            'secondary_cta_label' => 'View Companies',
            'hero_telegram_label' => 'Join Telegram updates',
            'stats_jobs_label' => 'Active Jobs',
            'stats_companies_label' => 'Companies',
            'stats_categories_label' => 'Categories',
            'stats_clicks_label' => 'Apply Clicks',
            'quick_categories_label' => 'Explore all categories',
            'view_all_jobs_label' => 'View All Jobs',
            'view_all_categories_label' => 'View All',
            'browse_companies_label' => 'Browse Companies',
            'preview_clicks_label' => 'Tracked apply clicks',
            'preview_expiry_value' => '3 mo',
            'preview_expiry_label' => 'Auto expiry window',
            'search_placeholder' => 'Job title, company, keywords',
            'hero_preview_label' => 'Live Job Board',
            'hero_preview_title' => 'Latest openings',
            'home_about_title' => 'Built for job seekers and growing employers',
            'home_about_text' => 'Tikuse Jobs helps visitors move from searching to applying with verified listings, clean filters, company pages, and tracked application clicks for better publishing decisions.',
            'home_about_cta_label' => 'Learn About Us',
            'home_about_point_1' => 'Verified job summaries',
            'home_about_point_2' => 'Fast search and filters',
            'home_about_point_3' => 'Admin analytics for every apply click',
            'latest_jobs_title' => 'Latest Verified Jobs',
            'latest_jobs_subtitle' => 'Fresh openings published from the admin dashboard',
            'categories_title' => 'Popular Categories',
            'categories_subtitle' => 'Browse jobs by field and study direction',
            'companies_title' => 'Trusted Companies',
            'companies_subtitle' => 'Employers currently listed on Tikuse Jobs',
            'telegram_card_title' => 'Telegram Job Alerts',
            'telegram_card_text' => 'Share new jobs instantly and bring returning visitors back to the site.',
            'telegram_card_button_label' => 'Join Now',
            'why_title' => 'Why visitors stay',
            'why_point_1' => 'Search by keyword, category, company, and location',
            'why_point_2' => 'Clean job detail pages with official apply links',
            'why_point_3' => 'Expired jobs hidden automatically from public pages',
            'telegram_link' => '#',
            'footer_about' => 'Your global-style job search partner. Find. Apply. Succeed.',
        ]);
    }
}
