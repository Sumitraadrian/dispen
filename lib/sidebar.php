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