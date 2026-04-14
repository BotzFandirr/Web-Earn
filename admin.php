<?php
require_once __DIR__ . '/lib/auth.php';

$admin = require_admin();

$pdo = db();
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalVerified = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();
$totalAdmins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

$listStmt = $pdo->prepare('SELECT name, email, role, is_verified, created_at FROM users ORDER BY id DESC LIMIT 20');
$listStmt->execute();
$users = $listStmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CuanTask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <header class="topbar glass-card mb-3">
        <div>
            <p class="label mb-1">Admin Area</p>
            <h1 class="mb-0">Halo, <?= htmlspecialchars($admin['name']) ?></h1>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-soft" href="dashboard.php">Dashboard</a>
            <a class="btn btn-light" href="logout.php">Logout</a>
        </div>
    </header>

    <section class="stats-grid mb-3">
        <article class="stat-card glass-card"><p>Total User</p><h3><?= number_format($totalUsers, 0, ',', '.') ?></h3></article>
        <article class="stat-card glass-card"><p>User Terverifikasi</p><h3><?= number_format($totalVerified, 0, ',', '.') ?></h3></article>
        <article class="stat-card glass-card"><p>Total Admin</p><h3><?= number_format($totalAdmins, 0, ',', '.') ?></h3></article>
    </section>

    <section class="glass-card p-3 table-wrap">
        <h4 class="mb-3">20 Akun Terbaru</h4>
        <div class="table-responsive">
            <table class="table table-dark table-borderless align-middle mb-0">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Verifikasi</th>
                    <th>Dibuat</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><span class="badge <?= $row['role'] === 'admin' ? 'text-bg-warning' : 'text-bg-info' ?>"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td><?= ((int) $row['is_verified'] === 1) ? 'Ya' : 'Belum' ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>
