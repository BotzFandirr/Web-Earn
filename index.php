<?php
$tasks = [
    [
        'id' => 1,
        'title' => 'Check-in Harian',
        'desc' => 'Masuk setiap hari untuk klaim bonus konsisten.',
        'reward' => 150,
        'type' => 'daily',
        'icon' => 'fa-calendar-check'
    ],
    [
        'id' => 2,
        'title' => 'Baca Artikel Edukasi',
        'desc' => 'Baca artikel finansial dan jawab 1 kuis singkat.',
        'reward' => 250,
        'type' => 'content',
        'icon' => 'fa-book-open-reader'
    ],
    [
        'id' => 3,
        'title' => 'Undang Teman',
        'desc' => 'Ajak 1 teman aktif dan dapatkan bonus referral.',
        'reward' => 1000,
        'type' => 'referral',
        'icon' => 'fa-user-plus'
    ],
    [
        'id' => 4,
        'title' => 'Tonton Video Sponsor',
        'desc' => 'Selesaikan video sponsor untuk poin tambahan.',
        'reward' => 300,
        'type' => 'ads',
        'icon' => 'fa-video'
    ],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuanTask - Web Penghasil Saldo DANA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <header class="topbar glass-card">
        <div>
            <p class="label mb-1">Selamat datang kembali 👋</p>
            <h1 class="mb-0">CuanTask App</h1>
        </div>
        <button class="btn btn-light btn-sm px-3" id="notifBtn">
            <i class="fa-solid fa-bell me-1"></i> Notif
        </button>
    </header>

    <section class="wallet-card glass-card mt-3">
        <div>
            <p class="label mb-1">Saldo Poin Kamu</p>
            <h2 id="pointBalance">4.850</h2>
            <small>Konversi estimasi: Rp4.850</small>
        </div>
        <button class="btn btn-gradient" id="withdrawBtn">
            <i class="fa-solid fa-wallet me-1"></i> Tarik ke DANA
        </button>
    </section>

    <section class="stats-grid mt-3">
        <article class="stat-card glass-card">
            <p>Total Penghasilan</p>
            <h3>Rp1.240.000</h3>
        </article>
        <article class="stat-card glass-card">
            <p>Pengguna Aktif</p>
            <h3>18.932</h3>
        </article>
        <article class="stat-card glass-card">
            <p>Task Hari Ini</p>
            <h3>7 Tersedia</h3>
        </article>
    </section>

    <section class="mt-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Misi Harian</h4>
            <span class="badge rounded-pill text-bg-light">Reward Real-Time</span>
        </div>

        <div class="task-list" id="taskList">
            <?php foreach ($tasks as $task): ?>
                <article class="task-card glass-card">
                    <div class="task-icon">
                        <i class="fa-solid <?= htmlspecialchars($task['icon']) ?>"></i>
                    </div>
                    <div class="task-content">
                        <h5><?= htmlspecialchars($task['title']) ?></h5>
                        <p class="mb-2"><?= htmlspecialchars($task['desc']) ?></p>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="reward">+<?= number_format($task['reward'], 0, ',', '.') ?> poin</span>
                            <button
                                class="btn btn-sm btn-gradient task-btn"
                                data-task="<?= htmlspecialchars($task['title']) ?>"
                                data-reward="<?= (int) $task['reward'] ?>"
                            >
                                Mulai
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <nav class="bottom-nav glass-card mt-4">
        <button class="active"><i class="fa-solid fa-house"></i><span>Home</span></button>
        <button><i class="fa-solid fa-list-check"></i><span>Task</span></button>
        <button><i class="fa-solid fa-users"></i><span>Referral</span></button>
        <button><i class="fa-solid fa-user"></i><span>Akun</span></button>
    </nav>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="liveToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastBody">Halo, siap kumpulkan cuan hari ini?</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title">Penarikan Saldo DANA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Nomor DANA</label>
                <input type="text" id="danaNumber" class="form-control mb-3" placeholder="08xxxxxxxxxx">

                <label class="form-label">Nominal (poin)</label>
                <input type="number" id="withdrawAmount" class="form-control" min="15000" step="500" placeholder="Minimal 15.000">
                <small class="text-muted d-block mt-2">Minimal withdraw 15.000 poin (1 poin = Rp1).</small>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-gradient" id="submitWithdraw">Kirim Permintaan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body>
</html>
