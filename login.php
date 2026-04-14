<?php
require_once __DIR__ . '/lib/auth.php';

require_guest();

$errors = [];
$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, name, email, password_hash, is_verified FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Email atau password salah.';
    } elseif ((int) $user['is_verified'] !== 1) {
        $errors[] = 'Akun belum diverifikasi. Cek email dan klik link verifikasi.';
    } else {
        $_SESSION['user_id'] = (int) $user['id'];
        flash_set('success', 'Login berhasil. Selamat datang kembali!');
        redirect('/dashboard.php');
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
        <h1 class="mb-2">Masuk Akun</h1>
        <p class="text-light-emphasis mb-4">Login untuk lanjut ke dashboard aplikasi.</p>

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

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>

        <button class="btn btn-gradient w-100" type="submit">Login</button>
        <p class="mt-3 mb-0 text-center text-light-emphasis">Belum punya akun? <a href="register.php" class="text-info">Daftar</a></p>
    </form>
</div>
</body>
</html>
