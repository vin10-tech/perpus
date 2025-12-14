
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'];

// Check if student has active loans
$stmt = $conn->prepare("SELECT COUNT(*) as active_loans FROM peminjaman WHERE id_siswa = ? AND status_pinjam = 'Dipinjam'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$active_loans = $result->fetch_assoc()['active_loans'];

if ($active_loans > 0) {
    header('Location: index.php?error=Siswa tidak bisa dihapus karena masih memiliki peminjaman aktif');
    exit();
}

// Delete the student
$stmt = $conn->prepare("DELETE FROM siswa WHERE id_siswa = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: index.php?success=Data siswa berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus data siswa');
}
exit();
?>
