
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$type = $_GET['type'] ?? '';
$db = new Database();
$conn = $db->getConnection();

function generateBukuReport($conn) {
    $query = "SELECT b.*, 
              COALESCE(COUNT(dp.id_buku), 0) as total_dipinjam,
              COALESCE(SUM(CASE WHEN p.status_pinjam = 'Dipinjam' THEN 1 ELSE 0 END), 0) as sedang_dipinjam
              FROM buku b 
              LEFT JOIN detail_pinjam dp ON b.id_buku = dp.id_buku 
              LEFT JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam
              GROUP BY b.id_buku 
              ORDER BY b.judul";
    return $conn->query($query);
}

function generateSiswaReport($conn) {
    $query = "SELECT s.*, 
              COALESCE(COUNT(p.id_pinjam), 0) as total_pinjam,
              COALESCE(SUM(CASE WHEN p.status_pinjam = 'Dipinjam' THEN 1 ELSE 0 END), 0) as sedang_pinjam
              FROM siswa s 
              LEFT JOIN peminjaman p ON s.id_siswa = p.id_siswa 
              GROUP BY s.id_siswa 
              ORDER BY s.nama_siswa";
    return $conn->query($query);
}

function generatePeminjamanReport($conn) {
    $query = "SELECT p.*, s.nama_siswa, s.nis, s.kelas, pet.nama_petugas,
              GROUP_CONCAT(b.judul SEPARATOR ', ') as buku_dipinjam,
              CASE 
                WHEN p.status_pinjam = 'Dipinjam' AND p.tanggal_jatuh_tempo < CURDATE() 
                THEN DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo)
                ELSE 0 
              END as hari_terlambat
              FROM peminjaman p
              JOIN siswa s ON p.id_siswa = s.id_siswa
              JOIN petugas pet ON p.id_petugas = pet.id_petugas
              JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
              JOIN buku b ON dp.id_buku = b.id_buku
              GROUP BY p.id_pinjam
              ORDER BY p.tanggal_pinjam DESC";
    return $conn->query($query);
}

function generateDendaReport($conn) {
    $query = "SELECT pg.*, p.tanggal_pinjam, p.tanggal_jatuh_tempo, 
              s.nama_siswa, s.nis, s.kelas,
              GROUP_CONCAT(b.judul SEPARATOR ', ') as buku_dikembalikan
              FROM pengembalian pg
              JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam
              JOIN siswa s ON p.id_siswa = s.id_siswa
              JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
              JOIN buku b ON dp.id_buku = b.id_buku
              WHERE pg.denda > 0
              GROUP BY pg.id_kembali
              ORDER BY pg.tanggal_kembali DESC";
    return $conn->query($query);
}

if (!$type) {
    header('Location: index.php');
    exit();
}

