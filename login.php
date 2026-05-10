<?php include 'backend/session.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In | RentIt</title>
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
              <h2>Welcome back</h2>
              <p>Sign in to continue your journey.</p>
            </div>

            <form id="loginForm">
              <div
                id="loginStatus"
                style="
                  display: none;
                  margin-bottom: 15px;
                  color: #ff003c;
                  text-shadow: 0 0 5px #ff003c;
                "
              ></div>

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
                <input
                  type="password"
                  name="password"
                  class="form-control"
                  placeholder="Password"
                  required
                  aria-label="Password"
                />
              </div>
              <button type="submit" class="btn btn-primary btn-block">
                Sign In
              </button>
            </form>

            <div class="auth-footer">
              <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
          </div>
        </div>
      </main>

      <footer>
        <p>&copy; 2026 RentIt</p>
      </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/auth.js?v=1.2"></script>
  </body>
</html>
