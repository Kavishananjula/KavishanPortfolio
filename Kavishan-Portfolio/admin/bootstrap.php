<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'admin123'; // Change this to a strong password for production use.

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

/**
 * Determines whether the current session is authenticated.
 */
function isAdminAuthenticated(): bool
{
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
}

/**
 * Forces authentication for protected routes.
 */
function requireAdmin(): void
{
    if (!isAdminAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Attempts to authenticate the supplied credentials.
 */
function attemptAdminLogin(string $username, string $password): bool
{
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_authenticated'] = true;

        return true;
    }

    return false;
}

/**
 * Logs out the active admin session.
 */
function adminLogout(): void
{
    $_SESSION['admin_authenticated'] = false;
    unset($_SESSION['admin_authenticated']);
    session_destroy();
}

/**
 * Sanitises uploaded filenames.
 */
function makeSafeFilename(string $filename): string
{
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-z0-9._-]/', '-', $filename) ?? '';

    return trim($filename, '-');
}

/**
 * Handles file uploads with extension validation.
 *
 * @return array{status: bool, path?: string, error?: string}
 */
function handleProjectUpload(array $file, string $destinationDirectory, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp']): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'The file is larger than the server allows (' . ini_get('upload_max_filesize') . ').',
            UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temp folder is missing. Contact support.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
        ];

        $message = $messages[$file['error']] ?? ('Upload error code: ' . $file['error']);

        return ['status' => false, 'error' => $message];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['status' => false, 'error' => 'Unsupported file type.'];
    }

    if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0775, true) && !is_dir($destinationDirectory)) {
        return ['status' => false, 'error' => 'Unable to create upload directory.'];
    }

    $filename = pathinfo($file['name'], PATHINFO_FILENAME);
    $safeFilename = makeSafeFilename($filename);
    $finalName = $safeFilename . '-' . time() . '.' . $extension;
    $targetPath = rtrim($destinationDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $finalName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['status' => false, 'error' => 'Failed to move uploaded file.'];
    }

    $absolute = realpath($targetPath) ?: $targetPath;
    $rootPath = realpath(__DIR__ . '/..');
    if ($rootPath && str_starts_with($absolute, $rootPath)) {
        $relativePath = ltrim(substr($absolute, strlen($rootPath)), DIRECTORY_SEPARATOR . '/');
    } else {
        $relativePath = $absolute;
    }

    return ['status' => true, 'path' => str_replace('\\', '/', $relativePath)];
}
