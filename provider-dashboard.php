<?php
require_once 'backend/session.php';
require_once 'config/database.php';

if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'provider') {
    // If Admin wants to view provider dashboard, maybe let them? 
    // The user said "for only admin and provider role". So allow admin too.
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'provider') {
        header("Location: explore.php");
        exit;
    }
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Fetch User Approval Status
$approval_status = 'approved';
if ($db) {
    $stmt = $db->prepare("SELECT approval_status FROM users WHERE id = :uid");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $approval_status = $stmt->fetchColumn() ?: 'approved';
    $_SESSION['approval_status'] = $approval_status;
}

// If provider is not approved, show pending screen
if ($_SESSION['role'] === 'provider' && $approval_status !== 'approved') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Pending Approval | RentIt</title>
      <link rel="stylesheet" href="assets/css/global.css">
      <link rel="stylesheet" href="assets/css/provider-dashboard.css">
      <script src="assets/js/theme.js"></script>
    </head>
    <body class="pending-approval-body">
      <div class="main-wrapper">
        <div class="nav-wrapper">
          <nav class="navbar">
            <a href="landing.php" class="logo">RentIt</a>
            <div class="nav-links">
                <a href="explore.php">Explore</a>
                <a href="backend/logout.php" class="btn btn-primary nav-btn">Logout</a>
                <button id="theme-toggle" class="theme-btn" aria-label="Toggle theme">🌙</button>
            </div>
          </nav>
        </div>
        <main class="pending-content">
          <div class="soft-box pending-card">
            <div class="status-icon-wrapper">
                <div class="status-pulse"></div>
                <span class="status-icon">⏳</span>
            </div>
            <h1>Account Under Review</h1>
            <p>Thanks for joining RentIt! Our team is currently reviewing your provider application. This usually takes 24-48 hours.</p>
            <div class="pending-steps">
                <div class="step-item completed">
                    <span class="step-dot"></span>
                    <p>Registration Complete</p>
                </div>
                <div class="step-item active">
                    <span class="step-dot"></span>
                    <p>Admin Verification</p>
                </div>
                <div class="step-item">
                    <span class="step-dot"></span>
                    <p>Start Listing Items</p>
                </div>
            </div>
            <a href="explore.php" class="btn btn-secondary">Browse as Renter</a>
          </div>
        </main>
      </div>
      <style>
        .pending-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 2rem;
        }
        .pending-card {
            max-width: 500px;
            text-align: center;
            padding: 3rem;
            animation: fadeInUp 0.6s ease-out;
        }
        .status-icon-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-light);
            border-radius: 50%;
            font-size: 2.5rem;
        }
        .status-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            animation: pulse 2s infinite;
        }
        .pending-steps {
            margin: 2.5rem 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            text-align: left;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            opacity: 0.5;
        }
        .step-item.completed { opacity: 0.8; color: var(--primary-color); }
        .step-item.active { opacity: 1; font-weight: 600; }
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--text-muted);
        }
        .completed .step-dot { background: var(--primary-color); }
        .active .step-dot { background: var(--primary-color); box-shadow: 0 0 10px var(--primary-color); }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
      </style>
    </body>
    </html>
    <?php
    exit;
}

// Fetch Stats
$monthly_earnings = 0;
$active_bookings = 0;
$listed_items = 0;

