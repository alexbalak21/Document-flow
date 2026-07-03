<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → go to dashboard
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? BASE_URL . '/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT id, email, password_hash, full_name FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name']  = $user['full_name'];
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — <?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; }
    .login-card { max-width: 400px; margin: 100px auto; }
    .brand-logo { max-height: 48px; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="text-center mb-4">
    <img src="<?= BASE_URL ?>/img/logo.png" alt="<?= htmlspecialchars(COMPANY['name']) ?>" class="brand-logo mb-3">
    <h4 class="fw-semibold"><?= htmlspecialchars(APP_NAME) ?></h4>
    <p class="text-muted small">Sign in to continue</p>
  </div>
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="mb-3">
          <label for="email" class="form-label fw-medium">Email</label>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label fw-medium">Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right me-1"></i>Sign in
        </button>
      </form>
    </div>
  </div>
  <p class="text-center text-muted small mt-3"><?= htmlspecialchars(COMPANY['name']) ?></p>
</div>
</body>
</html>
