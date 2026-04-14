<?php
require_once __DIR__ . '/lib/auth.php';

require_guest();

$errors = [];
$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$errors) {
        $stmt = db()->prepare('SELECT id, name, email, password_hash, role, is_verified FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email atau password salah.';
        } elseif ((int) $user['is_verified'] !== 1) {
            $errors[] = 'Akun belum diverifikasi. Cek email dan klik tautan verifikasi.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            flash_set('success', 'Login berhasil. Selamat datang kembali!');
            redirect('/dashboard.php');
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CuanTask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <form method="post" class="auth-card glass-card">
        <h1 class="mb-2">Masuk ke CuanTask</h1>
        <p class="auth-copy mb-4">Akses dashboard task, reward, dan histori akun kamu dengan aman.</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>

        <button class="btn btn-gradient w-100" type="submit">Login Sekarang</button>
        <p class="mt-3 mb-0 text-center auth-copy">Belum punya akun? <a href="register.php" class="text-info fw-semibold">Daftar gratis</a></p>
    </form>
</div>
</body>
</html>
