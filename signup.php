<?php include 'backend/session.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up | RentIt</title>
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/auth.css" />
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

      <main class="auth-main">
        <div class="auth-wrapper">
          <div class="soft-auth-card">
            <div class="auth-header">
              <h2>Create Account</h2>
              <p>Start renting effortlessly today.</p>
            </div>

            <form id="signupForm">
              <div
                id="signupStatus"
                style="
                  display: none;
                  margin-bottom: 15px;
                  color: #00ffaa;
                  text-shadow: 0 0 5px #00ffaa;
                "
              ></div>

              <div class="input-group">
                <input
                  type="text"
                  name="fullname"
                  class="form-control"
                  placeholder="Full Name"
                  required
                  aria-label="Full Name"
                />
              </div>
              <div class="input-group">
                <input
                  type="email"
                  name="email"
                  class="form-control"
                  placeholder="Email address"
                  required
                  aria-label="Email address"
                />
              </div>
              <div class="input-group">
                <select name="role" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: transparent; color: var(--text);">
                  <option value="" disabled selected>Select Role</option>
                  <option value="renter" style="color: #000;">Renter</option>
                  <option value="provider" style="color: #000;">Provider</option>
                  <option value="admin" style="color: #000;">Admin</option>
                </select>
              </div>
              <div class="input-group">
                <input
                  type="password"
                  name="password"
                  class="form-control"
                  placeholder="Create Password"
                  required
                  aria-label="Create Password"
                />
              </div>
              <button type="submit" class="btn btn-primary btn-block">
                Sign Up
              </button>
            </form>

            <div class="auth-footer">
              <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
          </div>
        </div>
      </main>

      <footer>
        <p>&copy; 2026 RentIt</p>
      </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/auth.js?v=1.1"></script>
  </body>
</html>