if ($db) {
    // Earnings (sum of total_price where status is accepted)
    $stmt = $db->prepare("SELECT SUM(total_price) FROM rentals r JOIN products p ON r.product_id = p.id WHERE p.provider_id = :uid AND r.status = 'accepted'");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $monthly_earnings = $stmt->fetchColumn() ?: 0;

    // Active bookings
    $stmt = $db->prepare("SELECT COUNT(*) FROM rentals r JOIN products p ON r.product_id = p.id WHERE p.provider_id = :uid AND r.status = 'accepted'");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $active_bookings = $stmt->fetchColumn() ?: 0;

    // Listed items
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE provider_id = :uid");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $listed_items = $stmt->fetchColumn() ?: 0;

    // Fetch Upcoming Bookings (Rentals)
    $rentals_query = "
        SELECT r.*, p.title, u.username as renter_name 
        FROM rentals r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u ON r.user_id = u.id 
        WHERE p.provider_id = :uid 
        ORDER BY r.start_date ASC
    ";
    $rentals_stmt = $db->prepare($rentals_query);
    $rentals_stmt->bindParam(':uid', $user_id);
    $rentals_stmt->execute();
    $rentals = $rentals_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Provider's Listings
    $listings_stmt = $db->prepare("SELECT * FROM products WHERE provider_id = :uid ORDER BY id DESC");
    $listings_stmt->bindParam(':uid', $user_id);
    $listings_stmt->execute();
    $listings = $listings_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Provider Dashboard | RentIt</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="assets/css/provider-dashboard.css">
  <script src="assets/js/theme.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

    <main>
      <section class="page-header">
        <div>
          <div class="soft-badge">Service Provider Dashboard</div>
          <h1>Run your rental business</h1>
          <p>Monitor bookings, earnings, availability, and customer requests.</p>
        </div>
        <div class="header-actions">
          <a href="#" class="btn btn-primary" id="add-item-btn">Add New Item</a>
        </div>
      </section>

      <section class="stats-grid">
        <div class="stat-card">
          <span>Total Earnings</span>
          <strong class="counter currency" data-target="<?= $monthly_earnings ?>">Rs. 0</strong>
        </div>
        <div class="stat-card">
          <span>Active Bookings</span>
          <strong class="counter" data-target="<?= $active_bookings ?>">0</strong>
        </div>
        <div class="stat-card">
          <span>Listed Items</span>
          <strong class="counter" data-target="<?= $listed_items ?>">0</strong>
        </div>
        <div class="stat-card">
          <span>Rating</span>
          <strong><span class="counter float" data-target="4.8">0</span>/5</strong>
        </div>
      </section>

      <section class="provider-layout">
        <div class="panel">
          <div class="panel-header">
            <h2>Rental Requests & Bookings</h2>
          </div>

          <?php if (!empty($rentals)): ?>
            <?php foreach($rentals as $rental): 
              $start = date("d M", strtotime($rental['start_date']));
              $end = date("d M", strtotime($rental['end_date']));
              $date_str = "$start - $end";
            ?>
              <div class="booking-card" data-rental-id="<?= $rental['id'] ?>">
                <div class="booking-info">
                  <h4><?= htmlspecialchars($rental['title']) ?></h4>
                  <p>Requested by <strong><?= htmlspecialchars($rental['renter_name']) ?></strong> • <?= $date_str ?></p>
                  
                  <?php if ($rental['status'] === 'pending'): ?>
                    <div class="booking-actions">
                      <button class="action-btn accept-btn" onclick="updateRental(<?= $rental['id'] ?>, 'accepted')">Accept</button>
                      <button class="action-btn decline-btn" onclick="updateRental(<?= $rental['id'] ?>, 'rejected')">Decline</button>
                    </div>
                  <?php endif; ?>
                </div>
                
                <?php if ($rental['status'] === 'accepted'): ?>
                  <span class="status-pill success">Confirmed</span>
                <?php elseif ($rental['status'] === 'rejected'): ?>
                  <span class="status-pill warning">Declined</span>
                <?php else: ?>
                  <span class="status-pill warning pending-pill">Pending</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No rentals or requests yet.</p>
          <?php endif; ?>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Your Listings</h2>
          </div>

          <?php if (!empty($listings)): ?>
            <?php foreach($listings as $item): ?>
              <div class="listing-item">
                <?php if(!empty($item['image_path'])): ?>
                    <img src="<?= $item['image_path'] ?>" class="listing-img">
                <?php endif; ?>
                <div class="listing-details">
                  <h4><?= htmlspecialchars($item['title']) ?></h4>
                  <p>Rs. <?= $item['price_per_day'] ?>/day • <?= htmlspecialchars(ucfirst($item['category'])) ?></p>
                </div>
                <div class="listing-controls">
                  <button class="btn btn-secondary btn-sm" onclick='openEditModal(<?= json_encode($item) ?>)'>Edit</button>
                  <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?= $item['id'] ?>)">Delete</button>
                  <label class="switch" title="Toggle visibility">
                    <input type="checkbox" checked class="visibility-toggle" data-item="<?= $item['id'] ?>">
                    <span class="slider round"></span>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>You haven't listed any items yet.</p>
          <?php endif; ?>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Availability</h2>
          </div>
          <div class="calendar-box interactive-calendar">
            <p class="cal-instructions">Click a day to block/unblock rentals.</p>
            <div class="mini-cal-grid" id="availability-grid">
              </div>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 RentIt</p>
    </footer>
  </div>

  <div id="toast-container"></div>

  <!-- Add Item Modal -->
  <div id="add-item-modal" class="modal">
    <div class="modal-content soft-box">
        <h2>Add New Item</h2>
        <form id="add-item-form" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" name="title" class="form-control" placeholder="Item Title (e.g. Sony Camera)" required>
            </div>
            <div class="input-group">
                <textarea name="description" class="form-control" placeholder="Item Description" required></textarea>
            </div>
            <div class="input-group">
                <input type="number" name="price_per_day" class="form-control" placeholder="Price per day (Rs.)" required>
            </div>
            <div class="input-group">
                <label class="input-label">Upload Product Photo</label>
                <input type="file" name="product_image" class="form-control" accept="image/*" required>
            </div>
            <div class="input-group">
                <select name="category" class="form-control select-control" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="electronics">Electronics</option>
                    <option value="tools">Tools & Machinery</option>
                    <option value="events">Events & Party</option>
                    <option value="costumes">Costumes & Luxury</option>
                    <option value="transport">Transportation</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Add Item</button>
                <button type="button" id="close-modal-btn" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
  </div>

  <!-- Edit Item Modal -->
  <div id="edit-item-modal" class="modal">
    <div class="modal-content soft-box">
        <h2>Edit Item</h2>
        <form id="edit-item-form" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="edit-id">
            <div class="input-group">
                <input type="text" name="title" id="edit-title" class="form-control" placeholder="Item Title" required>
            </div>
            <div class="input-group">
                <textarea name="description" id="edit-desc" class="form-control" placeholder="Item Description" required></textarea>
            </div>
            <div class="input-group">
                <input type="number" name="price_per_day" id="edit-price" class="form-control" placeholder="Price per day (Rs.)" required>
            </div>
            <div class="input-group">
                <label class="input-label">Change Photo (optional)</label>
                <input type="file" name="product_image" class="form-control" accept="image/*">
            </div>
            <div class="input-group">
                <select name="category" id="edit-cat" class="form-control select-control" required>
                    <option value="electronics">Electronics</option>
                    <option value="tools">Tools & Machinery</option>
                    <option value="events">Events & Party</option>
                    <option value="costumes">Costumes & Luxury</option>
                    <option value="transport">Transportation</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" id="close-edit-modal-btn" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
  </div>

  <script src="assets/js/provider.js"></script>
  <script>
    function updateRental(rentalId, status) {
        $.ajax({
            url: 'backend/update_rental_status.php',
            type: 'POST',
            data: { rental_id: rentalId, status: status },
            success: function(res) {
                if (res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                }
            }
        });
    }

    function deleteProduct(id) {
        if (confirm("Are you sure you want to delete this listing permanently? This will also decline any pending rental requests.")) {
            $.ajax({
                url: 'backend/delete_product.php',
                type: 'POST',
                data: { product_id: id },
                success: function(res) {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    }

    function openEditModal(product) {
        $('#edit-id').val(product.id);
        $('#edit-title').val(product.title);
        $('#edit-desc').val(product.description);
        $('#edit-price').val(product.price_per_day);
        $('#edit-cat').val(product.category);
        $('#edit-item-modal').css('display', 'flex').addClass('show');
    }

    $(document).ready(function() {
        $('#add-item-btn').on('click', function(e) {
            e.preventDefault();
            $('#add-item-modal').css('display', 'flex').addClass('show');
        });
        
        $('#close-modal-btn').on('click', function() {
            $('#add-item-modal').removeClass('show');
            setTimeout(() => $('#add-item-modal').hide(), 300);
        });

        $('#close-edit-modal-btn').on('click', function() {
            $('#edit-item-modal').removeClass('show');
            setTimeout(() => $('#edit-item-modal').hide(), 300);
        });

        $('#add-item-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: 'backend/add_product.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

        $('#edit-item-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: 'backend/update_product.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                }
            });
        });
    });
  </script>
</body>
</html>