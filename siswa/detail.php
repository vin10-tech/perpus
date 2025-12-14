
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'];

// Get student data
$stmt = $conn->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger">Siswa tidak ditemukan</div>';
    exit();
}

$student = $result->fetch_assoc();

// Get borrowing history
$borrow_query = $conn->prepare("
    SELECT p.*, GROUP_CONCAT(b.judul SEPARATOR ', ') as books,
           CASE WHEN p.status_pinjam = 'Dipinjam' THEN 'Dipinjam' ELSE 'Dikembalikan' END as status
    FROM peminjaman p
    JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
    JOIN buku b ON dp.id_buku = b.id_buku
    WHERE p.id_siswa = ?
    GROUP BY p.id_pinjam
    ORDER BY p.tanggal_pinjam DESC
    LIMIT 5
");
$borrow_query->bind_param("i", $id);
$borrow_query->execute();
$borrow_result = $borrow_query->get_result();

// Get statistics
$total_pinjam = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE id_siswa = ?");
$total_pinjam->bind_param("i", $id);
$total_pinjam->execute();
$total_borrowed = $total_pinjam->get_result()->fetch_assoc()['total'];

$active_pinjam = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE id_siswa = ? AND status_pinjam = 'Dipinjam'");
$active_pinjam->bind_param("i", $id);
$active_pinjam->execute();
$active_borrowed = $active_pinjam->get_result()->fetch_assoc()['total'];
?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Siswa</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-semibold">ID Siswa:</td>
                        <td><?= $student['id_siswa'] ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">NIS:</td>
                        <td><?= htmlspecialchars($student['nis']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Nama Lengkap:</td>
                        <td><?= htmlspecialchars($student['nama_siswa']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Kelas:</td>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($student['kelas']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Jurusan:</td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($student['jurusan']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Kontak:</td>
                        <td><?= htmlspecialchars($student['kontak']) ?: '<em class="text-muted">Tidak ada</em>' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Peminjaman</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1"><?= $total_borrowed ?></h4>
                            <small class="text-muted">Total Pinjam</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning mb-1"><?= $active_borrowed ?></h4>
                        <small class="text-muted">Sedang Dipinjam</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Peminjaman Terakhir</h6>
    </div>
    <div class="card-body">
        <?php if ($borrow_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Buku</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($borrow = $borrow_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($borrow['tanggal_pinjam'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($borrow['tanggal_jatuh_tempo'])) ?></td>
                            <td>
                                <small><?= htmlspecialchars($borrow['books']) ?></small>
                            </td>
                            <td>
                                <?php if ($borrow['status'] == 'Dipinjam'): ?>
                                    <span class="badge bg-warning">Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-book-open fa-2x mb-2"></i>
                <p>Belum ada riwayat peminjaman</p>
            </div>
        <?php endif; ?>
    </div>
</div>
