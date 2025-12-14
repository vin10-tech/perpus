
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$id_kembali = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

$query = "SELECT pg.*, p.tanggal_pinjam, p.tanggal_jatuh_tempo, s.nama_siswa, s.nis, s.kelas, s.jurusan, s.kontak,
          GROUP_CONCAT(CONCAT(b.judul, ' (', b.pengarang, ')') SEPARATOR '<br>') as buku_detail,
          pet.nama_petugas
          FROM pengembalian pg
          JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam
          JOIN siswa s ON p.id_siswa = s.id_siswa
          JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
          JOIN buku b ON dp.id_buku = b.id_buku
          LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
          WHERE pg.id_kembali = ?
          GROUP BY pg.id_kembali";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_kembali);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()):
?>
<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold text-primary mb-3">Informasi Siswa</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Nama:</strong></td>
                <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
            </tr>
            <tr>
                <td><strong>NIS:</strong></td>
                <td><?= $row['nis'] ?></td>
            </tr>
            <tr>
                <td><strong>Kelas:</strong></td>
                <td><?= $row['kelas'] ?> <?= $row['jurusan'] ?></td>
            </tr>
            <tr>
                <td><strong>Kontak:</strong></td>
                <td><?= $row['kontak'] ?: '-' ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold text-primary mb-3">Detail Pengembalian</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>ID Pengembalian:</strong></td>
                <td>#<?= str_pad($row['id_kembali'], 4, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Kembali:</strong></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
            </tr>
            <tr>
                <td><strong>Petugas:</strong></td>
                <td><?= htmlspecialchars($row['nama_petugas']) ?></td>
            </tr>
        </table>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <h6 class="fw-bold text-primary mb-3">Detail Peminjaman</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Tanggal Pinjam:</strong></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
            </tr>
            <tr>
                <td><strong>Jatuh Tempo:</strong></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
            </tr>
            <tr>
                <td><strong>Buku:</strong></td>
                <td><?= $row['buku_detail'] ?></td>
            </tr>
        </table>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <h6 class="fw-bold text-primary mb-3">Informasi Denda</h6>
        <div class="alert <?= $row['denda'] > 0 ? 'alert-warning' : 'alert-success' ?>">
            <div class="row">
                <div class="col-md-3">
                    <strong>Keterlambatan:</strong><br>
                    <span class="h5"><?= $row['telat'] ?> hari</span>
                </div>
                <div class="col-md-3">
                    <strong>Denda:</strong><br>
                    <span class="h5 <?= $row['denda'] > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $row['denda'] > 0 ? 'Rp ' . number_format($row['denda']) : 'Tidak ada' ?>
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Status Denda:</strong><br>
                    <span class="badge <?= $row['status_denda'] == 'Lunas' ? 'bg-success' : 'bg-warning' ?>">
                        <?= $row['status_denda'] ?>
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Perhitungan:</strong><br>
                    <small><?= $row['telat'] ?> hari × Rp 500 = Rp <?= number_format($row['denda']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Data pengembalian tidak ditemukan
</div>
<?php endif; ?>
