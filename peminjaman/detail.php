
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id_pinjam = $_GET['id'] ?? 0;

// Get loan detail
$query = "SELECT p.*, s.nama_siswa, s.nis, s.kelas, s.jurusan, s.kontak,
          pet.nama_petugas, pen.tanggal_kembali, pen.telat, pen.denda, pen.status_denda
          FROM peminjaman p 
          JOIN siswa s ON p.id_siswa = s.id_siswa 
          LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
          LEFT JOIN pengembalian pen ON p.id_pinjam = pen.id_pinjam
          WHERE p.id_pinjam = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pinjam);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

if (!$loan) {
    echo '<div class="alert alert-danger">Data peminjaman tidak ditemukan</div>';
    exit;
}

// Get books for this loan
$books_query = "SELECT b.*, dp.jumlah 
                FROM detail_pinjam dp 
                JOIN buku b ON dp.id_buku = b.id_buku 
                WHERE dp.id_pinjam = ?";
$books_stmt = $conn->prepare($books_query);
$books_stmt->bind_param("i", $id_pinjam);
$books_stmt->execute();
$books_result = $books_stmt->get_result();

$is_late = strtotime($loan['tanggal_jatuh_tempo']) < time() && $loan['status_pinjam'] == 'Dipinjam';
$days_late = $is_late ? ceil((time() - strtotime($loan['tanggal_jatuh_tempo'])) / (60 * 60 * 24)) : 0;
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Informasi Siswa</h6>
        <table class="table table-sm">
            <tr>
                <td width="40%"><strong>Nama:</strong></td>
                <td><?= htmlspecialchars($loan['nama_siswa']) ?></td>
            </tr>
            <tr>
                <td><strong>NIS:</strong></td>
                <td><?= $loan['nis'] ?></td>
            </tr>
            <tr>
                <td><strong>Kelas:</strong></td>
                <td><?= $loan['kelas'] ?> <?= $loan['jurusan'] ?></td>
            </tr>
            <tr>
                <td><strong>Kontak:</strong></td>
                <td><?= htmlspecialchars($loan['kontak']) ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3"><i class="fas fa-calendar me-2"></i>Informasi Peminjaman</h6>
        <table class="table table-sm">
            <tr>
                <td width="40%"><strong>ID Pinjam:</strong></td>
                <td>#<?= str_pad($loan['id_pinjam'], 4, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Pinjam:</strong></td>
                <td><?= date('d/m/Y', strtotime($loan['tanggal_pinjam'])) ?></td>
            </tr>
            <tr>
                <td><strong>Jatuh Tempo:</strong></td>
                <td>
                    <?= date('d/m/Y', strtotime($loan['tanggal_jatuh_tempo'])) ?>
                    <?php if ($is_late): ?>
                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Terlambat <?= $days_late ?> hari</small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Petugas:</strong></td>
                <td><?= htmlspecialchars($loan['nama_petugas']) ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <?php if ($loan['status_pinjam'] == 'Dipinjam'): ?>
                        <?php if ($is_late): ?>
                            <span class="badge bg-danger">Terlambat</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Dipinjam</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-success">Dikembalikan</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php if ($loan['status_pinjam'] == 'Dikembalikan' && $loan['tanggal_kembali']): ?>
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-success mb-3"><i class="fas fa-check-circle me-2"></i>Informasi Pengembalian</h6>
        <div class="alert alert-success">
            <div class="row">
                <div class="col-md-3">
                    <strong>Tanggal Kembali:</strong><br>
                    <?= date('d/m/Y', strtotime($loan['tanggal_kembali'])) ?>
                </div>
                <div class="col-md-3">
                    <strong>Keterlambatan:</strong><br>
                    <?= $loan['telat'] ?> hari
                </div>
                <div class="col-md-3">
                    <strong>Denda:</strong><br>
                    Rp <?= number_format($loan['denda']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Status Denda:</strong><br>
                    <span class="badge <?= $loan['status_denda'] == 'Lunas' ? 'bg-success' : 'bg-warning' ?>">
                        <?= $loan['status_denda'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary mb-3"><i class="fas fa-book me-2"></i>Daftar Buku yang Dipinjam</h6>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Pengarang</th>
                        <th>Penerbit</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($book = $books_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($book['judul']) ?></strong>
                            <?php if ($book['isbn']): ?>
                                <br><small class="text-muted">ISBN: <?= $book['isbn'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($book['pengarang']) ?></td>
                        <td><?= htmlspecialchars($book['penerbit']) ?> (<?= $book['tahun_terbit'] ?>)</td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($book['kategori']) ?></span></td>
                        <td><span class="badge bg-primary"><?= $book['jumlah'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($loan['status_pinjam'] == 'Dipinjam'): ?>
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Informasi Penting</h6>
            <ul class="mb-0">
                <li>Denda keterlambatan: Rp 500 per hari per buku</li>
                <li>Perpanjangan peminjaman maksimal 1 kali (7 hari)</li>
                <li>Hubungi perpustakaan jika ada kendala</li>
                <?php if ($is_late): ?>
                <li class="text-danger"><strong>Buku sudah terlambat <?= $days_late ?> hari. Segera kembalikan!</strong></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="text-center">
            <?php if ($loan['status_pinjam'] == 'Dipinjam'): ?>
                <a href="../pengembalian/add.php?id_pinjam=<?= $loan['id_pinjam'] ?>" class="btn btn-success">
                    <i class="fas fa-undo me-2"></i>Proses Pengembalian
                </a>
            <?php endif; ?>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Cetak Detail
            </button>
        </div>
    </div>
</div>
<?php endif; ?>
