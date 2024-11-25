<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Initialize variables to avoid undefined variable notice
$status = isset($_SESSION['status']) ? $_SESSION['status'] : '';
$status_type = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : '';

// Reset status setelah ditampilkan
unset($_SESSION['status']);
unset($_SESSION['status_type']);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query untuk mengambil data Kajur berdasarkan user_id
    $query = "SELECT dosen.id, dosen.nama_dosen, dosen.email, dosen.image
              FROM users
              JOIN dosen ON users.dosen_id = dosen.id
              WHERE users.id = '$user_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_dosen = $row['nama_dosen'];
        $email_dosen = $row['email'];
        $current_image = $row['image']; // Mendapatkan nama file gambar
    } else {
        echo "Data dosen tidak ditemukan!";
        exit;
    }

    // Jika form dikirim, lakukan konfirmasi password
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Proses perubahan password
        if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Cek apakah password saat ini sesuai dengan yang ada di database
            $query_check_password = "SELECT password FROM users WHERE id = '$user_id'";
            $result_check = mysqli_query($conn, $query_check_password);
            $user_data = mysqli_fetch_assoc($result_check);

            if (password_verify($current_password, $user_data['password'])) {
                // Verifikasi password baru
                if ($new_password == $confirm_password) {
                    // Update password di database
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query_update_password = "UPDATE users SET password = '$hashed_new_password' WHERE id = '$user_id'";
                    if (mysqli_query($conn, $query_update_password)) {
                        $status = 'Password berhasil diperbarui!';
                        $status_type = 'success';
                    } else {
                        $status = 'Terjadi kesalahan saat memperbarui password!';
                        $status_type = 'danger';
                    }
                } else {
                    $status = 'Password baru tidak cocok!';
                    $status_type = 'danger';
                }
            } else {
                $status = 'Password saat ini salah!';
                $status_type = 'danger';
            }
        }

        // Pastikan ini bagian upload gambar berfungsi dengan benar
            
    // File upload processing logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_FILES['profile_picture'])) {
            $upload_dir = 'image/';
            $file = $_FILES['profile_picture'];
            $file_name = basename($file['name']);
            $target_file = $upload_dir . $file_name;

            // Verify file size and type
            if ($file['size'] <= 1048576 && in_array($file['type'], ['image/jpeg', 'image/png'])) {
                // Process file upload
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    if (file_exists($target_file)) {
                        // Save the file name in the database
                        $file_name = mysqli_real_escape_string($conn, $file_name);
                        $query_update_image = "UPDATE dosen SET image = '$file_name' WHERE id = (SELECT dosen_id FROM users WHERE id = '$user_id')";
                        if (mysqli_query($conn, $query_update_image)) {
                            $_SESSION['status'] = "File berhasil diunggah dan nama gambar berhasil disimpan ke database!";
                            $_SESSION['status_type'] = 'success';
                        } else {
                            $_SESSION['status'] = "Gagal menyimpan gambar ke database!";
                            $_SESSION['status_type'] = 'danger';
                        }
                    } else {
                        $_SESSION['status'] = "File gagal diunggah.";
                        $_SESSION['status_type'] = 'danger';
                    }
                } else {
                    $_SESSION['status'] = "Gagal mengunggah file.";
                    $_SESSION['status_type'] = 'danger';
                }
            } else {
                $_SESSION['status'] = "File tidak sesuai kriteria (JPG/PNG, max 1MB).";
                $_SESSION['status_type'] = 'danger';
            }
        }
    }


        
    else {
    header("Location: index.php");
    exit;
}
    }
}
// Jika form Informasi Akun dikirim, perbarui data di database
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $new_nama_dosen = mysqli_real_escape_string($conn, $_POST['nama']);
        $new_email_dosen = mysqli_real_escape_string($conn, $_POST['email']);

        // Update data nama dan email di database
        $query_update_profile = "UPDATE dosen SET nama_dosen = '$new_nama_dosen', email = '$new_email_dosen' WHERE id = (SELECT dosen_id FROM users WHERE id = '$user_id')";
        if (mysqli_query($conn, $query_update_profile)) {
            $_SESSION['status'] = "Profil berhasil diperbarui!";
            $_SESSION['status_type'] = 'success';
            header("Location: pengaturan_kajur.php"); // Refresh halaman
            exit;
        } else {
            $_SESSION['status'] = "Terjadi kesalahan saat memperbarui profil!";
            $_SESSION['status_type'] = 'danger';
        }
    }
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    
        // Query untuk mengambil nama dari tabel dosen
        $queryKajur = "SELECT dosen.nama_dosen AS namaKajur FROM dosen
                       JOIN users ON dosen.id = users.dosen_id
                       WHERE users.id = ?";
        $stmt = $conn->prepare($queryKajur);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $resultKajur = $stmt->get_result();
    
            if ($resultKajur->num_rows > 0) {
                $row = $resultKajur->fetch_assoc();
                $namaKajur = $row['namaKajur'];
            } else {
                $namaKajur = "Unknown"; // Nama default jika tidak ditemukan
            }
    
            $stmt->close();
        } else {
            echo "Error in query preparation.";
        }
    }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun Kajur</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="lib/style.css">
   
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        /* Kustomisasi modal sukses dan gagal */
        .modal-success .modal-content {
            border-color: #28a745;
            background-color: #d4edda;
            color: #155724;
        }
        .modal-danger .modal-content {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }
        body {
    background-color: #f8f9fa;
}

    .container {
        margin-top: 120px; /* Atur sesuai keinginan */
    }
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 8px;
    }
    .profile-picture {
        width: 100px;
        height: 100px;
        background-color: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: #555;
        margin: 20px auto;
    }
    .btn-upload {
        width: 100%;
        margin-top: 10px;
    }
    .form-group label {
        font-weight: normal;
    }
    .nav-tabs .nav-link {
        font-weight: bold;
    }
