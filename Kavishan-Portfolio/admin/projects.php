<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

requireAdmin();

$errors = [];
$success = null;
$editingProject = null;
$dbError = null;
$pdo = null;

try {
    $pdo = getPDO();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$brandingLogo = '../assets/img/logo/logo.png';
$settings = [];
if ($pdo) {
    $settings = getSettings($pdo);
    if (!empty($settings['header_logo'])) {
        $brandingLogo = '../' . ltrim($settings['header_logo'], '/');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $errors[] = 'Database connection failed. Please verify your settings in inc/db.php.';
    }

    if (isset($_POST['delete_id'])) {
        if ($pdo) {
            $deleteId = (int) $_POST['delete_id'];
            $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
            $stmt->execute(['id' => $deleteId]);
            $success = 'Project deleted successfully.';
        } else {
            $errors[] = 'Unable to delete project because the database connection is unavailable.';
        }
    } else {
        if (!$pdo) {
            $errors[] = 'Unable to save project because the database connection is unavailable.';
        } else {
            $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : null;
            $categoryName = trim($_POST['category_name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $techStack = trim($_POST['tech_stack'] ?? '');
        $language = trim($_POST['primary_language'] ?? '');
        $focus = trim($_POST['project_focus'] ?? '');
        $liveDemo = trim($_POST['live_demo_url'] ?? '');
        $liveLabel = trim($_POST['live_demo_label'] ?? '');
        $githubUrl = trim($_POST['github_url'] ?? '');
        $githubLabel = trim($_POST['github_label'] ?? '');
        $displayOrder = (int) ($_POST['display_order'] ?? 0);

        if ($categoryName === '') {
            $errors[] = 'Category name is required.';
        }
        if ($title === '') {
            $errors[] = 'Project title is required.';
        }
        if ($description === '') {
            $errors[] = 'Project description is required.';
        }
        if ($language === '') {
            $errors[] = 'Primary language is required.';
        }
        if ($focus === '') {
            $errors[] = 'Project focus is required.';
        }

        $imagePath = null;
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = handleProjectUpload($_FILES['project_image'], __DIR__ . '/../uploads/projects');
            if ($upload['status'] === true && isset($upload['path'])) {
                $imagePath = $upload['path'];
            } else {
                $errors[] = $upload['error'] ?? 'Image upload failed.';
            }
        } elseif (!$projectId) {
            $errors[] = 'Project image is required for new entries.';
        }

            if (!$errors) {
                $slugBase = slugify($categoryName);
                if ($slugBase === '') {
                    $slugBase = 'project';
                }
                $slug = $slugBase;
            $iteration = 1;
            do {
                $params = ['slug' => $slug];
                $query = 'SELECT id FROM projects WHERE category_slug = :slug';
                if ($projectId) {
                    $query .= ' AND id != :current';
                    $params['current'] = $projectId;
                }
                $check = $pdo->prepare($query);
                $check->execute($params);
                $exists = $check->fetchColumn();
                if ($exists) {
                    $slug = $slugBase . '-' . $iteration;
                    ++$iteration;
                }
            } while ($exists);

            if ($projectId) {
                $fields = [
                    'category_name'   => $categoryName,
                    'category_slug'   => $slug,
                    'title'           => $title,
                    'description'     => $description,
                    'tech_stack'      => $techStack,
                    'primary_language'=> $language,
                    'project_focus'   => $focus,
                    'live_demo_url'   => $liveDemo,
                    'live_demo_label' => $liveLabel,
                    'github_url'      => $githubUrl,
                    'github_label'    => $githubLabel,
                    'display_order'   => $displayOrder,
                    'id'              => $projectId,
                ];

                $setClauses = [
                    'category_name = :category_name',
                    'category_slug = :category_slug',
                    'title = :title',
                    'description = :description',
                    'tech_stack = :tech_stack',
                    'primary_language = :primary_language',
                    'project_focus = :project_focus',
                    'live_demo_url = :live_demo_url',
                    'live_demo_label = :live_demo_label',
                    'github_url = :github_url',
                    'github_label = :github_label',
                    'display_order = :display_order',
                ];
                if ($imagePath) {
                    $fields['image_path'] = $imagePath;
                    $setClauses[] = 'image_path = :image_path';
                }

                $sql = 'UPDATE projects SET ' . implode(', ', $setClauses) . ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($fields);
                $success = 'Project updated successfully.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO projects
                    (category_name, category_slug, title, description, tech_stack, primary_language, project_focus,
                     image_path, live_demo_url, live_demo_label, github_url, github_label, display_order)
                    VALUES
                    (:category_name, :category_slug, :title, :description, :tech_stack, :primary_language, :project_focus,
                     :image_path, :live_demo_url, :live_demo_label, :github_url, :github_label, :display_order)'
                );
                $stmt->execute([
                    'category_name'   => $categoryName,
                    'category_slug'   => $slug,
                    'title'           => $title,
                    'description'     => $description,
                    'tech_stack'      => $techStack,
                    'primary_language'=> $language,
                    'project_focus'   => $focus,
                    'image_path'      => $imagePath,
                    'live_demo_url'   => $liveDemo,
                    'live_demo_label' => $liveLabel,
                    'github_url'      => $githubUrl,
                    'github_label'    => $githubLabel,
                    'display_order'   => $displayOrder,
                ]);
                $success = 'Project created successfully.';
            }
        }
    }
}
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $editingProject = $stmt->fetch();
        if (!$editingProject) {
            $errors[] = 'Project not found.';
        }
    } else {
        $errors[] = 'Unable to load project while database connection is unavailable.';
    }
}

