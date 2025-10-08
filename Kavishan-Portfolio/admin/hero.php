<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

requireAdmin();

$errors = [];
$successMessages = [];
$dbError = null;
$pdo = null;

$currentHeroImage = 'assets/img/author/author1.png';
$currentHeaderLogo = 'assets/img/logo/logo.png';
$currentFooterLogo = 'assets/img/logo/logo.png';
$currentCvPath = 'assets/docs/kavishan-anjula-cv.pdf';
$heroPadding = 260;
$socialLinks = [
    'facebook'  => '',
    'twitter'   => '',
    'linkedin'  => '',
    'instagram' => '',
];

try {
    $pdo = getPDO();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

if ($pdo) {
    $settings = getSettings($pdo);
    if (!empty($settings['hero_image'])) {
        $currentHeroImage = $settings['hero_image'];
    }
    if (!empty($settings['header_logo'])) {
        $currentHeaderLogo = $settings['header_logo'];
    }
    if (!empty($settings['footer_logo'])) {
        $currentFooterLogo = $settings['footer_logo'];
    }
    if (!empty($settings['cv_path'])) {
        $currentCvPath = $settings['cv_path'];
    }
    if (!empty($settings['hero_padding'])) {
        $heroPadding = (int) $settings['hero_padding'];
    }
    foreach ($socialLinks as $key => $value) {
        $settingKey = 'social_' . $key;
        if (array_key_exists($settingKey, $settings)) {
            $socialLinks[$key] = $settings[$settingKey];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $errors[] = 'Database connection failed. Please verify your settings in inc/db.php.';
    }

    $action = $_POST['action'] ?? '';

    if (!$pdo) {
        $errors[] = 'Database connection failed. Please verify your settings in inc/db.php.';
    } else {
        switch ($action) {
            case 'hero':
                if (!isset($_FILES['hero_image']) || $_FILES['hero_image']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Please choose an image to upload.';
                    break;
                }
                $upload = handleProjectUpload($_FILES['hero_image'], __DIR__ . '/../uploads/hero');
                if ($upload['status'] === true && isset($upload['path'])) {
                    $currentHeroImage = $upload['path'];
                    setSetting($pdo, 'hero_image', $currentHeroImage);
                    $successMessages[] = 'Hero profile image updated successfully.';
                } else {
                    $errors[] = $upload['error'] ?? 'Upload failed.';
                }
                break;

            case 'cv':
                if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Please choose a CV file to upload.';
                    break;
                }
                $upload = handleProjectUpload($_FILES['cv_file'], __DIR__ . '/../uploads/docs', ['pdf', 'doc', 'docx']);
                if ($upload['status'] === true && isset($upload['path'])) {
                    $currentCvPath = $upload['path'];
                    setSetting($pdo, 'cv_path', $currentCvPath);
                    $successMessages[] = 'CV file updated successfully.';
                } else {
                    $errors[] = $upload['error'] ?? 'Upload failed.';
                }
                break;

            case 'hero_padding':
                $inputPadding = (int) ($_POST['hero_padding_value'] ?? $heroPadding);
                if ($inputPadding < 120 || $inputPadding > 420) {
                    $errors[] = 'Please enter a hero spacing between 120 and 420 pixels.';
                    break;
                }
                $heroPadding = $inputPadding;
                setSetting($pdo, 'hero_padding', (string) $heroPadding);
                $successMessages[] = 'Hero spacing updated successfully.';
                break;

            case 'header_logo':
                if (!isset($_FILES['header_logo']) || $_FILES['header_logo']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Please choose a logo to upload.';
                    break;
                }
                $upload = handleProjectUpload($_FILES['header_logo'], __DIR__ . '/../uploads/branding');
                if ($upload['status'] === true && isset($upload['path'])) {
                    $currentHeaderLogo = $upload['path'];
                    setSetting($pdo, 'header_logo', $currentHeaderLogo);
                    $successMessages[] = 'Header logo updated successfully.';
                } else {
                    $errors[] = $upload['error'] ?? 'Upload failed.';
                }
                break;

            case 'footer_logo':
                if (!isset($_FILES['footer_logo']) || $_FILES['footer_logo']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Please choose a logo to upload.';
                    break;
                }
                $upload = handleProjectUpload($_FILES['footer_logo'], __DIR__ . '/../uploads/branding');
                if ($upload['status'] === true && isset($upload['path'])) {
                    $currentFooterLogo = $upload['path'];
                    setSetting($pdo, 'footer_logo', $currentFooterLogo);
                    $successMessages[] = 'Footer logo updated successfully.';
                } else {
                    $errors[] = $upload['error'] ?? 'Upload failed.';
                }
                break;

            case 'social_links':
                foreach ($socialLinks as $key => $existingValue) {
                    $submitted = trim($_POST[$key] ?? '');
                    setSetting($pdo, 'social_' . $key, $submitted);
                    $socialLinks[$key] = $submitted;
                }
                $successMessages[] = 'Social media links updated successfully.';
                break;

            default:
                $errors[] = 'Unknown action requested.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branding & Social - Kavishan Admin</title>
    <link rel="stylesheet" href="../assets/css/plugins/bootstrap.min.css">
    <style>
        body {
            background: #050c1f;
            color: #f5f8ff;
            font-family: "Poppins", sans-serif;
            padding: 40px 0;
        }
        .container {
            max-width: 760px;
        }
        .card {
            background: linear-gradient(150deg, rgba(12, 24, 46, 0.95), rgba(5, 12, 30, 0.96));
            border: none;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(2, 12, 41, 0.55);
        }
        .btn-primary {
            background: linear-gradient(135deg, #44f2ff, #2f80ff);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .form-control {
            background: rgba(6, 14, 28, 0.85);
            border: 1px solid rgba(86, 144, 255, 0.3);
            color: #f1f5ff;
        }
        a.nav-link {
            color: #9ebdff !important;
        }
        a.nav-link:hover {
            color: #44f2ff !important;
        }
        .current-image {
            border-radius: 16px;
            border: 1px solid rgba(74, 129, 252, 0.4);
            background: rgba(6, 14, 28, 0.6);
            padding: 18px;
        }
        .current-image img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-uppercase">Branding & Hero Assets</h1>
            <div>
                <a href="projects.php" class="nav-link d-inline-block me-3">Projects</a>
                <a href="logout.php" class="nav-link d-inline-block">Logout</a>
            </div>
        </div>

        <?php if ($dbError): ?>
            <div class="alert alert-warning">
                <strong>Database connection failed:</strong>
                <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessages): ?>
            <div class="alert alert-success">
                <ul class="mb-0">
                    <?php foreach ($successMessages as $message): ?>
                        <li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-4">Upload New Hero Image</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="hero">
                    <div class="mb-3">
                        <label for="hero_image" class="form-label text-uppercase">Choose Image</label>
                        <input type="file" class="form-control" id="hero_image" name="hero_image" accept=".jpg,.jpeg,.png,.webp" required>
                        <small class="text-muted d-block mt-2">Recommended size: 400x400px or larger square image.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Image</button>
                </form>
            </div>
        </div>

        <?php if ($currentHeroImage): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Current Image</h2>
                    <div class="current-image text-center">
                        <img src="../<?= htmlspecialchars($currentHeroImage, ENT_QUOTES, 'UTF-8'); ?>" alt="Current Hero Image" class="img-fluid" style="max-width: 240px;">
                        <div class="mt-3 text-muted small"><?= htmlspecialchars($currentHeroImage, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card mt-4">
            <div class="card-body">
                <h2 class="h5 mb-4">Downloadable CV</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="cv">
                    <div class="mb-3">
                        <label for="cv_file" class="form-label text-uppercase">Upload CV File</label>
                        <input type="file" class="form-control" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
                        <small class="text-muted d-block mt-2">Accepted types: PDF, DOC, DOCX.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update CV</button>
                </form>
                <?php if ($currentCvPath): ?>
                    <div class="current-image text-center mt-3">
                        <a href="../<?= htmlspecialchars($currentCvPath, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-light" target="_blank" rel="noopener">Download current CV</a>
                        <div class="mt-2 text-muted small"><?= htmlspecialchars($currentCvPath, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h2 class="h5 mb-4">Hero Section Spacing</h2>
                <form method="post">
                    <input type="hidden" name="action" value="hero_padding">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="hero_padding_value" class="form-label text-uppercase">Top Padding (px)</label>
                            <input type="number" class="form-control" id="hero_padding_value" name="hero_padding_value" min="120" max="420" value="<?= htmlspecialchars((string) $heroPadding, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">Save Spacing</button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Controls how far the hero card sits below the navigation. Recommended range: 120-420px.</small>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h2 class="h5 mb-4">Site Logos</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h3 class="h6 text-uppercase mb-3">Header Logo</h3>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="header_logo">
                            <div class="mb-3">
                                <label for="header_logo" class="form-label text-uppercase">Upload Logo</label>
                                <input type="file" class="form-control" id="header_logo" name="header_logo" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Header Logo</button>
                        </form>
                        <div class="current-image text-center mt-3">
                            <img src="../<?= htmlspecialchars($currentHeaderLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Current Header Logo" class="img-fluid" style="max-width: 180px;">
                            <div class="mt-2 text-muted small"><?= htmlspecialchars($currentHeaderLogo, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h6 text-uppercase mb-3">Footer Logo</h3>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="footer_logo">
                            <div class="mb-3">
                                <label for="footer_logo" class="form-label text-uppercase">Upload Logo</label>
                                <input type="file" class="form-control" id="footer_logo" name="footer_logo" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Footer Logo</button>
                        </form>
                        <div class="current-image text-center mt-3">
                            <img src="../<?= htmlspecialchars($currentFooterLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Current Footer Logo" class="img-fluid" style="max-width: 180px;">
                            <div class="mt-2 text-muted small"><?= htmlspecialchars($currentFooterLogo, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h2 class="h5 mb-4">Social Media Links</h2>
                <form method="post">
                    <input type="hidden" name="action" value="social_links">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase" for="facebook">Facebook URL</label>
                            <input type="url" class="form-control" id="facebook" name="facebook" placeholder="https://facebook.com/yourprofile" value="<?= htmlspecialchars($socialLinks['facebook'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase" for="twitter">Twitter URL</label>
                            <input type="url" class="form-control" id="twitter" name="twitter" placeholder="https://twitter.com/yourhandle" value="<?= htmlspecialchars($socialLinks['twitter'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase" for="linkedin">LinkedIn URL</label>
                            <input type="url" class="form-control" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/you" value="<?= htmlspecialchars($socialLinks['linkedin'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase" for="instagram">Instagram URL</label>
                            <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/yourhandle" value="<?= htmlspecialchars($socialLinks['instagram'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Social Links</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
