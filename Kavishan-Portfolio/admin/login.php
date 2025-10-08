<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (isAdminAuthenticated()) {
    header('Location: projects.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please provide both username and password.';
    } elseif (attemptAdminLogin($username, $password)) {
        header('Location: projects.php');
        exit;
    } else {
        $error = 'Invalid credentials. Please try again.';
    }
}

$brandingLogo = '../assets/img/logo/logo.png';
try {
    $pdo = getPDO();
    $settings = getSettings($pdo);
    if (!empty($settings['header_logo'])) {
        $brandingLogo = '../' . ltrim($settings['header_logo'], '/');
    }
} catch (Throwable $e) {
    // branding logo will fall back to default if the database is unavailable.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kavishan Admin Login</title>
    <link rel="stylesheet" href="../assets/css/plugins/bootstrap.min.css">
    <style>
        body {
            background: #050c1f;
            color: #f5f8ff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: "Poppins", sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 32px;
            border-radius: 18px;
            background: linear-gradient(160deg, rgba(12, 24, 46, 0.95), rgba(4, 12, 29, 0.96));
            box-shadow: 0 25px 60px rgba(2, 12, 41, 0.55);
        }
        .login-card h1 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .login-logo {
            height: 32px;
            width: auto;
        }
        .login-card h1 {
            font-size: 24px;
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: 0.12em;
        }
        .form-control {
            background: rgba(6, 14, 28, 0.85);
            border: 1px solid rgba(86, 144, 255, 0.35);
            color: #f5f8ff;
        }
        .btn-primary {
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
            background: linear-gradient(135deg, #44f2ff, #2f80ff);
            border: none;
        }
        .alert {
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1><img src="<?= htmlspecialchars($brandingLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan" class="login-logo"> Kavishan Admin</h1>
        <?php if ($error): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>
</body>
</html>
