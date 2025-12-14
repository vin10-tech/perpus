
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

if ($_POST) {
    $id_buku = $_POST['id_buku'];
    $additional_stock = $_POST['additional_stock'];
    $stock_note = $_POST['stock_note'] ?? '';
    
    // Get current stock
    $stmt = $conn->prepare("SELECT stok, judul FROM buku WHERE id_buku = ?");
    $stmt->bind_param("i", $id_buku);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    
    if ($book) {
        $new_stock = $book['stok'] + $additional_stock;
        
        // Update stock
        $stmt = $conn->prepare("UPDATE buku SET stok = ? WHERE id_buku = ?");
        $stmt->bind_param("ii", $new_stock, $id_buku);
        
        if ($stmt->execute()) {
            // Log the stock addition (optional - you can create a stock_log table)
            $success_msg = "Stok buku '{$book['judul']}' berhasil ditambahkan sebanyak {$additional_stock} eksemplar. Stok sekarang: {$new_stock}";
            header("Location: edit.php?id={$id_buku}&success=" . urlencode($success_msg));
        } else {
            header("Location: edit.php?id={$id_buku}&error=Gagal menambah stok");
        }
    } else {
        header("Location: index.php?error=Buku tidak ditemukan");
    }
} else {
    header("Location: index.php");
}
exit();
?>
