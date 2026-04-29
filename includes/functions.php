<?php

function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function csrf_token(): string
{
    app_session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    app_session_start();

    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function selected($actual, $expected): string
{
    return (string)$actual === (string)$expected ? ' selected' : '';
}

function checked($value): string
{
    return !empty($value) ? ' checked' : '';
}

function fetch_all_safe(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_row_safe(PDO $pdo, string $sql, array $params = []): ?array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function fetch_value_safe(PDO $pdo, string $sql, array $params = [], $default = 0)
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function set_flash(string $type, string $message): void
{
    app_session_start();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    app_session_start();

    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function app_slugify(string $text): string
{
    $text = trim(strtolower($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim((string)$text, '-');

    return $text !== '' ? $text : 'item';
}

function unique_slug(PDO $pdo, string $table, string $base, ?int $ignoreId = null): string
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return app_slugify($base);
    }

    $slug = app_slugify($base);
    $candidate = $slug;
    $counter = 2;

    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = :slug";
        $params = ['slug' => $candidate];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $exists = fetch_value_safe($pdo, $sql . " LIMIT 1", $params, false);

        if ($exists === false || $exists === null || $exists === 0) {
            return $candidate;
        }

        $candidate = $slug . '-' . $counter;
        $counter++;
    }
}

function normalize_date(?string $value): ?string
{
    $value = trim((string)$value);

    if ($value === '') {
        return null;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : null;
}

function default_expiry_date(?string $postedDate = null): string
{
    $base = $postedDate ? new DateTime($postedDate) : new DateTime();
    $base->modify('+3 months');
    return $base->format('Y-m-d');
}

function expire_old_jobs(PDO $pdo): void
{
    try {
        $pdo->exec("
            UPDATE jobs
            SET status = 'expired'
            WHERE status = 'published'
              AND expiry_date IS NOT NULL
              AND expiry_date < CURDATE()
        ");
    } catch (Throwable $e) {
        // The public pages should still render even if expiry automation cannot run.
    }
}

function upload_base_path(string $subdir = ''): string
{
    $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    return $subdir !== '' ? $base . DIRECTORY_SEPARATOR . $subdir : $base;
}

function upload_public_path(string $subdir, string $filename): string
{
    return 'uploads/' . trim($subdir, '/\\') . '/' . rawurlencode($filename);
}

function site_logo_url(array $settings, string $prefix = ''): ?string
{
    if (empty($settings['site_logo'])) {
        return null;
    }

    $prefix = trim($prefix, '/');
    return ($prefix !== '' ? $prefix . '/' : '') . upload_public_path('logos', $settings['site_logo']);
}

function brand_mark_html(array $settings, string $prefix = '', string $className = 'brand-logo-image'): string
{
    $siteName = $settings['site_name'] ?? 'Tikuse Jobs';
    $logoUrl = site_logo_url($settings, $prefix);

    if ($logoUrl) {
        return '<img src="' . e($logoUrl) . '" alt="' . e($siteName) . '" class="' . e($className) . '">';
    }

    return '<span class="brand-mark">T</span>';
}

function ensure_upload_dirs(): void
{
    foreach (['logos', 'jobs', 'admins'] as $folder) {
        $path = upload_base_path($folder);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}

function save_uploaded_image(string $field, string $subdir, ?string $currentFile = null, bool $required = false): array
{
    ensure_upload_dirs();

    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return ['filename' => $currentFile, 'error' => $required ? 'Please upload an image.' : null];
    }

    $file = $_FILES[$field];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['filename' => $currentFile, 'error' => $required ? 'Please upload an image.' : null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['filename' => $currentFile, 'error' => 'The upload failed. Please try again.'];
    }

    $maxBytes = 2 * 1024 * 1024;
    if ((int)$file['size'] > $maxBytes) {
        return ['filename' => $currentFile, 'error' => 'Images must be 2MB or smaller.'];
    }

    $originalName = (string)($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($extension, $allowedExtensions, true)) {
        return ['filename' => $currentFile, 'error' => 'Only JPG, PNG, WEBP, and GIF images are allowed.'];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    $mimeType = '';

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = (string)finfo_file($finfo, $tmpName);
            finfo_close($finfo);
        }
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true)) {
        return ['filename' => $currentFile, 'error' => 'The uploaded file is not a valid image.'];
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destinationDir = upload_base_path($subdir);
    $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        return ['filename' => $currentFile, 'error' => 'The image could not be saved.'];
    }

    if ($currentFile && $currentFile !== $filename) {
        $oldPath = $destinationDir . DIRECTORY_SEPARATOR . basename($currentFile);
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return ['filename' => $filename, 'error' => null];
}

function get_settings(PDO $pdo): array
{
    $defaults = [
        'id' => null,
        'site_name' => 'Tikuse Jobs',
        'site_logo' => null,
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
        'facebook_link' => '',
        'tiktok_link' => '',
        'instagram_link' => '',
        'contact_email' => '',
        'contact_phone' => '',
        'footer_about' => 'Your global-style job search partner. Find. Apply. Succeed.',
    ];

    $settings = fetch_row_safe($pdo, "SELECT * FROM settings ORDER BY id ASC LIMIT 1");

    return array_merge($defaults, $settings ?: []);
}

function save_settings(PDO $pdo, array $data): void
{
    $existingId = fetch_value_safe($pdo, "SELECT id FROM settings ORDER BY id ASC LIMIT 1", [], null);

    if ($existingId) {
        $stmt = $pdo->prepare("
            UPDATE settings
            SET site_name = :site_name,
                site_logo = :site_logo,
                hero_badge = :hero_badge,
                hero_title = :hero_title,
                hero_highlight = :hero_highlight,
                hero_subtitle = :hero_subtitle,
                primary_cta_label = :primary_cta_label,
                secondary_cta_label = :secondary_cta_label,
                hero_telegram_label = :hero_telegram_label,
                stats_jobs_label = :stats_jobs_label,
                stats_companies_label = :stats_companies_label,
                stats_categories_label = :stats_categories_label,
                stats_clicks_label = :stats_clicks_label,
                quick_categories_label = :quick_categories_label,
                view_all_jobs_label = :view_all_jobs_label,
                view_all_categories_label = :view_all_categories_label,
                browse_companies_label = :browse_companies_label,
                preview_clicks_label = :preview_clicks_label,
                preview_expiry_value = :preview_expiry_value,
                preview_expiry_label = :preview_expiry_label,
                search_placeholder = :search_placeholder,
                hero_preview_label = :hero_preview_label,
                hero_preview_title = :hero_preview_title,
                home_about_title = :home_about_title,
                home_about_text = :home_about_text,
                home_about_cta_label = :home_about_cta_label,
                home_about_point_1 = :home_about_point_1,
                home_about_point_2 = :home_about_point_2,
                home_about_point_3 = :home_about_point_3,
                latest_jobs_title = :latest_jobs_title,
                latest_jobs_subtitle = :latest_jobs_subtitle,
                categories_title = :categories_title,
                categories_subtitle = :categories_subtitle,
                companies_title = :companies_title,
                companies_subtitle = :companies_subtitle,
                telegram_card_title = :telegram_card_title,
                telegram_card_text = :telegram_card_text,
                telegram_card_button_label = :telegram_card_button_label,
                why_title = :why_title,
                why_point_1 = :why_point_1,
                why_point_2 = :why_point_2,
                why_point_3 = :why_point_3,
                telegram_link = :telegram_link,
                facebook_link = :facebook_link,
                tiktok_link = :tiktok_link,
                instagram_link = :instagram_link,
                contact_email = :contact_email,
                contact_phone = :contact_phone,
                footer_about = :footer_about
            WHERE id = :id
        ");
        $data['id'] = $existingId;
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO settings (
                site_name, site_logo, hero_badge, hero_title, hero_highlight, hero_subtitle,
                primary_cta_label, secondary_cta_label, hero_telegram_label, stats_jobs_label,
                stats_companies_label, stats_categories_label, stats_clicks_label, quick_categories_label,
                view_all_jobs_label, view_all_categories_label, browse_companies_label,
                preview_clicks_label, preview_expiry_value, preview_expiry_label,
                search_placeholder, hero_preview_label,
                hero_preview_title, home_about_title, home_about_text, home_about_cta_label,
                home_about_point_1, home_about_point_2, home_about_point_3, latest_jobs_title,
                latest_jobs_subtitle, categories_title, categories_subtitle, companies_title,
                companies_subtitle, telegram_card_title, telegram_card_text, telegram_card_button_label,
                why_title, why_point_1, why_point_2, why_point_3, telegram_link, facebook_link, tiktok_link,
                instagram_link, contact_email, contact_phone, footer_about
            ) VALUES (
                :site_name, :site_logo, :hero_badge, :hero_title, :hero_highlight, :hero_subtitle,
                :primary_cta_label, :secondary_cta_label, :hero_telegram_label, :stats_jobs_label,
                :stats_companies_label, :stats_categories_label, :stats_clicks_label, :quick_categories_label,
                :view_all_jobs_label, :view_all_categories_label, :browse_companies_label,
                :preview_clicks_label, :preview_expiry_value, :preview_expiry_label,
                :search_placeholder, :hero_preview_label,
                :hero_preview_title, :home_about_title, :home_about_text, :home_about_cta_label,
                :home_about_point_1, :home_about_point_2, :home_about_point_3, :latest_jobs_title,
                :latest_jobs_subtitle, :categories_title, :categories_subtitle, :companies_title,
                :companies_subtitle, :telegram_card_title, :telegram_card_text, :telegram_card_button_label,
                :why_title, :why_point_1, :why_point_2, :why_point_3, :telegram_link, :facebook_link, :tiktok_link,
                :instagram_link, :contact_email, :contact_phone, :footer_about
            )
        ");
    }

    $stmt->execute($data);
}

function track_job_event(PDO $pdo, int $jobId, string $eventType): void
{
    if (!in_array($eventType, ['view', 'click'], true)) {
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO job_views (job_id, event_type, ip_address, user_agent, referrer)
            VALUES (:job_id, :event_type, :ip_address, :user_agent, :referrer)
        ");
        $stmt->execute([
            'job_id' => $jobId,
            'event_type' => $eventType,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'referrer' => substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 500),
        ]);
    } catch (Throwable $e) {
        // Analytics must never block the job page or redirect.
    }
}

function public_job_where_sql(string $alias = 'j'): string
{
    return "$alias.status = 'published' AND ($alias.expiry_date IS NULL OR $alias.expiry_date >= CURDATE())";
}
