<?php
session_start();
include 'db.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan Anda telah menginstal PHPMailer (atau menggunakan autoload.php dari Composer)
require 'vendor/autoload.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Pengguna ditemukan, buat token reset
        $user = $result->fetch_assoc();
        $reset_token = bin2hex(random_bytes(16));  // Menghasilkan token acak untuk reset password

        // Simpan token reset ke database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $reset_token, $email);
        if ($stmt->execute()) {
            // Token reset disimpan, sekarang kirim email
            $reset_link = "http://localhost/dispen/reset_password.php?token=" . $reset_token;

            // Kirim email dengan PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Konfigurasi SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gunakan server SMTP Gmail
                $mail->SMTPAuth = true;
                $mail->Username = 'adriansyahsumitra@gmail.com'; // Ganti dengan email Gmail Anda
                $mail->Password = 'kivu njcw rcam nkwl'; // Ganti dengan password Gmail Anda
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Pengaturan email
                $mail->setFrom('adriansyahsumitra@gmail.com', 'SUDISMA'); // Alamat pengirim
                $mail->addAddress($email); // Alamat penerima
                $mail->Subject = 'Reset Password - SUDISMA';
                $mail->Body = 'Klik tautan berikut untuk mereset password Anda: ' . $reset_link;

                // Kirim email
                $mail->send();
                echo "<div class='alert alert-success text-center'>Email dengan link reset telah dikirim ke alamat email Anda.</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger text-center'>Terjadi kesalahan saat mengirim email: {$mail->ErrorInfo}</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Terjadi kesalahan saat menyimpan token reset.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Email tidak ditemukan.</div>";
    }
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
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Lupa Password?</h2>
                    <p class="text-muted text-center">Masukkan email Anda untuk mereset password</p>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="email" class="text-dark">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-success btn-block">Kirim Link Reset</button>
                    </form>

                    <p class="text-muted mt-3 text-center">Kembali ke <a href="index.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
