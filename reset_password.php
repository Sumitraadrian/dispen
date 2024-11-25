<?php
session_start();
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek token di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token valid, proses reset password
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Validasi password baru jika perlu
                if (strlen($new_password) >= 8) { // Contoh: minimal 8 karakter
                    // Hash password dan update di database
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password dan reset token
                    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
                    $stmt->bind_param("ss", $hashed_password, $token);
                    if ($stmt->execute()) {
                        $success = "Password Anda berhasil direset. Silakan login dengan password baru.";
                    } else {
                        $error = "Terjadi kesalahan saat mereset password.";
                    }
                } else {
                    $error = "Password harus memiliki minimal 8 karakter.";
                }
            } else {
                $error = "Password dan konfirmasi password tidak cocok.";
            }
        }
    } else {
        $error = "Token tidak valid.";
    }
} else {
    $error = "Token tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 align-items-center">
            <div class="col-md-6">
                <div class="card p-5 shadow-lg">
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Reset Password</h2>

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php elseif (isset($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="new_password" class="text-dark">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="text-dark">Konfirmasi Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-success btn-block">Reset Password</button>
                    </form>

                    <p class="text-muted mt-3 text-center">Kembali ke <a href="index.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
