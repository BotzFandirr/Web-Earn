<?php
require_once __DIR__ . '/lib/auth.php';

$token = trim($_GET['token'] ?? '');
$success = false;
$message = 'Token verifikasi tidak valid atau sudah kadaluarsa.';

if ($token !== '') {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT ev.id, ev.user_id
         FROM email_verifications ev
         WHERE ev.token = :token
           AND ev.used_at IS NULL
           AND ev.expires_at >= NOW()
         LIMIT 1'
    );

    $stmt->execute(['token' => $token]);
    $verification = $stmt->fetch();

    if ($verification) {
        $pdo->beginTransaction();

        $updateUser = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE id = :id');
        $updateUser->execute(['id' => $verification['user_id']]);

        $updateToken = $pdo->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = :id');
        $updateToken->execute(['id' => $verification['id']]);

        $pdo->commit();

        $success = true;
        $message = 'Verifikasi berhasil! Sekarang kamu bisa login.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - CuanTask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card glass-card text-center">
        <h1 class="mb-3"><?= $success ? '🎉 Email Terverifikasi' : '⚠️ Verifikasi Gagal' ?></h1>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
        <a href="login.php" class="btn btn-gradient w-100">Lanjut ke Login</a>
    </div>
</div>
</body>
</html>
