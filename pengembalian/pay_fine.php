
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

if ($_POST) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $id_kembali = $_POST['id_kembali'];
    $tanggal_bayar = $_POST['tanggal_bayar'];
    $catatan = $_POST['catatan'] ?? '';
    
    try {
        // Update status denda
        $stmt = $conn->prepare("UPDATE pengembalian SET status_denda = 'Lunas' WHERE id_kembali = ?");
        $stmt->bind_param("i", $id_kembali);
        $stmt->execute();
        
        // Optional: Insert payment record if you want to track payment history
        // You would need a separate table for this
        
        header('Location: index.php?success=' . urlencode('Denda berhasil dibayar'));
        exit();
        
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode('Gagal memproses pembayaran: ' . $e->getMessage()));
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
