<?php
require_once '../config/session.php';
require_once '../config/database.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$message = '';

// Ambil data petugas yang sedang login
$id_petugas = $_SESSION['id_petugas'];

if ($_POST) {
    $nama = $_POST['nama'];
    $password_baru = $_POST['password_baru'];
    
    if (!empty($password_baru)) {
        $stmt = $conn->prepare("UPDATE petugas SET nama_petugas = ?, password = MD5(?) WHERE id_petugas = ?");
        $stmt->bind_param("ssi", $nama, $password_baru, $id_petugas);
    } else {
        $stmt = $conn->prepare("UPDATE petugas SET nama_petugas = ? WHERE id_petugas = ?");
        $stmt->bind_param("si", $nama, $id_petugas);
    }
    
    if ($stmt->execute()) {
        $_SESSION['nama_petugas'] = $nama; // Update session
        $message = '<div class="alert alert-success">Profil berhasil diperbarui!</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal memperbarui profil.</div>';
    }
}

$query = $conn->query("SELECT * FROM petugas WHERE id_petugas = $id_petugas");
$data = $query->fetch_assoc();
?>