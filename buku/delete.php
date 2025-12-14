
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'];

// Check if book exists and is not being borrowed
$stmt = $conn->prepare("SELECT COUNT(*) as borrowed FROM detail_pinjam dp 
                       JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam 
                       WHERE dp.id_buku = ? AND p.status_pinjam = 'Dipinjam'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$borrowed = $result->fetch_assoc()['borrowed'];

if ($borrowed > 0) {
    header('Location: index.php?error=Buku tidak bisa dihapus karena sedang dipinjam');
    exit();
}

// Delete the book
$stmt = $conn->prepare("DELETE FROM buku WHERE id_buku = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: index.php?success=Buku berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus buku');
}
exit();
?>
