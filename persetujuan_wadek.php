<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // User is not logged in
    exit();
}

if ($_SESSION['role'] !== 'wakil_dekan') {
    header('Location: index.php'); // Unauthorized access
    exit();
}

// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk mengirim email konfirmasi
function sendApprovalEmail($email, $nama_lengkap, $id_pengajuan, $nim, $alasan) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Alamat SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'adriansyahsumitra@gmail.com'; // Alamat email Anda
        $mail->Password = 'kivu njcw rcam nkwl'; // Kata sandi email Anda
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // Gunakan level debug 3 atau 4 untuk output lebih terperinci

        // Pengaturan penerima
        $mail->setFrom('adriansyahsumitra@gmail.com', 'Info Dispensasi Saintek');
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Persetujuan Pengajuan Dispensasi";
        $mail->Body = "
        Halo $nama_lengkap,<br><br>
            Pengajuan dispensasi Anda telah disetujui. Berikut adalah detail pengajuan Anda:<br><br>
            <strong>ID Pengajuan:</strong> $id_pengajuan<br>
            <strong>Nama:</strong> $nama_lengkap<br>
            <strong>NIM:</strong> $nim<br>
            <strong>Email:</strong> $email<br>
            <strong>Alasan:</strong> $alasan<br><br>
            Anda dapat mengambil surat dispensasi di Tata Usaha Fakultas Sains dan Teknologi.<br><br>
            Terima kasih.
        ";

        $mail->send();
        echo "Email berhasil dikirim.";
    } catch (Exception $e) {
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
    }
}

