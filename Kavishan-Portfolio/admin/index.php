<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (isAdminAuthenticated()) {
    header('Location: projects.php');
} else {
    header('Location: login.php');
}
exit;
