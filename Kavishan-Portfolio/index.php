<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';

$defaultProjects = [
    'laravel' => [
        'category'     => 'Laravel',
        'title'        => 'SaaS Billing Console',
        'description'  => 'A subscription management dashboard with automated invoicing, approval workflows, and real-time revenue analytics designed for recurring businesses.',
        'stack'        => ['Laravel', 'MySQL', 'Inertia.js', 'Laravel Cashier'],
        'language'     => 'PHP',
        'focus'        => 'Subscription Platform',
        'image'        => 'assets/img/project/project1.png',
        'liveDemo'     => 'https://drive.google.com/file/d/1LARAVEL_DEMO/view?usp=sharing',
        'liveLabel'    => 'Watch Live Demo',
        'github'       => 'https://github.com/kavishan/laravel-saas',
        'githubLabel'  => 'View Source',
    ],
    'bootstrap' => [
        'category'     => 'Bootstrap',
        'title'        => 'Marketing Landing Kit',
        'description'  => 'A conversion-focused landing page system with themed sections, pricing toggles, and reusable components tuned for rapid campaign launches.',
        'stack'        => ['Bootstrap 5', 'SCSS', 'Alpine.js', 'Gulp'],
        'language'     => 'HTML & SCSS',
        'focus'        => 'Marketing System',
        'image'        => 'assets/img/project/project2.png',
        'liveDemo'     => 'https://drive.google.com/file/d/1BOOTSTRAP_DEMO/view?usp=sharing',
        'liveLabel'    => 'Preview Walkthrough',
        'github'       => 'https://github.com/kavishan/bootstrap-kit',
        'githubLabel'  => 'View Source',
    ],
    'tailwind-css' => [
        'category'     => 'Tailwind CSS',
        'title'        => 'Team Analytics Portal',
        'description'  => 'A headless analytics workspace with modular widgets, role-based dashboards, and motion-first interactions powered by Tailwind CSS.',
        'stack'        => ['Next.js', 'Tailwind CSS', 'Framer Motion', 'Headless UI'],
        'language'     => 'TypeScript',
        'focus'        => 'Analytics Dashboard',
        'image'        => 'assets/img/project/project3.png',
        'liveDemo'     => 'https://drive.google.com/file/d/1TAILWIND_DEMO/view?usp=sharing',
        'liveLabel'    => 'Watch Live Demo',
        'github'       => 'https://github.com/kavishan/tailwind-analytics',
        'githubLabel'  => 'View Source',
    ],
    'python' => [
        'category'     => 'Python',
        'title'        => 'AI Support Triage',
        'description'  => 'An intelligent support triage pipeline that prioritises tickets, detects sentiment, and orchestrates Slack/SMS alerts through automated playbooks.',
        'stack'        => ['FastAPI', 'Python', 'PostgreSQL', 'Redis'],
        'language'     => 'Python',
        'focus'        => 'Automation Pipeline',
        'image'        => 'assets/img/project/project4.png',
        'liveDemo'     => 'https://drive.google.com/file/d/1PYTHON_DEMO/view?usp=sharing',
        'liveLabel'    => 'Watch Live Demo',
        'github'       => 'https://github.com/kavishan/python-triage',
        'githubLabel'  => 'View Source',
    ],
    'cpp' => [
        'category'     => 'C++',
        'title'        => '3D Pathfinding Simulator',
        'description'  => 'A real-time navigation simulator with GPU-accelerated pathfinding, profiler overlays, and scenario recording for robotics research teams.',
        'stack'        => ['C++', 'OpenGL', 'ImGui'],
        'language'     => 'C++',
        'focus'        => 'Desktop Simulation',
        'image'        => 'assets/img/project/portfolio1.jpg',
        'liveDemo'     => 'https://drive.google.com/file/d/1CPP_DEMO/view?usp=sharing',
        'liveLabel'    => 'Watch Live Demo',
        'github'       => 'https://github.com/kavishan/cpp-pathfinding',
        'githubLabel'  => 'View Source',
    ],
    'ui-ux-design' => [
        'category'     => 'UI / UX Design',
        'title'        => 'Fintech Mobile Concept',
        'description'  => 'A high-fidelity mobile banking experience featuring gesture-driven controls, atomic components, and narrated user journey prototypes.',
        'stack'        => ['Figma', 'Design Tokens', 'Prototype Flows'],
        'language'     => 'Design',
        'focus'        => 'Mobile Experience',
        'image'        => 'assets/img/project/portfolio2.jpg',
        'liveDemo'     => 'https://drive.google.com/file/d/1UIUX_DEMO/view?usp=sharing',
        'liveLabel'    => 'Preview Prototype',
        'github'       => 'https://github.com/kavishan/fintech-concept',
        'githubLabel'  => 'Download Files',
    ],
];

