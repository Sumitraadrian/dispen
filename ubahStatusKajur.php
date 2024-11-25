<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Ambil status pengajuan saat ini
    $query = "SELECT status FROM pengajuan WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Error executing query']);
        exit;
    }

    $row = mysqli_fetch_assoc($result);
    
    if ($row) {
        $current_status = $row['status'];
        
        // Logika pengubahan status
        if ($current_status === 'ditolak') {
            $new_status = 'disetujui';
        } elseif ($current_status === 'disetujui') {
            $new_status = 'ditolak';
        } else { // status 'pending'
            $new_status = 'disetujui'; // atau 'ditolak' tergantung kebutuhan
        }

        // Update status ke status baru
        $update_query = "UPDATE pengajuan SET status = '$new_status' WHERE id = '$id'";

        if (mysqli_query($conn, $update_query)) {
            echo json_encode(['success' => true, 'new_status' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Gagal memperbarui status']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Data tidak ditemukan']);
    }
}
?>
