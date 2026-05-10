<?php include 'backend/session.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentIt | Smooth Renting</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="assets/css/landing-features.css">
    <script src="assets/js/theme.js"></script>
</head>
<body>

    <!-- Toast Container (Feature 9) -->
    <div id="toast-container"></div>

    <!-- Back to Top (Feature 7) -->
    <button id="back-to-top" aria-label="Back to top">↑</button>

    <div class="main-wrapper">

        <!-- NAV: sticky shrink (1) · dark toggle (6) · hamburger (11) -->
        <div class="nav-wrapper">
            <nav class="navbar">
                <a href="landing.php" class="logo">RentIt</a>

                <div class="nav-links">
                    <a href="explore.php">Explore</a>
                    <?php if(isLoggedIn()): ?>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php">Admin Panel</a>
                        <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'provider'): ?>
                            <a href="provider-dashboard.php">Provider Dashboard</a>
                        <?php endif; ?>
                        <a href="backend/logout.php" class="btn btn-primary nav-btn">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Sign In</a>
                        <a href="signup.php" class="btn btn-primary nav-btn">Get Started</a>
                    <?php endif; ?>
                    <button id="theme-toggle" class="theme-btn" aria-label="Toggle theme">🌙</button>
                </div>

                <div class="hamburger-area">
                    <button class="theme-btn" aria-label="Toggle theme"
                            onclick="document.getElementById('theme-toggle').click()">🌙</button>
                    <button id="hamburger" aria-label="Open menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Mobile drawer (Feature 11) -->
        <div id="mobile-menu">
        <a href="landing.php">Home</a>
        <a href="explore.php">Explore</a>
        <?php if(isLoggedIn()): ?>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'provider'): ?>
                <a href="provider-dashboard.php">Provider Dashboard</a>
            <?php endif; ?>
            <a href="backend/logout.php" class="btn btn-primary">Logout</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
            <a href="signup.php" class="btn btn-primary">Get Started</a>
        <?php endif; ?>
    </div>

        <main>

            <!-- HERO: typewriter (2) · search (5) · parallax (10) -->
            <header class="hero">
                <div class="hero-content reveal">
                    <div class="soft-badge">Effortless sharing</div>
                    <h1>Rent <span id="typewriter-word" class="typewriter-word">anything.</span><br>Smoothly.</h1>
                    <p>Skip the clutter. Access high-quality gear, tools, and experiences directly from your neighbors with zero friction.</p>

                    <!-- Search bar (Feature 5) -->
                    <div class="hero-search-bar">
                        <input id="hero-search" type="text"
                               placeholder='Try "camera", "bike", "tent"…' autocomplete="off" />
                        <button id="hero-search-btn">Search</button>
                    </div>

                    <div class="hero-btns">
                        <a href="explore.php" class="btn btn-primary">Browse Items</a>
                        <a href="login.php" class="btn btn-secondary">List Yours</a>
                    </div>
                </div>

                <div class="hero-visual reveal">
                    <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&fit=crop"
                         alt="Camera Gear laid out on a table">
                </div>
            </header>

            <!-- STATS: counter animation (Feature 8) -->
            <section id="stats-section" class="stats-section reveal">
                <div class="stat-item">
                    <span class="stat-number" data-target="12400">0</span>
                    <span class="stat-label">Items Listed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-target="8300">0</span>
                    <span class="stat-label">Happy Renters</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-target="97">0</span>
                    <span class="stat-label">% Satisfaction</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-target="340">0</span>
                    <span class="stat-label">Cities Covered</span>
                </div>
            </section>

            <!-- CATEGORIES: scroll reveal (3) · ripple (4) · hash routing (12) -->
            <section class="section">
                <div class="section-header reveal">
                    <h2>Explore Categories</h2>
                </div>
                <div class="category-grid">
                    <a href="explore.php" class="cat-card reveal" data-category="electronics">
                        <div class="icon-wrapper bg-blue">📸</div>
                        <h3>Electronics</h3>
                        <p>Cameras &amp; Gear</p>
                    </a>
                    <a href="explore.php" class="cat-card reveal" data-category="mobility">
                        <div class="icon-wrapper bg-green">🚲</div>
                        <h3>Mobility</h3>
                        <p>Bikes &amp; Scooters</p>
                    </a>
                    <a href="explore.php" class="cat-card reveal" data-category="tools">
                        <div class="icon-wrapper bg-orange">🔨</div>
                        <h3>Tools</h3>
                        <p>Drills &amp; Saws</p>
                    </a>
                    <a href="explore.php" class="cat-card reveal" data-category="outdoors">
                        <div class="icon-wrapper bg-purple">⛺</div>
                        <h3>Outdoors</h3>
                        <p>Tents &amp; Kayaks</p>
                    </a>
                </div>
            </section>

        </main>

        <footer>
            <p>&copy; 2026 RentIt</p>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="assets/js/landing.js"></script>
</body>
</html>