$dbError = null;
$heroImagePath = 'assets/img/author/author1.png';
$headerLogo = 'assets/img/logo/logo.png';
$footerLogo = 'assets/img/logo/logo.png';
$cvDownloadPath = 'assets/docs/kavishan-anjula-cv.pdf';
$heroPadding = 260;
$projectCategories = [];
$socialLinks = [
    'facebook'  => '#',
    'twitter'   => '#',
    'linkedin'  => '#',
    'instagram' => '#',
];

try {
    $pdo = getPDO();
    $settings = getSettings($pdo);
    if (!empty($settings['hero_image'])) {
        $heroImagePath = $settings['hero_image'];
    }
    if (!empty($settings['header_logo'])) {
        $headerLogo = $settings['header_logo'];
    }
    if (!empty($settings['footer_logo'])) {
        $footerLogo = $settings['footer_logo'];
    }
    if (!empty($settings['cv_path'])) {
        $cvDownloadPath = $settings['cv_path'];
    }
    if (!empty($settings['hero_padding'])) {
        $heroPadding = (int) $settings['hero_padding'];
    }
    foreach ($socialLinks as $key => $defaultLink) {
        $settingKey = 'social_' . $key;
        if (isset($settings[$settingKey]) && $settings[$settingKey] !== '') {
            $socialLinks[$key] = $settings[$settingKey];
        }
    }

    $projectRows = getProjects($pdo);
    if ($projectRows) {
        foreach ($projectRows as $project) {
            $groupKey = strtolower($project['category_name']);
            if (!isset($projectCategories[$groupKey])) {
                $slug = $project['category_slug'] ?: slugify($project['category_name']);
                $projectCategories[$groupKey] = [
                    'slug'     => $slug,
                    'label'    => $project['category_name'],
                    'projects' => [],
                ];
            }
            $projectCategories[$groupKey]['projects'][] = [
                'category'     => $project['category_name'],
                'title'        => $project['title'],
                'description'  => $project['description'],
                'stack'        => parseStack($project['tech_stack']),
                'language'     => $project['primary_language'],
                'focus'        => $project['project_focus'],
                'image'        => $project['image_path'] ?: 'assets/img/project/project1.png',
                'liveDemo'     => $project['live_demo_url'],
                'liveLabel'    => $project['live_demo_label'] ?: 'Watch Live Demo',
                'github'       => $project['github_url'],
                'githubLabel'  => $project['github_label'] ?: 'View Source',
            ];
        }
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$buildSocialLink = static function (array $links, string $key): array {
    $url = trim($links[$key] ?? '');
    if ($url === '') {
        $url = '#';
    }
    $attr = $url !== '#' ? ' target="_blank" rel="noopener"' : '';

    return [$url, $attr];
};

$fallbackCategories = [];
if (!$projectCategories) {
    foreach ($defaultProjects as $slug => $project) {
        $groupKey = strtolower($project['category']);
        if (!isset($fallbackCategories[$groupKey])) {
            $fallbackCategories[$groupKey] = [
                'slug'     => $slug,
                'label'    => $project['category'],
                'projects' => [],
            ];
        }
        $fallbackCategories[$groupKey]['projects'][] = $project;
    }
    $projectCategories = $fallbackCategories;
}

$projectCategories = array_values($projectCategories);

$projectTabs = array_map(
    static fn ($category) => [
        'slug'  => $category['slug'],
        'label' => $category['label'],
    ],
    $projectCategories
);
$initialCategory = $projectCategories[0] ?? null;
$initialProject = $initialCategory['projects'][0] ?? reset($defaultProjects);
?>
<!DOCTYPE html>
<html lang="en">
<!--HTML START-->

<head>
    <!--HEAD START-->
    <title>Kavishan</title>
    <!--::::: support meta :::::::-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!--::::: ALL CSS CALLING :::::::-->
    <link rel="stylesheet" href="assets/css/plugins/animate.min.css">
    <link rel="stylesheet" href="assets/css/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins/fontawesome.css">
    <link rel="stylesheet" href="assets/css/plugins/modal-video.min.css">
    <link rel="stylesheet" href="assets/css/plugins/stellarnav.css">
    <link rel="stylesheet" href="assets/css/plugins/owl.carousel.css">
    <link rel="stylesheet" href="assets/css/typography.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/button.css">
    <link rel="stylesheet" href="assets/css/inner.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

</head>
<!--HEADE END-->

<body><!--BODY START-->

    <!--PLACEHOLDER AREA START-->
    <div class="preloader">
        <div class="lds-dual-ring"></div>
    </div>
    <!--PLACEHOLDER AREA END-->

    <div class="site site-black"> <!--::::: SITE AREA START :::::::-->
        <!--::::: HEADER AREA START :::::::-->
        <div class="header-area header-main" id="header">
            <!--scroll to up btn-->
            <a href="#" class="up-btn"><i class="fal fa-angle-up"></i></a>
            <div class="container">
                <div class="row">
                    <div class="col-6 col-lg-3 align-self-center">
                        <a href="index.php" class="logo"><img src="<?= htmlspecialchars($headerLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan"></a>
                    </div>
                    <div class="col-6 text-center align-self-center">
                        <div class="main-menu">
                            <div class="stellarnav">
                                <ul class="navbarmneuclass">
                                    <li class="current"><a href="#home">Home</a></li>
                                    <li><a href="#about">About</a></li>
                                    <li><a href="#service">Services</a></li>
                                    <li><a href="#projects">Projects</a></li>
                                    <li><a href="#contact">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="d-none d-lg-block col-lg-3 align-self-center text-right">
                        <div class="search-area">
                            <div class="search-box">
                                <form action="#">
                                    <input type="search" placeholder="Search">
                                </form>
                                <div class="search-btn">
                                    <a href="#"><i class="fas fa-search"></i></a>
                                </div>
                            </div>
                            <div class="grid-menu" id="grid-side">
                                <img src="assets/img/icon/hamburger.svg" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slide-widgest-wrap" id="slide-widgest">
                <div class="side-widgest" id="side-content">
                    <div class="side-close" id="close-btn">
                    <i class="fal fa-times"></i>
                </div>
                    <div class="logo">
                        <a href="#"><img src="<?= htmlspecialchars($headerLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan"></a>
                    </div>
                    <div class="side-content">
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint ratione reprehenderit, error qui enim sit ex provident iure, dolor, nulla eaque delectus, repudiandae commodi. Velit assumenda odit quisquam at, error suscipit unde, necessitatibus ipsum ratione excepturi ducimus labore, totam dolorem.</p>
                    </div>
                    <div class="side-social">
                        <ul>
                            <?php [$facebookUrl, $facebookAttr] = $buildSocialLink($socialLinks, 'facebook'); ?>
                            <li><a href="<?= htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $facebookAttr; ?>><i class="fab fa-facebook-f"></i></a></li>
                            <?php [$twitterUrl, $twitterAttr] = $buildSocialLink($socialLinks, 'twitter'); ?>
                            <li><a href="<?= htmlspecialchars($twitterUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $twitterAttr; ?>><i class="fab fa-twitter"></i></a></li>
                            <?php [$linkedinUrl, $linkedinAttr] = $buildSocialLink($socialLinks, 'linkedin'); ?>
                            <li><a href="<?= htmlspecialchars($linkedinUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $linkedinAttr; ?>><i class="fab fa-linkedin-in"></i></a></li>
                            <?php [$instagramUrl, $instagramAttr] = $buildSocialLink($socialLinks, 'instagram'); ?>
                            <li><a href="<?= htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $instagramAttr; ?>><i class="fab fa-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--::::: HEADER AREA END :::::::-->
        
        <!--:::::WELCOME ATRA START :::::::-->
        <div class="welcome-area-wrap welcome__wrap1" id="home" style="--hero-padding: <?= max(120, min(420, (int) $heroPadding)); ?>px;">
            <div class="container">
                <div class="hero-card">
                    <div class="hero-profile">
                        <div class="hero-profile-ring">
                            <div class="hero-profile-img">
                                <img src="<?= htmlspecialchars($heroImagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan Anjula">
                            </div>
                        </div>
                    </div>
                    <div class="hero-content">
                        <span class="hero-chip"><i class="fal fa-bolt"></i> Available for freelance projects</span>
                        <h1>Hi, I'm <span>Kavishan Anjula</span> — <span class="highlight">Software Engineer</span></h1>
                        <div class="hero-roles">
                            <span class="hero-roles__label">Specialised in</span>
                            <span class="hero-typewriter" data-words='["Web Developer","UI/UX Designer","Mobile App Developer"]'></span>
                        </div>
                        <p>I craft immersive digital products across web, mobile, and admin ecosystems-marrying delightful motion with scalable engineering.</p>
                        <div class="hero-cta">
                            <a href="#projects" class="hero-btn primary">View My Work <i class="fal fa-arrow-right"></i></a>
                            <?php if ($cvDownloadPath): ?>
                                <a href="<?= htmlspecialchars($cvDownloadPath, ENT_QUOTES, 'UTF-8'); ?>" class="hero-btn ghost" download target="_blank" rel="noopener">Download CV <i class="fal fa-download"></i></a>
                            <?php else: ?>
                                <a href="#" class="hero-btn ghost is-disabled" aria-disabled="true">Download CV <i class="fal fa-download"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--:::::WELCOME AREA END :::::::-->

        <!--:::::SKILL AREA START :::::::-->
        <div class="skill-area section-padding" id="about">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 align-self-center">
                        <div class="heading white">
                            <strong class="filltext">toolbox</strong>
                            <small>Experience & Skills</small>
                            <h2>Platforms & <span>Frameworks</span></h2>
                        </div>
                    </div>
                    <div class="col-lg-6 align-self-center">
                        <div class="info-content">
                            <p>The right stack turns ambitious ideas into reliable products. Here’s where I move fastest—shipping secure, maintainable software across web and mobile.</p>
                        </div>
                    </div>
                </div>
                <div class="space-60"></div>
                <div class="skill-grid">
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--wordpress">
                            <i class="fab fa-wordpress-simple"></i>
                        </div>
                        <h5>WordPress</h5>
                        <p>Custom themes, Gutenberg blocks, performant headless builds, and marketing automation.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--laravel">
                            <i class="fab fa-laravel"></i>
                        </div>
                        <h5>Laravel</h5>
                        <p>API-first architectures, real-time dashboards, and integrations crafted with clean architecture.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--php">
                            <i class="fab fa-php"></i>
                        </div>
                        <h5>PHP</h5>
                        <p>Robust backends, modular microservices, and legacy modernisation tuned for high-traffic workloads.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--java">
                            <i class="fab fa-java"></i>
                        </div>
                        <h5>Java</h5>
                        <p>Enterprise-grade APIs, Spring Boot services, and cloud-native pipelines built for scale.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--react">
                            <i class="fab fa-react"></i>
                        </div>
                        <h5>React</h5>
                        <p>Interactive SPA dashboards, reusable component systems, and SSR for lightning fast UX.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--figma">
                            <i class="fab fa-figma"></i>
                        </div>
                        <h5>UI / UX</h5>
                        <p>Strategy-led product design, Figma component libraries, and collaborative prototyping workflows.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--mobile">
                            <i class="fal fa-mobile"></i>
                        </div>
                        <h5>Mobile Apps</h5>
                        <p>Cross-platform experiences with native polish, offline sync, and delightful micro-interactions.</p>
                    </div>
                    <div class="skill-box modern">
                        <div class="skill-icon skill-icon--bootstrap">
                            <i class="fab fa-bootstrap"></i>
                        </div>
                        <h5>Bootstrap</h5>
                        <p>Atomic component libraries and responsive design systems that accelerate product delivery.</p>
                    </div>
                </div>
            </div>
            <div class="space-100"></div>
            <!-- Start Technical Skills Section -->
            <div class="skill-metrics">
                <div class="container">
                    <div class="skill-metrics__grid">
                        <div class="metric-card" style="--progress:0.92;">
                            <div class="metric-circle">
                                <span>92%</span>
                            </div>
                            <h6>1️⃣ Laravel Development</h6>
                            <p>Building dynamic, scalable web applications with clean MVC architecture and robust backend APIs using Laravel. Expertise in authentication, RESTful APIs, and real-time data handling.</p>
                        </div>
                        <div class="metric-card" style="--progress:0.88;">
                            <div class="metric-circle">
                                <span>88%</span>
                            </div>
                            <h6>2️⃣ WordPress Customization</h6>
                            <p>Designing and developing custom WordPress themes and plugins with responsive layouts, SEO optimization, and user-friendly admin experiences.</p>
                        </div>
                        <div class="metric-card" style="--progress:0.86;">
                            <div class="metric-circle">
                                <span>86%</span>
                            </div>
                            <h6>3️⃣ React Frontend</h6>
                            <p>Developing high-performance user interfaces with React, integrating reusable components, API calls, and real-time state management for modern SPAs.</p>
                        </div>
                        <div class="metric-card" style="--progress:0.82;">
                            <div class="metric-circle">
                                <span>82%</span>
                            </div>
                            <h6>4️⃣ Bootstrap Framework</h6>
                            <p>Crafting visually stunning and responsive web layouts using Bootstrap with advanced grid systems, animations, and clean mobile-first design.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--:::::SKILL AREA END :::::::-->

        <!--:::::SERVICE AREA START :::::::-->
        <div class="service-area service-modern padding-bottom" id="service">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 align-self-center">
                        <div class="heading white">
                            <strong class="filltext">services</strong>
                            <small>WHAT I DELIVER</small>
                            <h2>Services & <span>Solutions</span></h2>
                        </div>
                    </div>
                    <div class="col-lg-6 align-self-center">
                         <div class="info-content">
                            <p>From concept to production, I guide teams through strategy, design, development, and launch—ensuring every release is performant, accessible, and maintainable.</p>
                        </div>
                    </div>
                </div>
                <div class="space-60"></div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="single-service">
                            <div class="service-icon">
                                <img src="assets/img/icon/service1.svg" alt="">
                            </div>
                            <div class="service-text">
                                <h4>UI / UX Design</h4>
                                <p>Designing seamless digital experiences through research-driven wireframes, interactive prototypes, and clean modern interfaces. Every design focuses on user satisfaction, accessibility, and brand consistency.</p>
                            </div>
                            <div class="circles-wrap">
                                <div class="circles">
                                    <span class="circle circle-1"></span>
                                    <span class="circle circle-2"></span>
                                    <span class="circle circle-3"></span>
                                    <span class="circle circle-4"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="single-service active">
                            <div class="service-icon">
                                <img src="assets/img/icon/service2.svg" alt="">
                            </div>
                            <div class="service-text">
                                <h4>💻 Frontend & Backend Development</h4>
                                <p>Building complete web systems from scratch using technologies like React, Laravel, and Node.js. I craft responsive, high-performance frontends and secure, scalable backends—ensuring smooth data flow and real-time functionality.</p>
                            </div>
                            <div class="circles-wrap">
                                <div class="circles">
                                    <span class="circle circle-1"></span>
                                    <span class="circle circle-2"></span>
                                    <span class="circle circle-3"></span>
                                    <span class="circle circle-4"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="single-service">
                            <div class="service-icon">
                                <img src="assets/img/icon/service3.svg" alt="">
                            </div>
                            <div class="service-text">
                                <h4>🧠 Machine Learning Modules (Python)</h4>
                                <p>Developing intelligent systems powered by Python-based ML frameworks such as TensorFlow and Scikit-learn. From predictive models to data analysis and automation, I bring AI-driven insight to web and mobile applications.</p>
                            </div>
                            <div class="circles-wrap">
                                <div class="circles">
                                    <span class="circle circle-1"></span>
                                    <span class="circle circle-2"></span>
                                    <span class="circle circle-3"></span>
                                    <span class="circle circle-4"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--:::::SERVICE AREA END :::::::-->

        <!--:::::PROJECT AREA START :::::::-->
        <div class="project-area section-padding" id="projects">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 align-self-center">
                        <div class="heading white">
                            <strong class="filltext">our projects</strong>
                            <small>WORKING PROCESS</small>
                            <h2>lastet working <span>project</span></h2>
                        </div>
                    </div>
                    <div class="col-lg-5 align-self-center">
                         <div class="info-content">
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint ratione reprehenderit </p>
                        </div>
                    </div>
                </div>
                <div class="space-60"></div>
                <div class="project-showcase">
                    <?php if ($dbError): ?>
                        <div class="alert alert-warning project-alert">
                            Unable to connect to the database. Showing demo projects. Error: <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="project-categories">
                        <?php foreach ($projectTabs as $index => $tab): ?>
                            <button
                                type="button"
                                class="project-tab<?= $index === 0 ? ' is-active' : ''; ?>"
                                data-project="<?= htmlspecialchars($tab['slug'], ENT_QUOTES, 'UTF-8'); ?>"
                                aria-pressed="<?= $index === 0 ? 'true' : 'false'; ?>"
                            >
                                <?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-board">
                        <div class="project-media">
                            <div class="project-media-frame">
                                <img
                                    id="projectImage"
                                    src="<?= htmlspecialchars($initialProject['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?= htmlspecialchars(($initialProject['title'] ?? 'Project') . ' preview', ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </div>
                        </div>
                        <div class="project-details">
                            <span class="project-tag" id="projectCategory"><?= htmlspecialchars($initialProject['category'] ?? 'Projects', ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3 id="projectTitle"><?= htmlspecialchars($initialProject['title'] ?? 'Project Coming Soon', ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p id="projectDescription"><?= htmlspecialchars($initialProject['description'] ?? 'Stay tuned for new additions!', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="project-stack" id="projectStack">
                                <?php foreach (($initialProject['stack'] ?? []) as $tech): ?>
                                    <span><?= htmlspecialchars($tech, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="project-links">
                                <?php
                                $initialLiveUrl = $initialProject['liveDemo'] ?? '';
                                $initialLiveLabel = $initialProject['liveLabel'] ?? 'Watch Live Demo';
                                $initialGithubUrl = $initialProject['github'] ?? '';
                                $initialGithubLabel = $initialProject['githubLabel'] ?? 'View Source';
                                ?>
                                <a
                                    id="projectLive"
                                    class="hero-btn primary<?= $initialLiveUrl ? '' : ' is-disabled'; ?>"
                                    href="<?= $initialLiveUrl ? htmlspecialchars($initialLiveUrl, ENT_QUOTES, 'UTF-8') : '#'; ?>"
                                    <?= $initialLiveUrl ? 'target="_blank" rel="noopener"' : 'aria-disabled="true"'; ?>
                                >
                                    <?= htmlspecialchars($initialLiveLabel, ENT_QUOTES, 'UTF-8'); ?> <i class="fal fa-play-circle"></i>
                                </a>
                                <a
                                    id="projectGithub"
                                    class="hero-btn ghost<?= $initialGithubUrl ? '' : ' is-disabled'; ?>"
                                    href="<?= $initialGithubUrl ? htmlspecialchars($initialGithubUrl, ENT_QUOTES, 'UTF-8') : '#'; ?>"
                                    <?= $initialGithubUrl ? 'target="_blank" rel="noopener"' : 'aria-disabled="true"'; ?>
                                >
                                    <?= htmlspecialchars($initialGithubLabel, ENT_QUOTES, 'UTF-8'); ?> <i class="fal fa-code-branch"></i>
                                </a>
                            </div>
                            <ul class="project-meta">
                                <li>
                                    <span>Primary Language</span>
                                    <strong id="projectLanguage"><?= htmlspecialchars($initialProject['language'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                                </li>
                                <li>
                                    <span>Focus</span>
                                    <strong id="projectFocus"><?= htmlspecialchars($initialProject['focus'] ?? 'In Progress', ENT_QUOTES, 'UTF-8'); ?></strong>
                                </li>
                            </ul>
                            <div class="project-list" id="projectList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--:::::PROJECT AREA END :::::::-->
        <!--:::::CONTACT AREA START :::::::-->
        <div class="contact-area section-padding" id="contact">
            <div class="container">
                <div class="contact-card">
                    <div class="contact-card__content">
                        <span class="hero-chip contact-chip"><i class="fal fa-comment-alt-dots"></i> Let's collaborate</span>
                        <h2>Let’s build your next <span>big idea</span></h2>
                        <p>Share your goals and I’ll help turn them into a resilient, user-loved product with clean code, thoughtful design, and reliable delivery.</p>
                        <div class="contact-actions">
                            <a href="mailto:hello@kavishan.dev" class="hero-btn primary">Send an Email <i class="fal fa-envelope"></i></a>
                            <a href="https://wa.me/94712345678" class="hero-btn ghost" target="_blank" rel="noopener">Message on WhatsApp <i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                    <div class="contact-card__details">
                        <div class="contact-detail">
                            <span class="detail-label">Email</span>
                            <a href="mailto:hello@kavishan.dev">hello@kavishan.dev</a>
                        </div>
                        <div class="contact-detail">
                            <span class="detail-label">Phone</span>
                            <a href="tel:+94712345678">+94 71 234 5678</a>
                        </div>
                        <div class="contact-detail">
                            <span class="detail-label">Location</span>
                            <p>Colombo, Sri Lanka</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--:::::CONTACT AREA END :::::::-->

        <!--:::::FOOTER AREA START :::::::-->
        <footer class="site-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <a href="#home" class="footer-logo">
                            <img src="<?= htmlspecialchars($footerLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Kavishan">
                        </a>
                        <p>Crafting polished digital experiences with scalable engineering, purposeful design, and dependable delivery.</p>
                        <div class="footer-social">
                            <?php [$facebookUrl, $facebookAttr] = $buildSocialLink($socialLinks, 'facebook'); ?>
                            <a href="<?= htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $facebookAttr; ?>><i class="fab fa-facebook-f"></i></a>
                            <?php [$twitterUrl, $twitterAttr] = $buildSocialLink($socialLinks, 'twitter'); ?>
                            <a href="<?= htmlspecialchars($twitterUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $twitterAttr; ?>><i class="fab fa-twitter"></i></a>
                            <?php [$linkedinUrl, $linkedinAttr] = $buildSocialLink($socialLinks, 'linkedin'); ?>
                            <a href="<?= htmlspecialchars($linkedinUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $linkedinAttr; ?>><i class="fab fa-linkedin-in"></i></a>
                            <?php [$instagramUrl, $instagramAttr] = $buildSocialLink($socialLinks, 'instagram'); ?>
                            <a href="<?= htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>"<?= $instagramAttr; ?>><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="footer-links">
                        <h5>Navigate</h5>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#service">Services</a></li>
                            <li><a href="#projects">Projects</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-links">
                        <h5>Let’s Connect</h5>
                        <ul>
                            <li><a href="mailto:hello@kavishan.dev">hello@kavishan.dev</a></li>
                            <li><a href="tel:+94712345678">+94 71 234 5678</a></li>
                            <li><span>Colombo, Sri Lanka</span></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2024 Kavishan Anjula. Built with dedication and curiosity.</p>
                </div>
            </div>
        </footer>
        <!--:::::FOOTER AREA END :::::::-->
    </div> <!--:::::sitea area end :::::::-->


    <!--:::::jquery 3.2.1 js :::::::-->
    <script src="assets/js/plugins/jQuery.2.1.0.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/plugins/jquery.nav.js"></script>
    <script src="assets/js/plugins/jquery.waypoints.min.js"></script>
    <script src="assets/js/plugins/jquery-modal-video.min.js"></script>
    <script src="assets/js/plugins/stellarnav.js"></script>
    <script src="assets/js/plugins/popper.min.js"></script>
    <script src="assets/js/plugins/owl.carousel.js"></script>
    <script src="assets/js/plugins/wow.min.js"></script>
    <script src="assets/js/plugins/easypiechart.min.js"></script>
    <script src="assets/js/plugins/animatenumber.min.js"></script>
    <script src="assets/js/plugins/circle-progress.js"></script>
    <script src="assets/js/plugins/bars.js"></script>
    <script src="assets/js/plugins/appear.js"></script>
    <script src="assets/js/plugins/jquery.hoverdir.js"></script>
    <script src="assets/js/plugins/isotop.v3.0.4.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var typeTarget = document.querySelector('.hero-typewriter');
            if (typeTarget) {
                var words = [];
                try {
                    words = JSON.parse(typeTarget.getAttribute('data-words'));
                } catch (e) {
                    words = typeTarget.getAttribute('data-words').split(',');
                }
                var wordIndex = 0;
                var charIndex = 0;
                var deleting = false;
                var typingSpeed = 110;
                var deletingSpeed = 55;
                var holdDelay = 1400;

                function type() {
                    var word = words[wordIndex] || "";
                    if (!deleting) {
                        charIndex++;
                        typeTarget.textContent = word.substring(0, charIndex);
                        if (charIndex === word.length) {
                            deleting = true;
                            setTimeout(type, holdDelay);
                            return;
                        }
                    } else {
                        charIndex--;
                        typeTarget.textContent = word.substring(0, charIndex);
                        if (charIndex === 0) {
                            deleting = false;
                            wordIndex = (wordIndex + 1) % words.length;
                        }
                    }
                    setTimeout(type, deleting ? deletingSpeed : typingSpeed);
                }

                type();
            }

            var projectCategories = <?= json_encode($projectCategories, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
            var categoryButtons = document.querySelectorAll('.project-categories .project-tab');
            var projectImage = document.getElementById('projectImage');
            var projectCategory = document.getElementById('projectCategory');
            var projectTitle = document.getElementById('projectTitle');
            var projectDescription = document.getElementById('projectDescription');
            var projectStack = document.getElementById('projectStack');
            var projectLive = document.getElementById('projectLive');
            var projectGithub = document.getElementById('projectGithub');
            var projectLanguage = document.getElementById('projectLanguage');
            var projectFocus = document.getElementById('projectFocus');
            var projectList = document.getElementById('projectList');
            var liveIcon = '<i class="fal fa-play-circle"></i>';
            var githubIcon = '<i class="fal fa-code-branch"></i>';
            var currentCategory = projectCategories[0] || null;
            var currentIndex = 0;

            function renderProject(project) {
                if (!project) {
                    projectImage.src = 'assets/img/project/project/project1.png';
                    projectImage.alt = 'Project preview';
                    projectCategory.textContent = 'Projects';
                    projectTitle.textContent = 'Project Coming Soon';
                    projectDescription.textContent = 'Stay tuned for new additions!';
                    projectStack.innerHTML = '';
                    projectLive.href = '#';
                    projectLive.classList.add('is-disabled');
                    projectLive.setAttribute('aria-disabled', 'true');
                    projectLive.innerHTML = 'Watch Live Demo ' + liveIcon;
                    projectGithub.href = '#';
                    projectGithub.classList.add('is-disabled');
                    projectGithub.setAttribute('aria-disabled', 'true');
                    projectGithub.innerHTML = 'View Source ' + githubIcon;
                    projectLanguage.textContent = 'N/A';
                    projectFocus.textContent = 'In Progress';
                    return;
                }
                projectImage.src = project.image || 'assets/img/project/project/project1.png';
                projectImage.alt = (project.title || 'Project') + ' preview';
                projectCategory.textContent = project.category || (currentCategory ? currentCategory.label : 'Projects');
                projectTitle.textContent = project.title || 'Project Coming Soon';
                projectDescription.textContent = project.description || 'Stay tuned for new additions!';
                var stack = Array.isArray(project.stack) ? project.stack : [];
                projectStack.innerHTML = stack.map(function (item) {
                    return '<span>' + item + '</span>';
                }).join('');
                if (project.liveDemo) {
                    projectLive.href = project.liveDemo;
                    projectLive.classList.remove('is-disabled');
                    projectLive.setAttribute('aria-disabled', 'false');
                } else {
                    projectLive.href = '#';
                    projectLive.classList.add('is-disabled');
                    projectLive.setAttribute('aria-disabled', 'true');
                }
                projectLive.innerHTML = (project.liveLabel || 'Watch Live Demo') + ' ' + liveIcon;

                if (project.github) {
                    projectGithub.href = project.github;
                    projectGithub.classList.remove('is-disabled');
                    projectGithub.setAttribute('aria-disabled', 'false');
                } else {
                    projectGithub.href = '#';
                    projectGithub.classList.add('is-disabled');
                    projectGithub.setAttribute('aria-disabled', 'true');
                }
                projectGithub.innerHTML = (project.githubLabel || 'View Source') + ' ' + githubIcon;
                projectLanguage.textContent = project.language || 'N/A';
                projectFocus.textContent = project.focus || 'In Progress';
            }

            function renderProjectList() {
                if (!projectList || !currentCategory) {
                    return;
                }
                projectList.innerHTML = currentCategory.projects.map(function (project, index) {
                    var activeClass = index === currentIndex ? ' is-active' : '';
                    return '<button type="button" class="project-list-item' + activeClass + '" data-index="' + index + '">' + project.title + '</button>';
                }).join('');
            }

            function setActiveCategoryButton(slug) {
                categoryButtons.forEach(function (btn) {
                    var isActive = btn.dataset.project === slug;
                    btn.classList.toggle('is-active', isActive);
                    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            }

            function activateCategoryBySlug(slug) {
                var category = projectCategories.find(function (item) {
                    return item.slug === slug;
                });
                if (!category) {
                    return;
                }
                currentCategory = category;
                currentIndex = 0;
                renderProject(currentCategory.projects[0]);
                renderProjectList();
                setActiveCategoryButton(slug);
            }

            if (projectList) {
                projectList.addEventListener('click', function (event) {
                    var button = event.target.closest('.project-list-item');
                    if (!button || !currentCategory) {
                        return;
                    }
                    var index = parseInt(button.getAttribute('data-index'), 10);
                    if (Number.isNaN(index) || !currentCategory.projects[index]) {
                        return;
                    }
                    currentIndex = index;
                    renderProject(currentCategory.projects[currentIndex]);
                    renderProjectList();
                });
            }

            categoryButtons.forEach(function (button, index) {
                button.addEventListener('click', function () {
                    if (this.classList.contains('is-active')) {
                        return;
                    }
                    activateCategoryBySlug(this.dataset.project);
                });
                if (index === 0 && !currentCategory) {
                    activateCategoryBySlug(button.dataset.project);
                }
            });

            if (currentCategory) {
                renderProject(currentCategory.projects[0]);
                renderProjectList();
                setActiveCategoryButton(currentCategory.slug);
            } else if (categoryButtons[0]) {
                activateCategoryBySlug(categoryButtons[0].dataset.project);
            }
        });
    </script>


</body>
<!--body end-->

</html>
<!--html end-->
