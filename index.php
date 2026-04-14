<?php
require_once __DIR__ . '/lib/auth.php';

$user = current_user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuanTask - Landing Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="landing-shell">
    <nav class="landing-nav glass-card">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-bolt text-info"></i>
            <strong>CuanTask</strong>
        </div>
        <div class="d-flex gap-2">
            <?php if ($user): ?>
                <a class="btn btn-gradient btn-sm" href="dashboard.php">Buka Dashboard</a>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
                <a class="btn btn-gradient btn-sm" href="register.php">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero glass-card mt-4">
        <div>
            <span class="badge text-bg-info mb-3">Platform Reward + Saldo DANA</span>
            <h1>Bangun web penghasil cuan untuk user, sambil monetisasi iklan kamu.</h1>
            <p>CuanTask siap dipakai sebagai MVP: landing page, sistem daftar + login, verifikasi email URL unik via SMTP, dan dashboard task bergaya aplikasi.</p>
            <div class="d-flex gap-2 flex-wrap mt-4">
                <a class="btn btn-gradient" href="register.php"><i class="fa-solid fa-rocket me-1"></i> Mulai Gratis</a>
                <a class="btn btn-outline-light" href="login.php">Saya sudah punya akun</a>
            </div>
        </div>
        <div class="hero-stats">
            <article class="glass-card p-3">
                <p>Task Completion</p>
                <h3>92%</h3>
            </article>
            <article class="glass-card p-3">
                <p>Payout Success</p>
                <h3>99.2%</h3>
            </article>
            <article class="glass-card p-3">
                <p>Pengguna Aktif</p>
                <h3>18.9K</h3>
            </article>
        </div>
    </section>

    <section class="features mt-4">
        <article class="glass-card p-3">
            <i class="fa-solid fa-envelope-circle-check mb-2"></i>
            <h5>Verifikasi Email Unik</h5>
            <p>Pendaftaran pakai email dan link verifikasi token unik.</p>
        </article>
        <article class="glass-card p-3">
            <i class="fa-solid fa-database mb-2"></i>
            <h5>MySQL (phpMyAdmin)</h5>
            <p>Struktur database siap import melalui phpMyAdmin.</p>
        </article>
        <article class="glass-card p-3">
            <i class="fa-solid fa-bell mb-2"></i>
            <h5>UI Alert Interaktif</h5>
            <p>Toast, modal, dan feedback aksi user real-time.</p>
        </article>
    </section>
</div>
</body>
</html>
