<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM pengajuan WHERE user_id = '$user_id'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pengajuan Dispensasi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .badge-disetujui { background-color: #28a745; color: white;}
        .badge-pending { background-color: #fd7e14;color: white; }
        .badge-ditolak { background-color: #dc3545; color: white;}
       
        .badge-waiting { background-color: #ffc107; color: black; }

        /* Sidebar styles */
        .sidebar {
            background-color: #f8f9fa;
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            transition: transform 0.3s ease;
            z-index: 1;
        }
        .sidebar.collapsed { transform: translateX(-250px); }
        
        /* Content wrapper styles */
        .content-wrapper {
            margin-left: 250px;
            padding-top: 140px;
            margin-bottom: 40px;
            transition: margin-left 0.3s ease;
        }
        .content-wrapper.expanded { margin-left: 0; }
        
        /* Navbar styles */
        .navbar {
            background-color: #fff;
            color: black;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        footer {
        padding-top: 40px;
    }

        .table-title { font-weight: bold; color: #007bff; font-size: 1.25em; padding-left: 10px; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                transform: translateX(-100%);
            }
            .sidebar.collapsed {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
                padding-top: 90px;
                padding-left: 30px;
                padding-right: 10px;
            }
            .content-wrapper.expanded {
                margin-left: 0;
            }
            .navbar-brand {
                display: flex;
                align-items: center;
            }
            .navbar-brand h4 {
                margin-left: 10px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .table-title { font-size: 1.1em; }
            .content-wrapper { padding-top: 80px; }
            .spacer { height: 50px; }
        }
        .status-wadek .badge-success {
            background-color: #007bff !important;
            color: white !important;
        }



        .badge-danger {
            background-color: #dc3545; /* Red for "Ditolak" */
            color: white;
        }

    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <button class="btn me-3" id="sidebarToggle" style="background-color: transparent; border: none;">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <a class="navbar-brand text-dark" href="#">SUDISMA</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav"></div>
        </div>
    </nav>

   <!-- Sidebar -->
<div class="sidebar bg-light p-3 shadow-lg" id="sidebar">
    <h4 class="text-center">SUDISMA</h4>
    <div style="height: 40px;"></div>
    <small class="text-muted ms-2">Menu</small>
    <nav class="nav flex-column mt-2">
        <a class="nav-link active d-flex align-items-center text-dark" href="dashboard_mahasiswa.php">
            <i class="bi bi-speedometer2" style="margin-right: 10px;"></i> Dashboard
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="status_pengajuan.php">
            <i class="bi bi-file-earmark-text" style="margin-right: 10px;"></i> Status Pengajuan
        </a>
        <a class="nav-link d-flex align-items-center text-dark" href="logout.php">
            <i class="bi bi-box-arrow-right" style="margin-right: 10px;"></i> Logout
        </a>
    </nav>
</div>



<!-- Main Content -->
<div class="container content-wrapper" id="content">
    <div class="card shadow-lg">
        <div class="card-header text-left">
            <h2 class="table-title">Status Pengajuan Dispensasi</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan alasan atau status">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Alasan</th>
                            <th>Status Kajur</th>
                            <th>Status Wadek</th>
                        </tr>
                    </thead>
                    <tbody id="pengajuanTable">
                        <?php if ($result->num_rows > 0): ?>
                                <?php $counter = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $counter++; ?></td>
                                        <td><?= $row['tanggal_pengajuan']; ?></td>
                                        <td><?= $row['alasan']; ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $row['status'] == 'disetujui' ? 'badge-disetujui' : ($row['status'] == 'pending' ? 'badge-pending' : 'badge-ditolak') ?>">
                                                <?= ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                       
                                        <td class="status-wadek">
                                            <?php
                                            if (empty($row['status_wadek'])) {
                                                echo '<span class="badge badge-waiting">Belum Disetujui Kajur</span>';
                                        } elseif ($row['status_wadek'] == 'disetujui final') {
                                                // Pastikan badge-success diterapkan untuk status 'Disetujui Final'
                                                echo '<span class="badge badge-success">Disetujui Final</span>';
                                            } elseif ($row['status_wadek'] == 'ditolak') {
                                                echo '<span class="badge badge-danger">Ditolak</span>';
                                            } else {
                                                echo '<span class="badge badge-pending">' . ucfirst($row['status_wadek']) . '</span>';
                                            }
                                            ?>
                                        </td>



                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada pengajuan dispensasi.</td>
                                </tr>
                            <?php endif; ?>
                    </tbody>
                </table>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#pengajuanTable tr');
        rows.forEach(row => {
            let alasan = row.cells[2].textContent.toLowerCase();
            let statusKajur = row.cells[3].textContent.toLowerCase();
            let statusWadek = row.cells[4].textContent.toLowerCase();
            row.style.display = (alasan.includes(filter) || statusKajur.includes(filter) || statusWadek.includes(filter)) ? '' : 'none';
        });
    });

    document.getElementById("sidebarToggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("content");

        sidebar.classList.toggle("collapsed");

        // Toggle margin based on sidebar state and window width
        if (window.innerWidth > 768) {
            content.style.marginLeft = sidebar.classList.contains("collapsed") ? "0" : "250px";
        } else {
            content.classList.toggle("expanded");
        }
    });
</script>
</body>
</html>
