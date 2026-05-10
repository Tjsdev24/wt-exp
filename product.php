<?php include 'backend/session.php'; ?>
<?php
require_once 'config/database.php';
$product_id = $_GET['id'] ?? 1; // default to 1 if not set
$db = (new Database())->getConnection();
$product = null;

if ($db) {
    $stmt = $db->prepare('SELECT p.*, u.username as provider_name FROM products p LEFT JOIN users u ON p.provider_id = u.id WHERE p.id = :id');
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch rental count for this product
    $rental_stmt = $db->prepare('SELECT COUNT(*) FROM rentals WHERE product_id = :id AND status = \'accepted\'');
    $rental_stmt->bindParam(':id', $product_id);
    $rental_stmt->execute();
    $rental_count = $rental_stmt->fetchColumn() ?: 0;
}

if (!$product) {
    die('Product not found.');
}

$cat = $product['category'];
$img = 'https://images.unsplash.com/photo-1504280516766-981882cd4b3b?w=1000&fit=crop';
if ($cat == 'electronics') $img = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=1000&fit=crop';
if ($cat == 'tools') $img = 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1000&fit=crop';
if ($cat == 'transport') $img = 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=1000&fit=crop';

// Override with uploaded image if exists
if (!empty($product['image_path'])) {
    $img = $product['image_path'];
}

$title = htmlspecialchars($product['title']);
$desc = htmlspecialchars($product['description'] ?? 'No description available.');
$price = $product['price_per_day'];
$provider = htmlspecialchars($product['provider_name'] ?? 'Provider');
$provider_initial = strtoupper(substr($provider, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | RentIt</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/product.css?v=<?= time() ?>">
    <style>
        .price {
            display: flex !important;
            flex-direction: row !important;
            align-items: baseline !important;
            gap: 0.5rem !important;
            margin-bottom: 2rem !important;
            background: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            color: #0f172a !important;
        }
        .price h2 {
            font-size: 2rem !important;
            color: #0f172a !important;
            line-height: 1 !important;
            font-weight: 900 !important;
            display: flex !important;
            align-items: baseline !important;
            gap: 0.3rem !important;
            margin: 0 !important;
        }
        .currency-symbol {
            font-size: 2rem !important;
            font-weight: 900 !important;
            color: #0f172a !important;
        }
        .price > span {
            color: #0f172a !important; 
            font-weight: 900 !important;
            font-size: 2rem !important;
            margin-left: 0 !important;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/theme.js"></script>
</head>
<body>
    <div class="main-wrapper">
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
            </nav>
        </div>

        <main class="product-layout">
            <div class="gallery">
                <a href="explore.php" class="breadcrumb">← Back to Explore</a>
                
                <img src="<?= $img ?>" id="main-image" class="main-img" alt="<?= $title ?>">
                
                <div class="thumbnails">
                    <img src="<?= $img ?>" class="thumb-img active" data-large="<?= $img ?>" alt="Thumbnail 1">
                </div>
            </div>

            <div class="details">
                <div class="title-area">
                    <h1 id="product-title" data-id="<?= $product_id ?>"><?= $title ?></h1>
                    <p class="rating"> ⭐ 4.9 <span style="font-size: 0.9rem;">(<?= $rental_count ?> rentals)</span></p>
                </div>

                <div class="owner-pill">
                    <div class="avatar"><?= $provider_initial ?></div>
                    <div class="owner-text">
                        <strong><?= $provider ?></strong>
                        <span>Usually responds in 1 hour</span>
                    </div>
                </div>

                <div class="description">
                    <p><?= $desc ?></p>
                </div>

                <div class="floating-widget">
                    <div class="price">
                        <h2><span class="currency-symbol">Rs.</span> <span id="daily-rate"><?= number_format($price, 2) ?></span></h2><span>/day</span>
                    </div>
                    
                    <div class="date-pickers">
                        <input type="date" id="start-date" class="form-control" title="Start Date" required aria-label="Start Date">
                        <input type="date" id="end-date" class="form-control" title="End Date" required aria-label="End Date">
                    </div>

                    <div class="total-breakdown" style="display: none;">
                        <div class="breakdown-row">
                            <span id="days-calc">Rs. 45 x 1 day</span>
                            <span id="subtotal-calc">Rs. 45</span>
                        </div>
                        <div class="breakdown-row">
                            <span>Service Fee</span>
                            <span>Rs. 5</span>
                        </div>
                        <hr class="divider">
                        <div class="breakdown-row total-row">
                            <span>Total</span>
                            <span id="total-calc">Rs. 50</span>
                        </div>
                    </div>

                    <button id="book-btn" class="btn btn-primary btn-block">Request to Book</button>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 RentIt</p>
        </footer>
    </div>

    <div id="toast-container"></div>

    <script src="assets/js/product.js"></script>
</body>
</html>