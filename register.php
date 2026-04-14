<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/smtp_mailer.php';

require_guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        $pdo = db();
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkStmt->execute(['email' => $email]);

        if ($checkStmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insertUser = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, is_verified, points, created_at)
                VALUES (:name, :email, :password_hash, 0, 0, NOW())'
            );

            $insertUser->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            $userId = (int) $pdo->lastInsertId();
            $token = bin2hex(random_bytes(32));

            $insertToken = $pdo->prepare(
                'INSERT INTO email_verifications (user_id, token, expires_at, created_at)
                VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())'
            );

            $insertToken->execute([
                'user_id' => $userId,
                'token' => $token,
            ]);

            $app = app_config()['app'];
            $verificationUrl = rtrim($app['url'], '/') . '/verify.php?token=' . urlencode($token);

            $subject = 'Verifikasi akun CuanTask kamu';
            $htmlBody = "
                <h2>Halo {$name},</h2>
                <p>Terima kasih sudah daftar di CuanTask.</p>
                <p>Silakan klik link berikut untuk verifikasi email:</p>
                <p><a href=\"{$verificationUrl}\">Verifikasi Email Sekarang</a></p>
                <p>Link berlaku 24 jam.</p>
            ";

            $sent = smtp_send_verification($email, $name, $subject, $htmlBody);

            if ($sent) {
                flash_set('success', 'Akun berhasil dibuat. Cek email untuk verifikasi akun via link unik.');
                redirect('/login.php');
            }

            $errors[] = 'Akun berhasil dibuat, tapi email verifikasi gagal dikirim. Cek konfigurasi SMTP di config.php.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CuanTask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <form method="post" class="auth-card glass-card">
        <h1 class="mb-2">Buat Akun</h1>
        <p class="text-light-emphasis mb-4">Daftar pakai email dan verifikasi link unik via SMTP.</p>

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
            <label class="form-label">Nama Lengkap</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
        </div>

        <button class="btn btn-gradient w-100" type="submit">Daftar Sekarang</button>
        <p class="mt-3 mb-0 text-center text-light-emphasis">Sudah punya akun? <a href="login.php" class="text-info">Login</a></p>
    </form>
</div>
</body>
</html>
