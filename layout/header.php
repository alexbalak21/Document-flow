<?php
// Expects $page_title to be set before including this file
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title ?? APP_NAME) ?> — <?= htmlspecialchars(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; }
    .navbar-brand img { max-height: 32px; }
    .sidebar { min-height: calc(100vh - 56px); border-right: 1px solid #dee2e6; background: #fff; }
    .sidebar .nav-link { color: #495057; border-radius: 6px; padding: .45rem .75rem; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #e8f0fe; color: #1a56db; }
    .sidebar .nav-link i { width: 18px; }
    .content-area { padding: 1.5rem 2rem; }
    @media print { .no-print { display: none !important; } }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/img/logo.png" alt="<?= htmlspecialchars(COMPANY['name']) ?>">
      <span class="fw-semibold small"><?= htmlspecialchars(APP_NAME) ?></span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 small"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container-fluid no-print">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 sidebar py-3 no-print">
      <ul class="nav flex-column gap-1">
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= ($active_nav ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/clients.php" class="nav-link <?= ($active_nav ?? '') === 'clients' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Clients
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/documents.php?type=quote" class="nav-link <?= ($active_nav ?? '') === 'quotes' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Quotes
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/documents.php?type=invoice" class="nav-link <?= ($active_nav ?? '') === 'invoices' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i> Invoices
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/documents.php" class="nav-link <?= ($active_nav ?? '') === 'documents' ? 'active' : '' ?>">
            <i class="bi bi-archive"></i> All documents
          </a>
        </li>
        <li class="nav-item mt-2">
          <a href="<?= BASE_URL ?>/products.php" class="nav-link <?= ($active_nav ?? '') === 'products' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> Products
          </a>
        </li>
      </ul>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 content-area">
