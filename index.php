<?php
session_start();
include 'db.php';

// Handle mahasiswa registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_mahasiswa'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];

    // Check if the username or email already exists (with prepared statement)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Username atau email sudah terdaftar!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Insert new mahasiswa into the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, email) VALUES (?, ?, ?, ?, ?)");
        $role = 'mahasiswa';  // Menambahkan variabel role
        $stmt->bind_param("sssss", $nama, $username, $hashed_password, $role, $email);        
        
        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan saat registrasi!";
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements for login query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables and redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on user role
            switch ($user['role']) {
                case 'kajur':
                    $_SESSION['jurusan_id'] = $user['jurusan_id'];
                    header('Location: dashboard_kajur.php');
                    break;
                case 'mahasiswa':
                    header('Location: dashboard_mahasiswa.php');
                    break;
                case 'admin':
                    header('Location: dashboard_admin.php');
                    break;
                case 'wakil_dekan':
                    $_SESSION['wakil_dekan_id'] = $user['wakil_dekan_id'];
                    header('Location: dashboard_wadek.php');
                    break;
                default:
                    $error = "Role tidak dikenali!";
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Pengguna tidak ditemukan!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Surat Dispensasi Mahasiswa</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
   
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

    <style>
        /* General styles */
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0056b3, #007bff); /* Blue gradient background */
            color: #333;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-primary, .btn-success {
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .custom-img-shift {
            transition: transform 0.3s ease;
            border-radius: 15px;
        }
        .custom-img-shift:hover {
            transform: translateY(-10px);
        }

        /* Title and Subtitle */
        .sudisma-title {
            font-size: 4rem; /* Larger font size for title */
            font-weight: 700;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); /* Subtle shadow for title */
        }
        .sudisma-subtitle {
            font-size: 1.8rem; /* Larger font size for subtitle */
            font-weight: 500;
            color: #e0e0e0;
        }
        
        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .sudisma-title {
                font-size: 2.5rem;
            }
            .sudisma-subtitle {
                font-size: 1.3rem;
            }
        }

        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 align-items-center">
            <div class="col-md-6 text-center text-white mb-4 mb-md-0">
                <h1 class="sudisma-title">SUDISMA</h1>
                <p class="sudisma-subtitle">Surat Dispensasi Mahasiswa</p>
                <img src="image/image.png" alt="Illustration" class="img-fluid mt-3 rounded shadow custom-img-shift" style="max-width: 80%;">
            </div>
            
            <!-- Login / Register Form Section -->
            <div class="col-md-6">
                <div class="card p-5 shadow-lg">
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Selamat Datang di SUDISMA!</h2>
                    <p class="text-muted text-center">Masuk atau daftar untuk mengakses aplikasi</p>

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php elseif (isset($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="loginForm" action="" method="POST">
                        <div class="form-group">
                            <label for="username" class="text-dark">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group position-relative">
                            <label for="password" class="text-dark">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <span class="password-toggle" onclick="togglePassword('password')">
                                <i class="fa fa-eye" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                    </form>

                    <hr>

                    <p class="text-muted text-center" id="registerText">Belum punya akun? <a href="javascript:void(0);" id="toRegisterMahasiswa">Daftar sebagai Mahasiswa</a></p>
                    <p class="text-muted text-center">
                        <a href="forgot_password.php" id="forgotPasswordLink">Lupa Password?</a>
                    </p>


                    <!-- Mahasiswa Registration Form -->
                    <div id="registerMahasiswaForm" class="d-none">
                        <form action="" method="POST">
                        <div class="form-group">
                            <label for="registerNama" class="text-dark">Nama Lengkap</label>
                            <input type="text" id="registerNama" name="nama" class="form-control" required>
                        </div>
                            <div class="form-group">
                                <label for="registerUsername" class="text-dark">Username</label>
                                <input type="text" id="registerUsername" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="registerEmail" class="text-dark">Email</label>
                                <input type="email" id="registerEmail" name="email" class="form-control" required>
                            </div>
                            <div class="form-group position-relative">
                                <label for="registerPassword" class="text-dark">Password</label>
                                <input type="password" id="registerPassword" name="password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('registerPassword')">
                                    <i class="fa fa-eye" id="toggleRegisterPasswordIcon"></i>
                                </span>
                            </div>
                            <div class="form-group position-relative">
                                <label for="registerConfirmPassword" class="text-dark">Konfirmasi Password</label>
                                <input type="password" id="registerConfirmPassword" name="confirm_password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('registerConfirmPassword')">
                                    <i class="fa fa-eye" id="toggleConfirmPasswordIcon"></i>
                                </span>
                            </div>
                            <button type="submit" name="register_mahasiswa" class="btn btn-success btn-block">Register Mahasiswa</button>
                        </form>
                        
                        <p class="text-muted mt-3 text-center" id="backToLoginText">Sudah punya akun? <a href="javascript:void(0);" id="toLogin">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>SUDISMA</h5>
                <p>Platform pengajuan surat dispensasi mahasiswa Fakultas Sains dan Teknologi UIN Sunan Gunung Djati Bandung.</p>
            </div>
            <div class="col-md-4">
                <h5>Kontak Kami</h5>
                <ul class="list-unstyled">
                <li><i class="bi bi-geo-alt-fill"></i> Jl. A.H. Nasution No.105, Cipadung Wetan, Kec. Cibiru, Kota Bandung, Jawa Barat 40614</li>
                <li><i class="bi bi-envelope-fill"></i> <a href="mailto:fst@uinsgd.ac.id" class="text-light">fst@uinsgd.ac.id</a></li>
                </ul>
            </div>
            <div class="col-md-4 md-3">
                <h5 class="text-light">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="dashboard_mahasiswa.php" class="text-light">Dashboard</a></li>
                    <li><a href="form_pengajuan.php" class="text-light">Ajukan Dispensasi</a></li>
                    <li><a href="status_pengajuan.php" class="text-light">Cek Status</a></li>
                    
                </ul>
            </div>

        </div>
        <hr class="my-3">
        <div class="text-center">
            <p class="mb-0">© 2024 SUDISMA - Surat Dispensasi Mahasiswa. All Rights Reserved.</p>
            <p class="mb-0"><small>Developed by Team SUDISMA</small></p>
        </div>
    </div>
</footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
        // Toggle Password Visibility
        function togglePassword(id) {
            const passwordField = document.getElementById(id);
            const toggleIcon = passwordField.nextElementSibling.querySelector('i');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
        
        $(document).ready(function() {
            $('#toRegisterMahasiswa').click(function() {
                $('#loginForm').addClass('d-none');
                $('#registerMahasiswaForm').removeClass('d-none');
                $('#registerText').addClass('d-none');
            });

            $('#toLogin').click(function() {
                $('#registerMahasiswaForm').addClass('d-none');
                $('#loginForm').removeClass('d-none');
                $('#registerText').removeClass('d-none');
            });
        });
    </script>
</body>
</html>