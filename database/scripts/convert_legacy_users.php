<?php

/**
 * Converts legacy sacredio_cors users.sql dump into Laravel users INSERT statements.
 *
 * Usage: php database/scripts/convert_legacy_users.php "C:/path/to/users.sql"
 */

$sourceFile = $argv[1] ?? null;

if ($sourceFile === null || ! is_readable($sourceFile)) {
    fwrite(STDERR, "Usage: php database/scripts/convert_legacy_users.php <path-to-users.sql>\n");
    exit(1);
}

$sql = file_get_contents($sourceFile);

if (! preg_match('/INSERT INTO `users`[^;]+;/s', $sql, $insertMatch)) {
    fwrite(STDERR, "No INSERT statement found in source file.\n");
    exit(1);
}

$insertBlock = $insertMatch[0];

preg_match_all(
    '/\((\d+),\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'(\$2y\$[^\'\\\\]+)\',\s*(\d+),\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*(NULL|\'(?:[^\'\\\\]|\\\\.)*\'),\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*(\d+),\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*\'((?:[^\'\\\\]|\\\\.)*)\',\s*(\d+),\s*(\d+),\s*\'([^\']*)\',\s*\'([^\']*)\'\)/',
    $insertBlock,
    $rows,
    PREG_SET_ORDER
);

if ($rows === []) {
    fwrite(STDERR, "Could not parse any user rows.\n");
    exit(1);
}

function unescapeSql(string $value): string
{
    return stripcslashes(str_replace(["\\'", '\\\\'], ["'", '\\'], $value));
}

function sqlQuote(?string $value): string
{
    if ($value === null || $value === '') {
        return 'NULL';
    }

    return "'".str_replace("'", "''", $value)."'";
}

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

$outputPath = dirname(__DIR__).'/seeders/legacy_users_import.sql';
$values = [];
$skipped = [];
$seenEmails = [];

foreach ($rows as $row) {
    [
        ,
        $legacyId,
        $username,
        $emailRaw,
        $password,
        $type,
        $firstName,
        $lastName,
        ,
        $countryRaw,
        ,
        ,
        ,
        ,
        $contactInfo,
        ,
        $status,
        $activateCode,
        ,
        ,
        ,
        $createdOn,
    ] = $row;

    $email = normalizeEmail(unescapeSql($emailRaw));

    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $skipped[] = "legacy id {$legacyId}: invalid email ".trim(unescapeSql($emailRaw));

        continue;
    }

    if (isset($seenEmails[$email])) {
        $skipped[] = "legacy id {$legacyId}: duplicate email {$email} (kept legacy id {$seenEmails[$email]})";

        continue;
    }

    $seenEmails[$email] = $legacyId;

    $firstName = trim(unescapeSql($firstName));
    $lastName = trim(unescapeSql($lastName));
    $name = trim($firstName.' '.$lastName);

    if ($name === '') {
        $name = trim(unescapeSql($username));
    }

    if ($name === '') {
        $name = $email;
    }

    $country = trim(unescapeSql($countryRaw));
    if (strlen($country) > 120) {
        $country = substr($country, 0, 120);
    }

    $phone = trim(unescapeSql($contactInfo));
    if ($phone === '') {
        $phone = null;
    } elseif (strlen($phone) > 32) {
        $phone = substr($phone, 0, 32);
    }

    // Legacy status=1 means the account was activated on the old site.
    $isVerified = (int) $status === 1;
    $emailVerifiedAt = $isVerified ? $createdOn.' 00:00:00' : null;

    $createdAt = $createdOn !== '0000-00-00' ? $createdOn.' 00:00:00' : date('Y-m-d H:i:s');
    $updatedAt = $createdAt;

    $values[] = sprintf(
        '(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
        (int) $legacyId,
        sqlQuote($name),
        sqlQuote($firstName !== '' ? $firstName : null),
        sqlQuote($lastName !== '' ? $lastName : null),
        sqlQuote($email),
        sqlQuote($phone),
        sqlQuote($country !== '' ? $country : null),
        sqlQuote($emailVerifiedAt),
        sqlQuote($password),
        sqlQuote($createdAt),
        sqlQuote($updatedAt)
    );
}

$header = <<<'SQL'
-- Legacy users import for Laravel sacredionCors
-- Generated from old sacredio_cors.users dump
-- Passwords are existing bcrypt hashes ($2y$...) — users can log in with their old passwords.
-- Rows use INSERT ... ON DUPLICATE KEY UPDATE so re-imports are safe.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

SQL;

$body = "INSERT INTO `users` (`id`, `name`, `first_name`, `last_name`, `email`, `phone`, `country`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES\n"
    .implode(",\n", $values)
    ."\nON DUPLICATE KEY UPDATE\n"
    ."  `name` = VALUES(`name`),\n"
    ."  `first_name` = VALUES(`first_name`),\n"
    ."  `last_name` = VALUES(`last_name`),\n"
    ."  `phone` = VALUES(`phone`),\n"
    ."  `country` = VALUES(`country`),\n"
    ."  `email_verified_at` = VALUES(`email_verified_at`),\n"
    ."  `password` = VALUES(`password`),\n"
    ."  `updated_at` = VALUES(`updated_at`);\n\n"
    ."SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents($outputPath, $header.$body);

fwrite(STDOUT, "Converted ".count($values)." users -> {$outputPath}\n");

if ($skipped !== []) {
    fwrite(STDOUT, "Skipped ".count($skipped)." rows:\n");
    foreach (array_slice($skipped, 0, 20) as $line) {
        fwrite(STDOUT, "  - {$line}\n");
    }

    if (count($skipped) > 20) {
        fwrite(STDOUT, '  ... and '.(count($skipped) - 20)." more\n");
    }
}
