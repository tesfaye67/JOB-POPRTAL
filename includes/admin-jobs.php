<?php

function job_form_options(PDO $pdo): array
{
    return [
        'companies' => fetch_all_safe(
            $pdo,
            "SELECT c.id, c.name, c.logo, c.company_category_id, cc.name AS category_name
             FROM companies c
             LEFT JOIN company_categories cc ON cc.id = c.company_category_id
             ORDER BY c.name ASC"
        ),
        'fields' => fetch_all_safe($pdo, "SELECT id, name FROM fields_of_study ORDER BY name ASC"),
        'company_categories' => fetch_all_safe($pdo, "SELECT id, name FROM company_categories ORDER BY name ASC"),
    ];
}

function job_form_values(?array $job = null): array
{
    $defaults = [
        'title' => '',
        'company_id' => '',
        'new_company_name' => '',
        'company_website' => '',
        'company_description' => '',
        'field_id' => '',
        'new_field_name' => '',
        'company_category_id' => '',
        'new_company_category_name' => '',
        'location' => '',
        'job_type' => 'Full Time',
        'short_description' => '',
        'full_description' => '',
        'requirements' => '',
        'application_process' => '',
        'application_link' => '',
        'is_featured' => 0,
        'status' => 'draft',
        'source_type' => 'direct',
        'posted_date' => date('Y-m-d'),
        'deadline' => '',
        'expiry_date' => default_expiry_date(date('Y-m-d')),
        'vacancy_image' => '',
    ];

    if ($job) {
        $defaults = array_merge($defaults, [
            'title' => $job['title'] ?? '',
            'company_id' => $job['company_id'] ?? '',
            'field_id' => $job['field_id'] ?? '',
            'company_category_id' => $job['company_category_id'] ?? '',
            'location' => $job['location'] ?? '',
            'job_type' => $job['job_type'] ?? 'Full Time',
            'short_description' => $job['short_description'] ?? '',
            'full_description' => $job['full_description'] ?? '',
            'requirements' => $job['requirements'] ?? '',
            'application_process' => $job['application_process'] ?? '',
            'application_link' => $job['application_link'] ?? '',
            'is_featured' => $job['is_featured'] ?? 0,
            'status' => $job['status'] ?? 'draft',
            'source_type' => $job['source_type'] ?? 'direct',
            'posted_date' => $job['posted_date'] ?? date('Y-m-d'),
            'deadline' => $job['deadline'] ?? '',
            'expiry_date' => $job['expiry_date'] ?? default_expiry_date($job['posted_date'] ?? null),
            'vacancy_image' => $job['vacancy_image'] ?? '',
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($defaults as $key => $value) {
            if ($key === 'is_featured') {
                $defaults[$key] = !empty($_POST[$key]) ? 1 : 0;
                continue;
            }

            if (isset($_POST[$key])) {
                $defaults[$key] = trim((string)$_POST[$key]);
            }
        }
    }

    return $defaults;
}

function find_or_create_named_record(PDO $pdo, string $table, string $name): ?int
{
    $name = trim($name);

    if ($name === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return null;
    }

    $existing = fetch_value_safe($pdo, "SELECT id FROM `$table` WHERE name = :name LIMIT 1", ['name' => $name], null);
    if ($existing) {
        return (int)$existing;
    }

    $stmt = $pdo->prepare("INSERT INTO `$table` (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);
    return (int)$pdo->lastInsertId();
}

function valid_select_id($value): ?int
{
    $id = (int)$value;
    return $id > 0 ? $id : null;
}

function save_job_from_form(PDO $pdo, ?array $existingJob = null): array
{
    $errors = [];
    $values = job_form_values($existingJob);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    }

    $values['title'] = trim($values['title']);
    $values['short_description'] = trim($values['short_description']);
    $values['application_link'] = trim($values['application_link']);

    if ($values['title'] === '') {
        $errors[] = 'Job title is required.';
    }

    if ($values['short_description'] === '') {
        $errors[] = 'Short description is required.';
    }

    if ($values['application_link'] === '' || !filter_var($values['application_link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'A valid application link is required.';
    }

    $allowedTypes = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance'];
    if (!in_array($values['job_type'], $allowedTypes, true)) {
        $values['job_type'] = 'Full Time';
    }

    $allowedStatuses = ['draft', 'published', 'expired'];
    if (!in_array($values['status'], $allowedStatuses, true)) {
        $values['status'] = 'draft';
    }

    $allowedSources = ['company', 'agent', 'direct'];
    if (!in_array($values['source_type'], $allowedSources, true)) {
        $values['source_type'] = 'direct';
    }

    $postedDate = normalize_date($values['posted_date']);
    if ($values['posted_date'] !== '' && !$postedDate) {
        $errors[] = 'Posted date must be a valid date.';
    }

    $deadline = normalize_date($values['deadline']);
    if ($values['deadline'] !== '' && !$deadline) {
        $errors[] = 'Deadline must be a valid date.';
    }

    $expiryDate = normalize_date($values['expiry_date']);
    if ($values['expiry_date'] !== '' && !$expiryDate) {
        $errors[] = 'Expiry date must be a valid date.';
    }

    $postedDate = $postedDate ?: date('Y-m-d');
    $expiryDate = $expiryDate ?: default_expiry_date($postedDate);

    $companyCategoryId = valid_select_id($values['company_category_id']);
    if ($values['new_company_category_name'] !== '') {
        $companyCategoryId = find_or_create_named_record($pdo, 'company_categories', $values['new_company_category_name']);
    }

    $fieldId = valid_select_id($values['field_id']);
    if ($values['new_field_name'] !== '') {
        $fieldId = find_or_create_named_record($pdo, 'fields_of_study', $values['new_field_name']);
    }

    if (!$fieldId) {
        $errors[] = 'Choose or create a job category.';
    }

    $companyId = valid_select_id($values['company_id']);
    $company = null;

    if ($companyId) {
        $company = fetch_row_safe($pdo, "SELECT * FROM companies WHERE id = :id LIMIT 1", ['id' => $companyId]);
        if (!$company) {
            $errors[] = 'Selected company was not found.';
            $companyId = null;
        }
    }

    $newCompanyName = trim($values['new_company_name']);
    if ($newCompanyName !== '') {
        $existingCompany = fetch_row_safe($pdo, "SELECT * FROM companies WHERE name = :name LIMIT 1", ['name' => $newCompanyName]);
        if ($existingCompany) {
            $company = $existingCompany;
            $companyId = (int)$existingCompany['id'];
        } else {
            $logoUpload = save_uploaded_image('company_logo', 'logos');
            if ($logoUpload['error']) {
                $errors[] = $logoUpload['error'];
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, slug, company_category_id, logo, website, description)
                    VALUES (:name, :slug, :company_category_id, :logo, :website, :description)
                ");
                $stmt->execute([
                    'name' => $newCompanyName,
                    'slug' => unique_slug($pdo, 'companies', $newCompanyName),
                    'company_category_id' => $companyCategoryId,
                    'logo' => $logoUpload['filename'],
                    'website' => trim($values['company_website']) ?: null,
                    'description' => trim($values['company_description']) ?: null,
                ]);
                $companyId = (int)$pdo->lastInsertId();
                $company = fetch_row_safe($pdo, "SELECT * FROM companies WHERE id = :id", ['id' => $companyId]);
            }
        }
    }

    if (!$companyId) {
        $errors[] = 'Choose an existing company or create a new one.';
    }

    if ($companyId && $newCompanyName === '') {
        $currentLogo = $company['logo'] ?? null;
        $logoUpload = save_uploaded_image('company_logo', 'logos', $currentLogo);
        if ($logoUpload['error']) {
            $errors[] = $logoUpload['error'];
        } elseif ($logoUpload['filename'] !== $currentLogo || $companyCategoryId) {
            $stmt = $pdo->prepare("
                UPDATE companies
                SET logo = :logo,
                    company_category_id = COALESCE(:company_category_id, company_category_id)
                WHERE id = :id
            ");
            $stmt->execute([
                'logo' => $logoUpload['filename'],
                'company_category_id' => $companyCategoryId,
                'id' => $companyId,
            ]);
        }
    }

    if (!$companyCategoryId && $company) {
        $companyCategoryId = valid_select_id($company['company_category_id'] ?? null);
    }

    $vacancyUpload = save_uploaded_image('vacancy_image', 'jobs', $existingJob['vacancy_image'] ?? null);
    if ($vacancyUpload['error']) {
        $errors[] = $vacancyUpload['error'];
    }

    if (!empty($errors)) {
        $values['posted_date'] = $postedDate;
        $values['deadline'] = $deadline ?: '';
        $values['expiry_date'] = $expiryDate;
        return ['success' => false, 'errors' => $errors, 'values' => $values];
    }

    $jobData = [
        'title' => $values['title'],
        'slug' => unique_slug($pdo, 'jobs', $values['title'], $existingJob ? (int)$existingJob['id'] : null),
        'company_id' => $companyId,
        'field_id' => $fieldId,
        'company_category_id' => $companyCategoryId,
        'location' => trim($values['location']) ?: null,
        'job_type' => $values['job_type'],
        'short_description' => $values['short_description'],
        'full_description' => trim($values['full_description']) ?: null,
        'requirements' => trim($values['requirements']) ?: null,
        'application_process' => trim($values['application_process']) ?: null,
        'application_link' => $values['application_link'],
        'vacancy_image' => $vacancyUpload['filename'],
        'is_featured' => !empty($values['is_featured']) ? 1 : 0,
        'status' => $values['status'],
        'source_type' => $values['source_type'],
        'posted_date' => $postedDate,
        'deadline' => $deadline,
        'expiry_date' => $expiryDate,
    ];

    if ($existingJob) {
        $jobData['id'] = (int)$existingJob['id'];
        $stmt = $pdo->prepare("
            UPDATE jobs
            SET title = :title,
                slug = :slug,
                company_id = :company_id,
                field_id = :field_id,
                company_category_id = :company_category_id,
                location = :location,
                job_type = :job_type,
                short_description = :short_description,
                full_description = :full_description,
                requirements = :requirements,
                application_process = :application_process,
                application_link = :application_link,
                vacancy_image = :vacancy_image,
                is_featured = :is_featured,
                status = :status,
                source_type = :source_type,
                posted_date = :posted_date,
                deadline = :deadline,
                expiry_date = :expiry_date
            WHERE id = :id
        ");
        $stmt->execute($jobData);

        return ['success' => true, 'job_id' => (int)$existingJob['id']];
    }

    $stmt = $pdo->prepare("
        INSERT INTO jobs (
            title, slug, company_id, field_id, company_category_id, location, job_type,
            short_description, full_description, requirements, application_process,
            application_link, vacancy_image, is_featured, status, source_type,
            posted_date, deadline, expiry_date
        ) VALUES (
            :title, :slug, :company_id, :field_id, :company_category_id, :location, :job_type,
            :short_description, :full_description, :requirements, :application_process,
            :application_link, :vacancy_image, :is_featured, :status, :source_type,
            :posted_date, :deadline, :expiry_date
        )
    ");
    $stmt->execute($jobData);

    return ['success' => true, 'job_id' => (int)$pdo->lastInsertId()];
}

