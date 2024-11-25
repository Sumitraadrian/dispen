<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Pastikan pengguna telah login dan memiliki session 'user_id'
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query untuk mendapatkan jurusan_id dari user yang sedang login
    $query = "SELECT jurusan.id AS jurusan_id, jurusan.nama_jurusan
              FROM users
              JOIN dosen ON users.dosen_id = dosen.id
              JOIN jurusan ON jurusan.ketua_jurusan_id = dosen.id
              WHERE users.id = '$user_id'";
    
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $jurusan_id = $row['jurusan_id'];
        $nama_jurusan = $row['nama_jurusan'];
    } else {
        echo "Data jurusan tidak ditemukan!";
        exit;
    }

    // Query untuk mengambil semua pengajuan berdasarkan jurusan_id (menampilkan seluruh data pengajuan)
    $query_pengajuan = "SELECT * FROM pengajuan WHERE jurusan_id = '$jurusan_id' ORDER BY tanggal_pengajuan DESC";

    $result_pengajuan = mysqli_query($conn, $query_pengajuan);
} else {
    header("Location: index.php"); // Redirect ke halaman login jika belum login
    exit;
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
    <title>Pengajuan Kajur</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: -250px; /* Sidebar tersembunyi di kiri */
            width: 250px; /* Lebar sidebar */
            transition: left 0.3s ease; /* Animasi ketika sidebar muncul dari kiri */
            z-index: 1000;
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar h4 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar.visible {
        left: 0; /* Sidebar muncul dari kiri */
    }
        
        .main-content {
            
            margin-left: 250px; /* Beri ruang agar konten tidak tertutup sidebar */
        padding: 20px;
        transition: margin-left 0.3s ease;
        z-index: 1;
        padding-top: 60px; 
        }
        .status-badge {
            padding: 3px 8px;
            font-size: 0.8em;
        }
        .status-belum-diproses {
            background-color: orange;
            color: white;
        }
        .status-diterima {
            background-color: green;
            color: white;
        }
        .status-ditolak {
            background-color: red;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-custom {
            padding: 3px 5px;
            font-size: 0.85em;
            border-radius: 3px;
        }
        .table-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 0; /* Pastikan tidak ada margin di atas tabel */
        }
        .table thead th {
            background-color: #a3c1e0;
            color: black;
            font-size: 0.9em;
        }
        .table th, .table td {
            font-size: 0.85em;
            padding: 8px;
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                left: -100%; /* Sidebar tersembunyi di luar layar */
                top: 0;
            }
            .sidebar.visible {
            left: 0; /* Sidebar muncul dari atas pada layar kecil */
            }
            .main-content {
                margin-left: 0;
                padding-top: 60px; /* Beri ruang di atas untuk navbar */
            }
            .sidebar a {
                font-size: 14px;
                padding: 8px 16px;
            }
            .table th, .table td {
                font-size: 0.75em;
                padding: 6px;
            }
            .table-container {
                padding: 10px;
                margin-top: 0;
            }
        }
        @media (max-width: 576px) {
            .sidebar {
                position: fixed;
            width: 100%;
            height: 100%;
            left: -100%;
            top: 0;
            }
            .main-content {
                padding-top: 60px; /* Pastikan ada ruang untuk navbar */
                margin-left: 0; 
            }
            .table th, .table td {
                font-size: 0.7em;
                padding: 5px;
            }
            .table-container {
                margin-top: 0;
                padding: 10px; /* Sesuaikan padding untuk layar kecil */
            }
        }
        .header-title {
    font-size: 1.5em;  /* Menambah ukuran font judul */
    font-weight: bold;  /* Membuat teks lebih tebal */
    margin-bottom: 20px;  /* Memberi ruang di bawah judul */
    text-align: left;  /* Menjaga teks tetap di tengah */
    color: #333;  /* Warna teks yang sedikit gelap untuk kontras */
}
.status-badge {
    padding: 3px 8px;
    font-size: 0.8em;
}

.status-belum-diproses {
    background-color: orange;
    color: white;
}

.status-diterima {
    background-color: green;
    color: white;
}

.status-ditolak {
    background-color: red;
    color: white;
}
/* Menambahkan padding pada modal dan memperbaiki tampilan form */
.modal-content {
    padding: 20px;
}

.form-group {
    margin-bottom: 1.5rem;
}

/* Ubah warna latar belakang header modal menjadi biru pastel */
.modal-header {
    background-color: #a3c1e0; /* Warna biru pastel */
    color: white;
}

/* Ubah warna tombol close untuk menyesuaikan dengan tema */
.modal-header .close span {
    color: black;
}

.modal-body {
    color: black; /* Warna teks body modal menjadi hitam */
}
.modal-footer {
    display: flex;
    justify-content: space-between;
}

/* Menambahkan margin pada tombol Export Excel */
.btn-export {
    margin-bottom: 20px;  /* Atur spasi atas tombol */
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

    <!-- Main Content -->
    <div class="main-content" id="content">
        <div class="container mt-5">
            <div class="table-container">
            <div class="header-title">Riwayat Pengajuan Dispensasi</div>
            <!-- Tombol untuk membuka Modal -->
            <!-- Tombol untuk membuka Modal dengan kelas btn-export -->
            <button type="button" class="btn btn-primary btn-export" data-toggle="modal" data-target="#exportModal">
                Export Excel
            </button>

                <!-- Modal untuk memilih rentang tanggal -->
                <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exportModalLabel">Pilih Rentang Tanggal</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="GET" action="export_excel.php">
                                    <div class="form-group">
                                        <label for="start_date">Tanggal Mulai</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">Tanggal Selesai</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success mt-3">Cetak Excel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                <table id="dispensasiTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>NIM</th>
                                <th>Angkatan</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result_pengajuan)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nim']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['angkatan']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['tanggal_pengajuan']); ?></td>
                                <td class="text-center">
                                    
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <span class="status-badge status-belum-diproses">Belum diproses</span>
                                    <?php elseif ($row['status'] == 'disetujui'): ?>
                                        <span class="status-badge status-diterima">Diterima</span>
                                    <?php elseif ($row['status'] == 'ditolak'): ?>
                                        <span class="status-badge status-ditolak">Ditolak</span>
                                    <?php endif; ?>
                                </td>


                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
       document.getElementById("sidebarToggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");

        if (window.innerWidth <= 768) {
            // Mode mobile: toggle dari atas
            sidebar.classList.toggle("visible");
        } else {
            // Mode desktop: toggle dari kiri
            sidebar.classList.toggle("visible");
        }
    });
    $(document).ready(function() {
            $('#dispensasiTable').DataTable();
        });
    </script>
</body>
</html>
