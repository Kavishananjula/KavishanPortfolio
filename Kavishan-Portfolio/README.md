## Kavishan Portfolio Admin Setup

This template now loads hero assets and project content from a MySQL database, with a lightweight admin panel for edits.

### 1. Requirements

- PHP 8.0 or newer (the template ships inside XAMPP already)
- MySQL 5.7+ / MariaDB 10+
- Write access to `uploads/` for image storage

### 2. Database

1. Update the DSN, username, and password in `inc/db.php` to match your local MySQL credentials.
2. Import `database.sql` into MySQL:
   ```bash
   mysql -u root -p < database.sql
   ```
   This creates the `kavishan_portfolio` database with the `settings` and `projects` tables plus starter content.

### 3. Admin Panel

- URL: `/admin/login.php`
- Default credentials are defined in `admin/bootstrap.php` (`ADMIN_USERNAME`, `ADMIN_PASSWORD`). **Change these before deploying.**
- Features:
  - Create, edit, and delete project entries (with image upload, tech stack, links, and ordering).
  - Upload hero/header/footer imagery, fine-tune hero spacing, manage CV downloads, and keep social media links in sync—all stored in the `settings` table.

Uploaded images are stored under:

- `uploads/projects/`
- `uploads/hero/`

Make sure these directories are writable by PHP.

### 4. Front-End

- The landing page is now `index.php`. Navigation already points to this file.
- Project tabs populate from the live database; if the DB is unreachable, bundled demo data is shown instead.
- Hero imagery defaults to `assets/img/author/author1.png` until a custom image is uploaded via the admin panel.

### 5. Optional Tweaks

- Update the hero text and other copy in `index.php` as needed.
- Adjust section styling inside `assets/css/theme.css`.

Happy shipping!