$projects = $pdo ? getProjects($pdo) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Admin - Kavishan</title>
    <link rel="stylesheet" href="../assets/css/plugins/bootstrap.min.css">
    <style>
        body {
            background: #050c1f;
            color: #f5f8ff;
            font-family: "Poppins", sans-serif;
            padding: 40px 0;
        }
        .container {
            max-width: 1100px;
        }
        .card {
            background: linear-gradient(150deg, rgba(12, 24, 46, 0.95), rgba(5, 12, 30, 0.96));
            border: none;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(2, 12, 41, 0.55);
        }
        .admin-logo {
            height: 36px;
            width: auto;
        }
        .card h2 {
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .btn-primary {
            background: linear-gradient(135deg, #44f2ff, #2f80ff);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        a.nav-link {
            color: #9ebdff !important;
        }
        a.nav-link:hover {
            color: #44f2ff !important;
        }
        table tbody tr td {
            vertical-align: middle;
        }
        label {
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.16em;
        }
        .form-control {
            background: rgba(6, 14, 28, 0.85);
            border: 1px solid rgba(86, 144, 255, 0.3);
            color: #f1f5ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-uppercase">Kavishan Admin</h1>
            <div>
                <a href="hero.php" class="nav-link d-inline-block me-3">Branding</a>
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

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card mb-5">
            <div class="card-body"><img src="<?= htmlspecialchars($brandingLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan" class="admin-logo mb-3">
                <h2 class="h5 mb-4"><?= $editingProject ? 'Update Project' : 'Create Project'; ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <?php if ($editingProject): ?>
                        <input type="hidden" name="project_id" value="<?= (int) $editingProject['id']; ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="category_name">Category</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" required value="<?= htmlspecialchars($editingProject['category_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="title">Project Title</label>
                            <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($editingProject['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($editingProject['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="tech_stack">Tech Stack (comma separated)</label>
                            <input type="text" id="tech_stack" name="tech_stack" class="form-control" value="<?= htmlspecialchars($editingProject['tech_stack'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="primary_language">Primary Language</label>
                            <input type="text" id="primary_language" name="primary_language" class="form-control" required value="<?= htmlspecialchars($editingProject['primary_language'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="project_focus">Project Focus</label>
                            <input type="text" id="project_focus" name="project_focus" class="form-control" required value="<?= htmlspecialchars($editingProject['project_focus'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="live_demo_url">Live Demo URL</label>
                            <input type="url" id="live_demo_url" name="live_demo_url" class="form-control" value="<?= htmlspecialchars($editingProject['live_demo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="live_demo_label">Live Demo Label</label>
                            <input type="text" id="live_demo_label" name="live_demo_label" class="form-control" value="<?= htmlspecialchars($editingProject['live_demo_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="github_url">GitHub URL</label>
                            <input type="url" id="github_url" name="github_url" class="form-control" value="<?= htmlspecialchars($editingProject['github_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="github_label">GitHub Label</label>
                            <input type="text" id="github_label" name="github_label" class="form-control" value="<?= htmlspecialchars($editingProject['github_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" class="form-control" value="<?= htmlspecialchars((string) ($editingProject['display_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="project_image">Project Image <?= $editingProject ? '(leave blank to keep current)' : ''; ?></label>
                            <input type="file" id="project_image" name="project_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <?php if ($editingProject): ?>
                                <small class="d-block mt-2">Current: <?= htmlspecialchars($editingProject['image_path'], ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= $editingProject ? 'Update Project' : 'Create Project'; ?></button>
                        <?php if ($editingProject): ?>
                            <a href="projects.php" class="btn btn-outline-light">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body"><img src="<?= htmlspecialchars($brandingLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan" class="admin-logo mb-3">
                <h2 class="h5 mb-4">Existing Projects</h2>
                <div class="table-responsive">
                    <table class="table table-borderless table-striped align-middle text-white">
                        <thead>
                            <tr>
                                <th scope="col">Order</th>
                                <th scope="col">Category</th>
                                <th scope="col">Title</th>
                                <th scope="col">Language</th>
                                <th scope="col">Focus</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?= (int) $project['display_order']; ?></td>
                                    <td><?= htmlspecialchars($project['category_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($project['primary_language'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($project['project_focus'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end">
                                        <a href="projects.php?id=<?= (int) $project['id']; ?>" class="btn btn-sm btn-outline-info">Edit</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this project?');">
                                            <input type="hidden" name="delete_id" value="<?= (int) $project['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$projects): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No projects saved yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
