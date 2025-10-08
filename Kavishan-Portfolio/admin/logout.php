<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

adminLogout();

header('Location: login.php');
exit;
