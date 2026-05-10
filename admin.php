<?php 
require_once 'backend/session.php';
require_once 'config/database.php';

if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$db = (new Database())->getConnection();

// Fetch counts
$total_users = 0;
$active_listings = 0;
$total_rentals = 0;
$open_reports = 0;

if ($db) {
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'available'");
    $active_listings = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM rentals");
    $total_rentals = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM reports WHERE status = 'open'");
    $open_reports = $stmt->fetchColumn();

    // Fetch listings (latest 10)
    $listings_query = "SELECT p.*, u.username FROM products p LEFT JOIN users u ON p.provider_id = u.id ORDER BY p.id DESC LIMIT 10";
    $listings_stmt = $db->query($listings_query);
    $listings = $listings_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending rentals
    $pending_rentals_query = "
        SELECT r.*, p.title, 
               u1.username as renter_name, 
               u2.username as provider_name 
        FROM rentals r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u1 ON r.user_id = u1.id 
        JOIN users u2 ON p.provider_id = u2.id 
        WHERE r.status = 'pending' 
        ORDER BY r.id DESC
    ";
    $pending_rentals_stmt = $db->query($pending_rentals_query);
    $pending_rentals = $pending_rentals_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch reports
    $reports_query = "SELECT * FROM reports WHERE status = 'open' ORDER BY created_at DESC LIMIT 5";
    $reports_stmt = $db->query($reports_query);
    $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Pending Providers
    $pending_providers_query = "SELECT id, username, email, created_at FROM users WHERE role = 'provider' AND approval_status = 'pending' ORDER BY id DESC";
    $pending_providers_stmt = $db->query($pending_providers_query);
    $pending_providers = $pending_providers_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin | RentIt</title>
  <link rel="stylesheet" href="assets/css/global.css" />
  <link rel="stylesheet" href="assets/css/admin.css" />
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

    <main>
      <section class="page-header">
        <div>
          <div class="soft-badge">Admin Control Center</div>
          <h1>Manage platform activity</h1>
          <p>Track users, listings, disputes, and approvals from one place.</p>
        </div>
      </section>

      <section class="stats-grid">
        <div class="stat-card">
          <span>Total Users</span>
          <strong class="counter" data-target="<?= $total_users ?>">0</strong>
        </div>
        <div class="stat-card">
          <span>Active Listings</span>
          <strong class="counter" data-target="<?= $active_listings ?>">0</strong>
        </div>
        <div class="stat-card">
          <span>Total Rentals</span>
          <strong class="counter" data-target="<?= $total_rentals ?>">0</strong>
        </div>
        <div class="stat-card">
          <span>Open Reports</span>
          <strong class="counter" data-target="<?= $open_reports ?>">0</strong>
        </div>
      </section>

      <section class="admin-layout">
        <div class="panel" style="grid-column: 1 / -1;">
          <div class="panel-header">
            <h2>Pending Provider Approvals</h2>
            <span class="badge"><?= count($pending_providers) ?></span>
          </div>

          <div class="table-list" id="pending-providers-list">
            <?php if (!empty($pending_providers)): ?>
              <?php foreach($pending_providers as $prov): ?>
                <div class="table-row user-row" data-user-id="<?= $prov['id'] ?>">
                  <div class="user-meta">
                    <h4><?= htmlspecialchars($prov['username']) ?></h4>
                    <p><?= htmlspecialchars($prov['email']) ?> • Registered on <?= date("d M Y", strtotime($prov['created_at'])) ?></p>
                  </div>
                  <div class="row-actions">
                    <button class="btn btn-primary btn-sm approve-user" data-id="<?= $prov['id'] ?>">Approve</button>
                    <button class="btn btn-danger btn-sm reject-user" data-id="<?= $prov['id'] ?>">Reject</button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color: var(--text-muted); padding: 1rem;">No providers waiting for approval.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="panel" style="grid-column: 1 / -1;">
          <div class="panel-header">
            <h2>Pending Rental Requests</h2>
          </div>

          <div class="table-list">
            <?php if (!empty($pending_rentals)): ?>
              <?php foreach($pending_rentals as $rental): 
                $start = date("d M Y", strtotime($rental['start_date']));
                $end = date("d M Y", strtotime($rental['end_date']));
              ?>
                <div class="table-row">
                  <div>
                    <h4><?= htmlspecialchars($rental['title']) ?></h4>
                    <p>Requested by <strong><?= htmlspecialchars($rental['renter_name']) ?></strong> from Provider <strong><?= htmlspecialchars($rental['provider_name']) ?></strong></p>
                    <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 5px;">📅 <?= $start ?> to <?= $end ?> (Total: Rs. <?= $rental['total_price'] ?>)</p>
                  </div>
                  <span class="status-pill warning">Waiting on Provider</span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No pending rental requests.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Listings Overview</h2>
            <a href="#">View all</a>
          </div>

          <div class="filter-group">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="pending">Pending</button>
            <button class="filter-btn" data-filter="available">Approved</button>
          </div>

          <div class="table-list" id="listings-container">
            <?php if (!empty($listings)): ?>
              <?php foreach($listings as $item): 
                $statusClass = $item['status'] == 'pending' ? 'warning' : ($item['status'] == 'available' ? 'success' : 'default');
              ?>
                <div class="table-row" data-status="<?= $item['status'] ?>">
                  <div>
                    <h4><?= htmlspecialchars($item['title']) ?></h4>
                    <p>Submitted by <?= htmlspecialchars($item['username'] ?? 'Unknown') ?> • <?= htmlspecialchars(ucfirst($item['category'])) ?></p>
                  </div>
                  <span class="status-pill <?= $statusClass ?>"><?= ucfirst($item['status']) ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No listings found.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Recent Reports</h2>
            <a href="#">View all</a>
          </div>

          <?php if (!empty($reports)): ?>
            <?php foreach($reports as $rep): ?>
              <div class="report-card">
                <button class="dismiss-report" title="Resolve Report">&times;</button>
                <h4><?= htmlspecialchars($rep['title']) ?></h4>
                <p><?= htmlspecialchars($rep['description']) ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No open reports.</p>
          <?php endif; ?>
        </div>
      </section>
    </main>

    <footer>
      <p>&copy; 2026 RentIt</p>
    </footer>
  </div>

  <div id="toast-container"></div>

  <script src="assets/js/admin.js"></script>
</body>
</html>