// Cek apakah tombol "Setuju" diklik
if (isset($_POST['approve'])) {
    $status_wadek = 'disetujui final';
    $id = $_POST['id'];
    
    // Update status Wakil Dekan di database
    $query = "UPDATE pengajuan SET status_wadek = ?, tanggal_acc_wakil_dekan = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_wadek, $id);
    $stmt->execute();

    // Ambil data pengajuan untuk email
    $query = "SELECT * FROM pengajuan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pengajuan = $result->fetch_assoc();

    if ($pengajuan) {
        $email = $pengajuan['email'];
        $nama_lengkap = $pengajuan['nama_lengkap'];
        $nim = $pengajuan['nim'];  // Mengambil NIM dari tabel pengajuan
        $alasan = $pengajuan['alasan'];  // Mengambil alasan dari tabel pengajuan

        // Kirim email konfirmasi jika disetujui
        sendApprovalEmail($email, $nama_lengkap, $id, $nim, $alasan);
    }

    // Redirect ulang halaman untuk menghindari pengiriman ulang form
    header("Location: persetujuan_wadek.php?id=$id");
    exit();
}
// Cek apakah tombol "Tolak" diklik
if (isset($_POST['reject'])) {
    $status_wadek = 'ditolak';
    $id = $_POST['id'];
    
    // Update status Wadek di database menjadi ditolak
    $query = "UPDATE pengajuan SET status_wadek = ?, tanggal_acc_wakil_dekan = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_wadek, $id);
    $stmt->execute();

    // Redirect ulang halaman untuk menghindari pengiriman ulang form
    header("Location: persetujuan_wadek.php?id=$id");
    exit();
}
// Assuming that $_SESSION['user_id'] contains the ID of the logged-in user
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Query to get the name of the Wakil Dekan
    $queryWadek = "SELECT wakil_dekan.nama AS namaWadek FROM wakil_dekan
                   JOIN users ON wakil_dekan.id = users.wakil_dekan_id
                   WHERE users.id = ?";
    $stmt = $conn->prepare($queryWadek);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultWadek = $stmt->get_result();

    if ($resultWadek->num_rows > 0) {
        $row = $resultWadek->fetch_assoc();
        $namaWadek = $row['namaWadek'];
    } else {
        $namaWadek = "Unknown"; // Default name if not found
    }

    $stmt->close();
}
// Pastikan ID pengajuan ada di URL dan valid
if (!isset($_GET['id'])) {
    echo "ID pengajuan tidak ditemukan.";
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM pengajuan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pengajuan = $result->fetch_assoc();

if (!$pengajuan) {
    echo "Pengajuan tidak ditemukan.";
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dispensasi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #a3c1e0;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            z-index: 1000;
        }
        .sidebar.visible {
            transform: translateX(0);
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1050; 
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 70px;
            min-height: calc(100vh - 56px); 
        }
        /* Status Badge */
        .status-badge {
            padding: 5px 10px;
            font-size: 0.8em;
            border-radius: 15px;
            color: white;
            display: inline-block;
            font-weight: bold;
        }
        .status-belum-diproses {
            background-color: orange;
        }
        .status-disetujui {
            background-color: green;
        }
        .status-ditolak {
            background-color: red;
        }
       
        .btn.approve {
            background-color: green;
            color: white;
        }
        .btn.reject {
            background-color: red;
            color: white;
        }
        .card {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
        }
        .back-button {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        /* Gaya untuk ikon lampiran */
.fas.fa-file-alt {
    color: #343a40; /* Warna ikon dokumen */
    cursor: pointer;
}

/* Gaya status badge */
.status-badge {
    padding: 5px 10px;
    font-size: 0.8em;
    border-radius: 15px;
    color: white;
    display: inline-block;
    font-weight: bold;
}

.status-belum-diproses {
    background-color: orange;
}

.status-disetujui {
    background-color: green;
}

.status-ditolak {
    background-color: red;
}

/* Gaya tombol aksi */
.btn-success {
    background-color: green;
    border-color: green;
    font-size: 0.9em;
}

.btn-danger {
    background-color: red;
    border-color: red;
    font-size: 0.9em;
}
/* CSS untuk mengatur layout data dispensasi */
.data-list {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Memberikan jarak antara setiap item */
}

.data-list p {
    display: flex;
    justify-content: space-between;
    margin: 0;
}

.data-list p strong {
    width: 40%; /* Menentukan lebar label di sisi kiri */
}

.data-list p span, .data-list p a {
    width: 60%; /* Menentukan lebar nilai di sisi kanan */
}
 /* Responsiveness */
 @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
            #sidebarToggle {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <button class="btn me-3" id="sidebarToggle" style="background-color: transparent; border: none;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand text-black" href="#">SUDISMA</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Tambahkan menu lain di sini jika diperlukan -->
            </ul>
        </div>
    </div>
</nav>


<div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar">
    <h4 class="text-center">SUDISMA</h4>
   
    <small class="text-muted ms-2" style="margin-top: 30px;">Menu</small>
    <nav class="nav flex-column mt-2">
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_wadek.php' ? 'active' : '' ?>" href="dashboard_wadek.php" style="color: <?= $currentPage == 'dashboard_wadek.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
        </a>
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengajuan_wadek.php' ? 'active' : '' ?>" href="pengajuan_wadek.php" style="color: <?= $currentPage == 'pengajuan_wadek.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Dispensasi
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="angkatan_kajur.php" style="color: black;">
            <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Data Ditolak
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="list_dosen.php" style="color: black;">
            <i class="bi bi-person-check" style="margin-right: 15px;"></i> Dosen Penyetuju
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="list_tanggal.php" style="color: black;">
            <i class="bi bi-calendar-check" style="margin-right: 15px;"></i> Tanggal Pengajuan
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="logout.php" style="color: black;">
            <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
        </a>
    </nav>

    <div class="mt-auto text-left p-3" style="background-color: #ffffff; color: black;">
        <small>Logged in as: <br><strong><?php echo $namaWadek; ?></strong></small>
    </div>

</div>


    <div class="main-content">
        <div class="container">
            <div class="card shadow-sm border-0">
                <h3 class="card-title text-center mb-3">List Data Dispensasi</h3>
                <div class="card-body">
    <div class="data-list">
        <p><strong>Nama:</strong> <span><?= htmlspecialchars($pengajuan['nama_lengkap']); ?></span></p>
        <p><strong>NIM:</strong> <span><?= htmlspecialchars($pengajuan['nim']); ?></span></p>
        <p><strong>Angkatan:</strong> <span><?= htmlspecialchars($pengajuan['angkatan']); ?></span></p>
        <p><strong>Tanggal Pengajuan:</strong> <span><?= htmlspecialchars($pengajuan['tanggal_pengajuan']); ?></span></p>
        <p><strong>Alasan:</strong> <span><?= htmlspecialchars($pengajuan['alasan']); ?></span></p>
        <p><strong>Email:</strong> <span><?= htmlspecialchars($pengajuan['email']); ?></span></p>
        <p><strong>Lampiran Dokumen:</strong> 
                            <?php if (!empty($pengajuan['dokumen_lampiran'])): ?>
                                <a href="uploads/<?= $pengajuan['dokumen_lampiran']; ?>" target="_blank">Lihat Dokumen</a>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </p>
        <p><strong>Status Kajur:</strong>
    <span class="status-badge 
        <?= $pengajuan['status'] == 'disetujui' ? 'status-disetujui' : ($pengajuan['status'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diproses'); ?>">
        <?php 
            // Menampilkan status kajur sesuai dengan nilai di database
            echo ($pengajuan['status'] == 'disetujui') ? 'Disetujui' : 
                 (($pengajuan['status'] == 'ditolak') ? 'Ditolak' : 'Belum Diproses');
        ?>
    </span>
</p>

<p><strong>Status Wadek:</strong>
    <span class="status-badge 
        <?= $pengajuan['status_wadek'] == 'disetujui final' ? 'status-disetujui' : ($pengajuan['status_wadek'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diproses'); ?>">
        <?php 
            // Menampilkan status wadek sesuai dengan nilai di database
            echo ($pengajuan['status_wadek'] == 'disetujui final') ? 'Disetujui' : 
                 (($pengajuan['status_wadek'] == 'ditolak') ? 'Ditolak' : 'Belum Diproses');
        ?>
    </span>
</p>


        
    
    </div>

    <!-- Form untuk Setuju / Tolak -->
    <p><strong>Aksi:</strong>
        <form method="post" class="d-inline">
        <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
        <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
        <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
        <input type="hidden" name="nim" value="<?= $pengajuan['nim']; ?>">
        <input type="hidden" name="alasan" value="<?= $pengajuan['alasan']; ?>">
        <button type="submit" name="approve" class="btn btn-success btn-sm mx-1">Setuju</button>
        <button type="submit" name="reject" class="btn btn-danger btn-sm">Tidak</button>
</form>

    </p>
    
</div>

                
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('visible');
        });
    </script>
</body>
</html>
