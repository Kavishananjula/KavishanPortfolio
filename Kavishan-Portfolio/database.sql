-- Database schema for Kavishan portfolio admin panel

CREATE DATABASE IF NOT EXISTS `kavishan_portfolio`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kavishan_portfolio`;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(120) NOT NULL,
  `category_slug` VARCHAR(150) NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `description` TEXT NOT NULL,
  `tech_stack` TEXT NULL,
  `primary_language` VARCHAR(80) NOT NULL,
  `project_focus` VARCHAR(160) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `live_demo_url` VARCHAR(255) DEFAULT NULL,
  `live_demo_label` VARCHAR(120) DEFAULT NULL,
  `github_url` VARCHAR(255) DEFAULT NULL,
  `github_label` VARCHAR(120) DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_unique` (`category_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES
('hero_image', 'assets/img/author/author1.png'),
('header_logo', 'assets/img/logo/logo.png'),
('footer_logo', 'assets/img/logo/logo.png'),
('social_facebook', '#'),
('social_twitter', '#'),
('social_linkedin', '#'),
('social_instagram', '#'),
('hero_padding', '260')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

INSERT INTO `projects`
(`category_name`, `category_slug`, `title`, `description`, `tech_stack`, `primary_language`, `project_focus`,
 `image_path`, `live_demo_url`, `live_demo_label`, `github_url`, `github_label`, `display_order`)
VALUES
('Laravel', 'laravel', 'SaaS Billing Console',
 'A subscription management dashboard with automated invoicing, approval workflows, and real-time revenue analytics designed for recurring businesses.',
 'Laravel, MySQL, Inertia.js, Laravel Cashier', 'PHP', 'Subscription Platform',
 'assets/img/project/project1.png', 'https://drive.google.com/file/d/1LARAVEL_DEMO/view?usp=sharing', 'Watch Live Demo',
 'https://github.com/kavishan/laravel-saas', 'View Source', 1),
('Bootstrap', 'bootstrap', 'Marketing Landing Kit',
 'A conversion-focused landing page system with themed sections, pricing toggles, and reusable components tuned for rapid campaign launches.',
 'Bootstrap 5, SCSS, Alpine.js, Gulp', 'HTML & SCSS', 'Marketing System',
 'assets/img/project/project2.png', 'https://drive.google.com/file/d/1BOOTSTRAP_DEMO/view?usp=sharing', 'Preview Walkthrough',
 'https://github.com/kavishan/bootstrap-kit', 'View Source', 2),
('Tailwind CSS', 'tailwind-css', 'Team Analytics Portal',
 'A headless analytics workspace with modular widgets, role-based dashboards, and motion-first interactions powered by Tailwind CSS.',
 'Next.js, Tailwind CSS, Framer Motion, Headless UI', 'TypeScript', 'Analytics Dashboard',
 'assets/img/project/project3.png', 'https://drive.google.com/file/d/1TAILWIND_DEMO/view?usp=sharing', 'Watch Live Demo',
 'https://github.com/kavishan/tailwind-analytics', 'View Source', 3),
('Python', 'python', 'AI Support Triage',
 'An intelligent support triage pipeline that prioritises tickets, detects sentiment, and orchestrates Slack/SMS alerts through automated playbooks.',
 'FastAPI, Python, PostgreSQL, Redis', 'Python', 'Automation Pipeline',
 'assets/img/project/project4.png', 'https://drive.google.com/file/d/1PYTHON_DEMO/view?usp=sharing', 'Watch Live Demo',
 'https://github.com/kavishan/python-triage', 'View Source', 4),
('C++', 'cpp', '3D Pathfinding Simulator',
 'A real-time navigation simulator with GPU-accelerated pathfinding, profiler overlays, and scenario recording for robotics research teams.',
 'C++, OpenGL, ImGui', 'C++', 'Desktop Simulation',
 'assets/img/project/portfolio1.jpg', 'https://drive.google.com/file/d/1CPP_DEMO/view?usp=sharing', 'Watch Live Demo',
 'https://github.com/kavishan/cpp-pathfinding', 'View Source', 5),
('UI / UX Design', 'ui-ux-design', 'Fintech Mobile Concept',
 'A high-fidelity mobile banking experience featuring gesture-driven controls, atomic components, and narrated user journey prototypes.',
 'Figma, Design Tokens, Prototype Flows', 'Design', 'Mobile Experience',
 'assets/img/project/portfolio2.jpg', 'https://drive.google.com/file/d/1UIUX_DEMO/view?usp=sharing', 'Preview Prototype',
 'https://github.com/kavishan/fintech-concept', 'Download Files', 6)
ON DUPLICATE KEY UPDATE
 `title` = VALUES(`title`),
 `description` = VALUES(`description`),
 `tech_stack` = VALUES(`tech_stack`),
 `primary_language` = VALUES(`primary_language`),
 `project_focus` = VALUES(`project_focus`),
 `image_path` = VALUES(`image_path`),
 `live_demo_url` = VALUES(`live_demo_url`),
 `live_demo_label` = VALUES(`live_demo_label`),
 `github_url` = VALUES(`github_url`),
 `github_label` = VALUES(`github_label`),
 `display_order` = VALUES(`display_order`);