</style>

    </style>
</head>
<body>
<?php include 'lib/navbar.php'; ?>
<?php include 'lib/sidebar.php'; ?>

    </div>
    <div class="container padding-top 40px">
        <h2>Pengaturan Akun Kajur</h2>
        <ul class="nav nav-tabs" id="pengaturanTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Profil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Ubah Password</a>
            </li>
            
        </ul>

        <div class="tab-content mt-4" id="pengaturanTabsContent">
            <!-- Tab Profil -->
            <div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                <div class="row">
                    <!-- Card Profile Picture -->
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h5 class="text-center">Profile Picture</h5>
                          
                            <form method="post" enctype="multipart/form-data">
                                <div class="profile-picture">
                                    <img id="profile-img" src="<?= $current_image ? 'image/' . $current_image : 'default_profile.png' ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                </div>
                                <input type="file" name="profile_picture" id="profilePictureInput" class="btn-upload" onchange="previewProfileImage(event)">
                                <button type="submit" class="btn btn-primary btn-upload"><i class="bi bi-upload"></i> Unggah File</button>
                            </form>
                        </div>
                    </div>

                    <!-- Card Informasi Akun (disebelah kanan) -->
                    <!-- Card Informasi Akun -->
                    <div class="col-md-8">
                        <div class="card p-3">
                            <h5>Informasi Akun</h5>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama_dosen); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email_dosen); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Perbarui Profil</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tab Ubah Password -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card p-3 mt-3">
                    <h5>Ubah Password</h5>
                    <form method="post">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>

            <!-- Tab Setup Aplikasi -->
            
        </div>
    </div>

<!-- Modal for Upload Status (Success or Error) -->
<div class="modal fade" id="uploadStatusModal" tabindex="-1" aria-labelledby="uploadStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadStatusModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMessage">
                <?php 
                    // Display status message from session
                    if ($status != '') {
                        echo "<p class='text-" . ($status_type == 'success' ? 'success' : 'danger') . "'>" . $status . "</p>";
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Include JS and Bootstrap for modal functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="lib/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Menangani form konfirmasi modal
    $('#confirmButton').on('click', function () {
        $('#form-password').submit();
        $('#confirmModal').modal('hide');
    });

    // Show the modal after the page loads, if the session status is set
    <?php if (isset($_SESSION['status'])): ?>
        $(document).ready(function() {
            $('#statusModal').modal('show');
        });
    <?php endif; ?>

    function previewProfileImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            // Menampilkan gambar yang diupload di dalam lingkaran
            document.getElementById('profile-img').src = e.target.result;
        }

        if (file) {
            reader.readAsDataURL(file); // Membaca file gambar sebagai URL
        }
    }
    //Check if session status and type exist and show modal with corresponding message
    <?php if ($status != ''): ?>
        var myModal = new bootstrap.Modal(document.getElementById('uploadStatusModal'), {
            keyboard: false
        });
        myModal.show();

        // Hide the modal after 3 seconds
        setTimeout(function() {
            myModal.hide();
        }, 3000);
    <?php endif; ?>

    
</script>

</body>
</html>
