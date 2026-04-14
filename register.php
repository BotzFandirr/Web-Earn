<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/smtp_mailer.php';

require_guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 120) {
        $errors[] = 'Nama wajib diisi dan maksimal 120 karakter.';
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
                'INSERT INTO users (name, email, password_hash, role, is_verified, points, created_at)
                VALUES (:name, :email, :password_hash, :role, 0, 0, NOW())'
            );

            $insertUser->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => 'user',
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
            $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

            $subject = 'Aktifkan akun CuanTask kamu 🚀';
            $htmlBody = "
                <div style=\"font-family:Arial,sans-serif;background:#f4f8ff;padding:24px;color:#1f2a44;\">
                    <div style=\"max-width:520px;margin:auto;background:#ffffff;border-radius:16px;padding:24px;border:1px solid #e5edff;\">
                        <h2 style=\"margin:0 0 12px;color:#153e90;\">Halo {$safeName}, 👋</h2>
                        <p style=\"margin:0 0 12px;line-height:1.6;\">Terima kasih sudah bergabung di <strong>CuanTask</strong>! Biar akun kamu aktif penuh, tinggal verifikasi email sekarang.</p>
                        <p style=\"margin:0 0 20px;line-height:1.6;\">Klik tombol di bawah ini untuk lanjut:</p>

                        <a href=\"{$verificationUrl}\" style=\"display:inline-block;padding:12px 18px;border-radius:10px;background:linear-gradient(135deg,#00d2ff,#3a7bff,#7f5bff);color:#ffffff;text-decoration:none;font-weight:700;\">✅ Verifikasi Sekarang</a>

                        <p style=\"margin:20px 0 0;line-height:1.6;color:#516182;\">Jika tombol tidak bisa diklik, salin link ini ke browser:</p>
                        <p style=\"word-break:break-all;margin:8px 0 0;color:#1d4ed8;\">{$verificationUrl}</p>
                        <p style=\"margin:18px 0 0;color:#6b7a99;font-size:13px;\">Link ini berlaku selama <strong>24 jam</strong>.</p>
                    </div>
                </div>
            ";

            $sent = smtp_send_verification($email, $name, $subject, $htmlBody);

            if ($sent) {
                flash_set('success', 'Akun berhasil dibuat. Cek email untuk verifikasi akun via link unik.');
                redirect('/login.php');
            }

            $errors[] = 'Akun dibuat, tetapi email verifikasi gagal dikirim. Periksa konfigurasi SMTP di config.php.';
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
        <h1 class="mb-2">Buat Akun Baru</h1>
        <p class="auth-copy mb-4">Daftar dengan email aktif, lalu verifikasi lewat tautan unik yang kami kirimkan via SMTP.</p>

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
            <label class="form-label">Nama Lengkap</label>
            <input name="name" class="form-control" maxlength="120" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
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
        <p class="mt-3 mb-0 text-center auth-copy">Sudah punya akun? <a href="login.php" class="text-info fw-semibold">Login di sini</a></p>
    </form>
</div>
</body>
</html>
