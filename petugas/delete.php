
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

// Get petugas data first
$stmt = $conn->prepare("SELECT * FROM petugas WHERE id_petugas = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$petugas = $result->fetch_assoc();

if (!$petugas) {
    header('Location: index.php?error=Petugas tidak ditemukan');
    exit();
}

// Prevent deletion of admin account
if ($petugas['username'] == 'admin') {
    header('Location: index.php?error=Akun administrator tidak dapat dihapus');
    exit();
}

// Check if this petugas has any loan records
$loan_check = $conn->prepare("SELECT COUNT(*) as count FROM peminjaman WHERE id_petugas = ?");
$loan_check->bind_param("i", $id);
$loan_check->execute();
$loan_result = $loan_check->get_result();
$loan_count = $loan_result->fetch_assoc()['count'];

if ($loan_count > 0) {
    header('Location: index.php?error=Tidak dapat menghapus petugas karena masih memiliki riwayat transaksi peminjaman');
    exit();
}

// Delete the petugas
$delete_stmt = $conn->prepare("DELETE FROM petugas WHERE id_petugas = ?");
$delete_stmt->bind_param("i", $id);

if ($delete_stmt->execute()) {
    header('Location: index.php?success=Petugas berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus petugas: ' . $conn->error);
}
exit();
?>
