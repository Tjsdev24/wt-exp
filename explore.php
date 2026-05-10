<?php include 'backend/session.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore | RentIt</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/explore.css">
    <link rel="stylesheet" href="assets/css/explore-features.css">
    <script src="assets/js/theme.js"></script>
</head>
<body>

    <!-- Toast Container (Feature 12) -->
    <div id="toast-container"></div>

    <!-- Mobile Menu (Feature 11) -->
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

    <div class="main-wrapper">

        <!-- NAV: sticky (4) · dark mode (5) · hamburger (11) · wishlist badge (6) -->
        <div class="nav-wrapper">
            <nav class="navbar">
                <a href="landing.php" class="logo">RentIt</a>

                <!-- Desktop -->
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

                <!-- Mobile -->
                <div class="hamburger-area">
                    <button class="theme-btn" aria-label="Toggle theme"
                            onclick="document.getElementById('theme-toggle').click()">🌙</button>
                    <button id="hamburger" aria-label="Open menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </nav>
        </div>

        <main>
            <!-- Search (Feature 1) -->
            <div class="explore-header">
                <div class="search-pill">
                    <input type="text" placeholder="Search for cameras, tools, or bikes..." aria-label="Search items">
                    <button class="search-btn" aria-label="Search">🔍</button>
                </div>
            </div>

            <div class="explore-layout">

                <!-- SIDEBAR: category checkboxes (2) · price slider (8) -->
                <aside class="sidebar">
                    <div class="soft-box">
                        <h3>Categories</h3>
                        <label class="check-item">
                            <input type="checkbox" data-filter="all" checked> All Items
                        </label>
                        <label class="check-item">
                            <input type="checkbox" data-filter="electronics"> Electronics
                        </label>
                        <label class="check-item">
                            <input type="checkbox" data-filter="tools"> Tools &amp; Machinery
                        </label>
                        <label class="check-item">
                            <input type="checkbox" data-filter="events"> Events &amp; Party
                        </label>
                        <label class="check-item">
                            <input type="checkbox" data-filter="costumes"> Costumes &amp; Luxury
                        </label>
                        <label class="check-item">
                            <input type="checkbox" data-filter="transport"> Transportation
                        </label>

                        <!-- Price Range (Feature 8) -->
                        <div class="price-range-wrap">
                            <label>
                                <span>Max Daily Price</span>
                                <strong id="price-label">Rs. 10000/d</strong>
                            </label>
                            <input type="range" id="price-range" min="50" max="10000" value="10000" step="50">
                        </div>
                    </div>
                </aside>

                <!-- RESULTS: topbar + filter tags + grid -->
                <div class="results">

                    <!-- Results topbar: count (Feature 1) · sort (Feature 3) -->
                    <div class="results-topbar">
                        <span id="result-count">6 items found</span>
                        <select id="sort-select" aria-label="Sort by">
                            <option value="default">Sort: Default</option>
                            <option value="price-asc">Price: Low → High</option>
                            <option value="price-desc">Price: High → Low</option>
                            <option value="name">Name: A → Z</option>
                        </select>
                    </div>

                    <!-- Active Filter Tags (Feature 13) -->
                    <div id="active-tags"></div>

                    <!-- Grid: data-* attrs drive all filtering/sorting -->
                    <div class="grid">
                        <?php
                        require_once 'config/database.php';
                        $db = (new Database())->getConnection();
                        if ($db) {
                            $stmt = $db->query("SELECT p.*, u.username as provider_name FROM products p LEFT JOIN users u ON p.provider_id = u.id WHERE p.status = 'available'");
                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($products as $p) {
                                $id = 'p' . $p['id'];
                                $name = htmlspecialchars($p['title']);
                                $cat = htmlspecialchars($p['category']);
                                $price = $p['price_per_day'];
                                $provider_name = htmlspecialchars($p['provider_name'] ?? 'Unknown');
                                
                                // Default images based on category
                                $img = 'https://images.unsplash.com/photo-1504280516766-981882cd4b3b?w=500&fit=crop';
                                if ($cat == 'electronics') $img = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=500&fit=crop';
                                if ($cat == 'tools') $img = 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=500&fit=crop';
                                if ($cat == 'transport') $img = 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=500&fit=crop';
                                
                                // Override with uploaded image if exists
                                if (!empty($p['image_path'])) {
                                    $img = $p['image_path'];
                                }
                                
                                echo '
                                <a href="product.php?id='.$p['id'].'" class="soft-product-card"
                                   data-id="'.$id.'" data-name="'.$name.'"
                                   data-category="'.$cat.'" data-price="'.$price.'">
                                    <div class="card-img-wrap">
                                        <img src="'.$img.'" alt="'.$name.'">
                                        <div class="price-bubble">Rs. '.$price.'/d</div>
                                    </div>
                                    <div class="card-info">
                                        <h4>'.$name.'</h4>
                                        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 4px;">👤 By '.$provider_name.'</p>
                                    </div>
                                </a>';
                            }
                        }
                        ?>
                    </div><!-- /.grid -->
                </div><!-- /.results -->

            </div><!-- /.explore-layout -->
        </main>

        <footer>
            <p>&copy; 2026 RentIt</p>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="assets/js/explore.js?v=1.3"></script>
</body>
</html>