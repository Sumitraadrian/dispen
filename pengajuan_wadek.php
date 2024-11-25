<?php
session_start();
include 'db.php';
// Menentukan halaman saat ini
$currentPage = basename($_SERVER['PHP_SELF']);


if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // User is not logged in
    exit();
}

if ($_SESSION['role'] !== 'wakil_dekan') {
    header('Location: index.php'); // Unauthorized access
    exit();
}
// Proses penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // memastikan id adalah angka
    $deleteQuery = "DELETE FROM pengajuan WHERE id = $delete_id";
    if ($conn->query($deleteQuery) === TRUE) {
        echo "<script>alert('Data berhasil dihapus!');</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . $conn->error . "');</script>";
    }
}

// Query untuk mengambil data pengajuan yang disetujui dan menggabungkan dengan data jurusan
$query = "SELECT p.*, j.nama_jurusan FROM pengajuan p 
          INNER JOIN jurusan j ON p.jurusan_id = j.id
          WHERE p.status = 'disetujui'
          ORDER BY j.nama_jurusan, p.tanggal_pengajuan DESC";
$result = $conn->query($query);

// Memeriksa apakah query berhasil dijalankan
if ($result === false) {
    die("Error: " . $conn->error);
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

// Menyiapkan data pengajuan berdasarkan jurusan
$pengajuanByJurusan = [];
while ($row = $result->fetch_assoc()) {
    $jurusan = $row['nama_jurusan']; // Nama jurusan dari tabel jurusan
    if (!isset($pengajuanByJurusan[$jurusan])) {
        $pengajuanByJurusan[$jurusan] = [];
    }
    $pengajuanByJurusan[$jurusan][] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dispensasi</title>
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
            .header-title{
                font-size: small;
            }
            .table-container .h3{
                font-size: small;
            }
        }
        .header-title {
    font-size: 1.5em;  /* Menambah ukuran font judul */
    font-weight: bold;  /* Membuat teks lebih tebal */
    margin-bottom: 20px;  /* Memberi ruang di bawah judul */
    text-align: left;  /* Menjaga teks tetap di tengah */
    color: #333;  /* Warna teks yang sedikit gelap untuk kontras */
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
    
    <small class="text-muted ms-2" style="margin-top: 70px;">Menu</small>
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

    <!-- Menampilkan nama Wakil Dekan di bagian bawah sidebar -->
    <div class="mt-auto text-left p-3" style="background-color: #ffffff; color: black;">
        <small>Logged in as: <br><strong><?php echo $namaWadek; ?></strong></small>
    </div>

</div>

    <div class="main-content"  id="content">
        <div class="container mt-5">
            <div class="table-container">
                <div class="header-title">List Data Dispensasi Disetujui</div>
                <?php foreach ($pengajuanByJurusan as $jurusan => $pengajuans): ?>
                    <h3>Jurusan: <?= htmlspecialchars($jurusan); ?></h3>
                    <div class="table-responsive">
                        <table id="dispensasiTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>NIM</th>
                                <th>Angkatan</th>
                                <th>Alasan</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th>Persetujuan Anda</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($pengajuans as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nim']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['angkatan']); ?></td>
                                <td><?= htmlspecialchars($row['alasan']); ?></td>
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
                                <td class="text-center">
                                            <?php if ($row['status_wadek'] == 'disetujui final'): ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php elseif ($row['status_wadek'] == 'ditolak'): ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                <td class="action-buttons text-center">
                                <a href="persetujuan_wadek.php?id=<?= urlencode($row['id']); ?>" class="btn btn-info btn-custom" style="text-decoration: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                    <button class="btn btn-danger btn-custom" onclick="confirmDelete(<?= $row['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dispensasiTable').DataTable({
            "pagingType": "simple_numbers",
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": 7 }
            ]
        });
    });

    function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                window.location.href = "pengajuan_wadek.php?delete_id=" + id;
            }
        }
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
    
</script>

</body>
</html>
