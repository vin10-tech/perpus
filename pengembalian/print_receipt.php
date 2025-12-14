
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$id_kembali = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

$query = "SELECT pg.*, p.tanggal_pinjam, p.tanggal_jatuh_tempo, s.nama_siswa, s.nis, s.kelas,
          GROUP_CONCAT(CONCAT(b.judul, ' (', b.pengarang, ')') SEPARATOR ', ') as buku_detail,
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengembalian - <?= $row['nama_siswa'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .row { display: flex; margin-bottom: 5px; }
        .col { flex: 1; }
        .label { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>PERPUSTAKAAN SMK YMIK JAKARTA</h2>
        <h3>BUKTI PENGEMBALIAN BUKU</h3>
        <p>No: <?= str_pad($row['id_kembali'], 6, '0', STR_PAD_LEFT) ?></p>
    </div>

    <div class="content">
        <div class="row">
            <div class="col">
                <div class="label">Nama Siswa:</div>
                <div><?= htmlspecialchars($row['nama_siswa']) ?></div>
            </div>
            <div class="col">
                <div class="label">NIS:</div>
                <div><?= $row['nis'] ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Kelas:</div>
                <div><?= $row['kelas'] ?></div>
            </div>
            <div class="col">
                <div class="label">Tanggal Kembali:</div>
                <div><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></div>
            </div>
        </div>

        <div style="margin: 20px 0;">
            <div class="label">Buku yang Dikembalikan:</div>
            <div style="margin-top: 5px;"><?= $row['buku_detail'] ?></div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Tanggal Pinjam:</div>
                <div><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></div>
            </div>
            <div class="col">
                <div class="label">Jatuh Tempo:</div>
                <div><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Keterlambatan:</div>
                <div><?= $row['telat'] ?> hari</div>
            </div>
            <div class="col">
                <div class="label">Denda:</div>
                <div style="color: <?= $row['denda'] > 0 ? 'red' : 'green' ?>; font-weight: bold;">
                    <?= $row['denda'] > 0 ? 'Rp ' . number_format($row['denda']) : 'Tidak ada' ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="label">Status Denda:</div>
                <div style="font-weight: bold; color: <?= $row['status_denda'] == 'Lunas' ? 'green' : 'orange' ?>;">
                    <?= $row['status_denda'] ?>
                </div>
            </div>
            <div class="col">
                <div class="label">Petugas:</div>
                <div><?= htmlspecialchars($row['nama_petugas']) ?></div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: right;">
            <div>Jakarta, <?= date('d F Y') ?></div>
            <div style="margin-top: 60px;">
                <div>(_______________________)</div>
                <div>Petugas Perpustakaan</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih telah menggunakan layanan perpustakaan SMK YMIK Jakarta</p>
        <p>Dokumen ini dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

<?php else: ?>
<script>
    alert('Data tidak ditemukan');
    window.close();
</script>
<?php endif; ?>