switch ($type) {
    case 'buku':
        $result = generateBukuReport($conn);
        $title = 'Laporan Data Buku';
        break;
    case 'siswa':
        $result = generateSiswaReport($conn);
        $title = 'Laporan Data Siswa';
        break;
    case 'peminjaman':
        $result = generatePeminjamanReport($conn);
        $title = 'Laporan Peminjaman';
        break;
    case 'denda':
        $result = generateDendaReport($conn);
        $title = 'Laporan Denda';
        break;
    default:
        header('Location: index.php');
        exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Perpustakaan SMK YMIK Jakarta</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            border-bottom: 3px solid #333; 
            padding-bottom: 15px; 
            margin-bottom: 25px; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 18px; 
            color: #333;
        }
        .header h2 { 
            margin: 5px 0; 
            font-size: 16px; 
            color: #666;
        }
        .header p { 
            margin: 5px 0; 
            color: #888;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .summary-label {
            color: #666;
            font-size: 11px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .header { border-bottom: 2px solid #000; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PERPUSTAKAAN SMK YMIK JAKARTA</h1>
        <h2><?= $title ?></h2>
        <p>Jl. Dewi Sartika No. 25, Jakarta Timur</p>
    </div>

    <div class="report-info">
        <div>
            <strong>Tanggal Cetak:</strong> <?= date('d F Y H:i:s') ?>
        </div>
        <div>
            <strong>Dicetak oleh:</strong> <?= htmlspecialchars($_SESSION['nama_petugas']) ?>
        </div>
    </div>

    <?php if ($type == 'buku'): ?>
        <?php
        $total_buku = 0;
        $total_stok = 0;
        $total_dipinjam = 0;
        ?>
        <div class="summary">
            <h3>Ringkasan Data Buku</h3>
            <div class="summary-grid">
                <?php 
                // Calculate totals
                $temp_result = generateBukuReport($conn);
                while ($temp_row = $temp_result->fetch_assoc()) {
                    $total_buku++;
                    $total_stok += $temp_row['stok'];
                    $total_dipinjam += $temp_row['total_dipinjam'];
                }
                ?>
                <div class="summary-item">
                    <div class="summary-number"><?= $total_buku ?></div>
                    <div class="summary-label">Total Judul Buku</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number"><?= $total_stok ?></div>
                    <div class="summary-label">Total Stok</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number"><?= $total_dipinjam ?></div>
                    <div class="summary-label">Total Peminjaman</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Judul Buku</th>
                    <th width="15%">ISBN</th>
                    <th width="15%">Pengarang</th>
                    <th width="15%">Penerbit</th>
                    <th width="8%">Tahun</th>
                    <th width="8%">Stok</th>
                    <th width="9%">Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= htmlspecialchars($row['isbn']) ?></td>
                    <td><?= htmlspecialchars($row['pengarang']) ?></td>
                    <td><?= htmlspecialchars($row['penerbit']) ?></td>
                    <td class="text-center"><?= $row['tahun_terbit'] ?></td>
                    <td class="text-center"><?= $row['stok'] ?></td>
                    <td class="text-center"><?= $row['total_dipinjam'] ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data buku</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type == 'siswa'): ?>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">NIS</th>
                    <th width="25%">Nama Siswa</th>
                    <th width="10%">Kelas</th>
                    <th width="15%">Jurusan</th>
                    <th width="15%">Kontak</th>
                    <th width="10%">Total Pinjam</th>
                    <th width="5%">Aktif</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= $row['nis'] ?></td>
                    <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td class="text-center"><?= $row['kelas'] ?></td>
                    <td><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td><?= htmlspecialchars($row['kontak']) ?></td>
                    <td class="text-center"><?= $row['total_pinjam'] ?></td>
                    <td class="text-center">
                        <?php if ($row['sedang_pinjam'] > 0): ?>
                            <span class="badge badge-warning">Ya</span>
                        <?php else: ?>
                            <span class="badge badge-success">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data siswa</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type == 'peminjaman'): ?>
        <table>
            <thead>
                <tr>
                    <th width="8%">ID Pinjam</th>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Siswa</th>
                    <th width="25%">Buku</th>
                    <th width="12%">Jatuh Tempo</th>
                    <th width="10%">Status</th>
                    <th width="8%">Terlambat</th>
                    <th width="7%">Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td class="text-center">#<?= str_pad($row['id_pinjam'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                    <td>
                        <?= htmlspecialchars($row['nama_siswa']) ?><br>
                        <small><?= $row['nis'] ?> - <?= $row['kelas'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['buku_dipinjam']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                    <td class="text-center">
                        <?php if ($row['status_pinjam'] == 'Dipinjam'): ?>
                            <?php if ($row['hari_terlambat'] > 0): ?>
                                <span class="badge badge-danger">Terlambat</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Dipinjam</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-success">Kembali</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= $row['hari_terlambat'] > 0 ? $row['hari_terlambat'] . ' hari' : '-' ?>
                    </td>
                    <td><?= htmlspecialchars($row['nama_petugas']) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data peminjaman</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type == 'denda'): ?>
        <?php
        $total_denda = 0;
        $denda_lunas = 0;
        $denda_belum = 0;
        ?>
        <table>
            <thead>
                <tr>
                    <th width="8%">ID</th>
                    <th width="12%">Tanggal Kembali</th>
                    <th width="18%">Siswa</th>
                    <th width="25%">Buku</th>
                    <th width="8%">Terlambat</th>
                    <th width="12%">Denda</th>
                    <th width="12%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                        $total_denda += $row['denda'];
                        if ($row['status_denda'] == 'Lunas') {
                            $denda_lunas += $row['denda'];
                        } else {
                            $denda_belum += $row['denda'];
                        }
                ?>
                <tr>
                    <td class="text-center"><?= $row['id_kembali'] ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
                    <td>
                        <?= htmlspecialchars($row['nama_siswa']) ?><br>
                        <small><?= $row['nis'] ?> - <?= $row['kelas'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['buku_dikembalikan']) ?></td>
                    <td class="text-center"><?= $row['telat'] ?> hari</td>
                    <td class="text-right">Rp <?= number_format($row['denda']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= $row['status_denda'] == 'Lunas' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $row['status_denda'] ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="5" class="text-right">TOTAL DENDA:</td>
                    <td class="text-right">Rp <?= number_format($total_denda) ?></td>
                    <td class="text-center">-</td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data denda</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_denda > 0): ?>
        <div class="summary">
            <h3>Ringkasan Denda</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-number">Rp <?= number_format($total_denda) ?></div>
                    <div class="summary-label">Total Denda</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number">Rp <?= number_format($denda_lunas) ?></div>
                    <div class="summary-label">Sudah Dibayar</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number">Rp <?= number_format($denda_belum) ?></div>
                    <div class="summary-label">Belum Dibayar</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="footer">
        <div>
            <p><strong>PERPUSTAKAAN SMK YMIK JAKARTA</strong></p>
            <p>Jl. Dewi Sartika No. 25, Jakarta Timur | Telp: (021) 123-4567</p>
            <p>Email: perpustakaan@smkymikjkt.sch.id | Website: www.smkymikjkt.sch.id</p>
        </div>
        <div style="margin-top: 15px;">
            <p>Dokumen ini dicetak pada: <?= date('d F Y H:i:s') ?> WIB</p>
            <p>Petugas: <?= htmlspecialchars($_SESSION['nama_petugas']) ?></p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; margin-right: 10px;">
            <i class="fas fa-print"></i> Print Laporan
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px;">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Optional: uncomment to auto print
            // window.print();
        }
    </script>
</body>
</html>
