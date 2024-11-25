<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Pastikan pengguna telah login dan memiliki session 'user_id'
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query untuk mengambil nama jurusan berdasarkan user_id ketua jurusan
    $query = "SELECT jurusan.nama_jurusan, jurusan.id AS jurusan_id
              FROM users AS users
              JOIN dosen AS dosen ON users.dosen_id = dosen.id
              JOIN jurusan AS jurusan ON jurusan.ketua_jurusan_id = dosen.id
              WHERE users.id = '$user_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_jurusan = $row['nama_jurusan'];
        $jurusan_id = $row['jurusan_id']; // ID jurusan yang terkait dengan ketua jurusan

        // Query untuk mengambil data pengajuan sesuai jurusan
        if (isset($_GET['id'])) {
            $pengajuan_id = $_GET['id'];
            $query_pengajuan = "SELECT * FROM pengajuan WHERE id = '$pengajuan_id' AND jurusan_id = '$jurusan_id'";
            $result_pengajuan = mysqli_query($conn, $query_pengajuan);

            if ($result_pengajuan && mysqli_num_rows($result_pengajuan) > 0) {
                $pengajuan = mysqli_fetch_assoc($result_pengajuan);
            } else {
                echo "Data pengajuan tidak ditemukan!";
                exit;
            }
        } else {
            echo "ID pengajuan tidak disediakan!";
            exit;
        }

    } else {
        echo "Data jurusan tidak ditemukan!";
        exit;
    }

} else {
    header("Location: index.php"); // Redirect ke halaman login jika belum login
    exit;
}

// Cek apakah tombol "Setuju" atau "Tolak" diklik
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $status = isset($_POST['approve']) ? 'disetujui' : 'ditolak'; // Tentukan status
    $status_wadek = isset($_POST['approve']) ? 'pending' : null;  // Status Wadek jika disetujui oleh Kajur
    $id = $_POST['id'];

    // Query untuk memperbarui status di tabel pengajuan
    $query = "UPDATE pengajuan 
              SET status = ?, status_wadek = ?, tanggal_acc_ketua_jurusan = NOW() 
              WHERE id = ? AND jurusan_id = ?";
    
    // Persiapkan statement dan bind parameter
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssii", $status, $status_wadek, $id, $jurusan_id);  // Pastikan tipe data yang sesuai
        $stmt->execute();

        // Redirect untuk mencegah pengiriman ulang form
        header("Location: persetujuan_kajur.php?id=$id");
        exit();
    } else {
        // Jika gagal menyiapkan statement, tampilkan error
        echo "Error preparing statement: " . $conn->error;
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
        .data-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .data-list p {
            display: flex;
            justify-content: space-between;
            margin: 0;
        }

        .data-list p strong {
            width: 40%;
        }

        .data-list p span, .data-list p a {
            width: 60%;
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


    <!-- Sidebar -->
    <div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar" style="height: 100vh;">
        <h4 class="text-center">SUDISMA</h4>
        
        <small class="text-muted ms-2" style="margin-top: 70px;">Menu</small>
        <nav class="nav flex-column mt-2">
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_kajur.php' ? 'active' : '' ?>" href="dashboard_kajur.php" style="color: <?= $currentPage == 'dashboard_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengajuan_kajur.php' ? 'active' : '' ?>" href="pengajuan_kajur.php" style="color: <?= $currentPage == 'pengajuan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Dispensasi
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'angkatan_kajur.php' ? 'active' : '' ?>" href="angkatan_kajur.php" style="color: <?= $currentPage == 'angkatan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Data Ditolak
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'riwayat_kajur.php' ? 'active' : '' ?>" href="riwayat_kajur.php" style="color: <?= $currentPage == 'riwayat_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-archive" style="margin-right: 15px;"></i> Riwayat Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengaturan_kajur.php' ? 'active' : '' ?>" href="pengaturan_kajur.php" style="color: <?= $currentPage == 'pengaturan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'logout.php' ? 'active' : '' ?>" href="logout.php" style="color: <?= $currentPage == 'logout.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
            </a>
        </nav>

        <!-- Menampilkan nama Kajur di bagian paling bawah sidebar -->
        <div class="mt-auto text-left p-3" style="background-color: #ffffff; color: black;">
            <small>Logged in as: <br><strong><?php echo $namaKajur; ?></strong></small>
        </div>
    </div>


    <div class="main-content">
        <div class="container">
            <div class="card shadow-sm border-0">
                <h3 class="header-title">List Data Dispensasi</h3>
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

        <p><strong>Status:</strong> 
            <span class="status-badge 
                <?= $pengajuan['status'] == '' ? 'status-belum-diproses' : 
                    ($pengajuan['status'] == 'disetujui' ? 'status-disetujui' : 'status-ditolak'); ?>">
                <?= $pengajuan['status'] == '' ? 'Belum diproses' : htmlspecialchars($pengajuan['status']); ?>
            </span>
        </p>
        
    
    </div>

    <!-- Form untuk Setuju / Tolak -->
    <p><strong>Aksi:</strong>
        <form method="post" class="d-inline">
            <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
            <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
            <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
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
        // JavaScript untuk toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('visible');
        });

    </script>
</body>
</html>
