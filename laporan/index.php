
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get statistics for reports
$total_buku = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
$total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
$total_peminjaman = $conn->query("SELECT COUNT(*) as total FROM peminjaman")->fetch_assoc()['total'];
$total_pengembalian = $conn->query("SELECT COUNT(*) as total FROM pengembalian")->fetch_assoc()['total'];

// Monthly stats
$bulan_ini = date('Y-m');
$peminjaman_bulan_ini = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE tanggal_pinjam LIKE '$bulan_ini%'")->fetch_assoc()['total'];
$pengembalian_bulan_ini = $conn->query("SELECT COUNT(*) as total FROM pengembalian WHERE tanggal_kembali LIKE '$bulan_ini%'")->fetch_assoc()['total'];

// Popular books
$buku_populer = $conn->query("SELECT b.judul, b.pengarang, COUNT(dp.id_buku) as total_pinjam 
                             FROM buku b 
                             LEFT JOIN detail_pinjam dp ON b.id_buku = dp.id_buku 
                             GROUP BY b.id_buku 
                             ORDER BY total_pinjam DESC 
                             LIMIT 5");

// Active students
$siswa_aktif = $conn->query("SELECT s.nama_siswa, s.kelas, COUNT(p.id_pinjam) as total_pinjam
                            FROM siswa s
                            LEFT JOIN peminjaman p ON s.id_siswa = p.id_siswa
                            GROUP BY s.id_siswa
                            ORDER BY total_pinjam DESC
                            LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Perpustakaan SMK YMIK Jakarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="modern-body">
    <?php include '../includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <main class="dashboard-main">
            <!-- Page Header -->
            <div class="welcome-header">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">
                            <i class="fas fa-chart-bar me-3"></i>
                            Laporan Perpustakaan
                        </h1>
                        <p class="welcome-subtitle">
                            Analisis dan statistik perpustakaan SMK YMIK Jakarta
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar me-2"></i>
                                <?= date('F Y') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-chart-line me-2"></i>
                                <?= number_format($peminjaman_bulan_ini) ?> Peminjaman Bulan Ini
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Print Laporan
                        </button>
                        <button class="btn btn-primary-custom" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>
                            Export Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-books"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_buku) ?></div>
                        <div class="stat-label">Total Buku</div>
                        <div class="stat-sublabel">Koleksi</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_siswa) ?></div>
                        <div class="stat-label">Total Siswa</div>
                        <div class="stat-sublabel">Anggota</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_peminjaman) ?></div>
                        <div class="stat-label">Total Peminjaman</div>
                        <div class="stat-sublabel">Transaksi</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_pengembalian) ?></div>
                        <div class="stat-label">Total Pengembalian</div>
                        <div class="stat-sublabel">Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Monthly Report & Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Laporan Bulan Ini
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="text-center p-3 rounded" style="background: rgba(102, 126, 234, 0.1); border: 2px solid rgba(102, 126, 234, 0.2);">
                                        <div class="display-6 fw-bold text-primary"><?= number_format($peminjaman_bulan_ini) ?></div>
                                        <small class="text-muted fw-semibold">Peminjaman</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 rounded" style="background: rgba(25, 135, 84, 0.1); border: 2px solid rgba(25, 135, 84, 0.2);">
                                        <div class="display-6 fw-bold text-success"><?= number_format($pengembalian_bulan_ini) ?></div>
                                        <small class="text-muted fw-semibold">Pengembalian</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Tingkat Pengembalian</span>
                                    <span class="badge bg-success"><?= $peminjaman_bulan_ini > 0 ? number_format(($pengembalian_bulan_ini / $peminjaman_bulan_ini) * 100, 1) : 0 ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?= $peminjaman_bulan_ini > 0 ? ($pengembalian_bulan_ini / $peminjaman_bulan_ini) * 100 : 0 ?>%; border-radius: 4px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="d-grid gap-3">
                                <button class="btn btn-outline-primary d-flex align-items-center justify-content-start" onclick="generateReport('buku')">
                                    <div class="me-3">
                                        <i class="fas fa-book" style="width: 20px; text-align: center;"></i>
                                    </div>
                                    <span>Laporan Buku</span>
                                </button>
                                <button class="btn btn-outline-success d-flex align-items-center justify-content-start" onclick="generateReport('siswa')">
                                    <div class="me-3">
                                        <i class="fas fa-users" style="width: 20px; text-align: center;"></i>
                                    </div>
                                    <span>Laporan Siswa</span>
                                </button>
                                <button class="btn btn-outline-warning d-flex align-items-center justify-content-start" onclick="generateReport('peminjaman')">
                                    <div class="me-3">
                                        <i class="fas fa-hand-holding" style="width: 20px; text-align: center;"></i>
                                    </div>
                                    <span>Laporan Peminjaman</span>
                                </button>
                                <button class="btn btn-outline-info d-flex align-items-center justify-content-start" onclick="generateReport('denda')">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle" style="width: 20px; text-align: center;"></i>
                                    </div>
                                    <span>Laporan Denda</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Books & Active Students -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-star me-2"></i>
                                Buku Terpopuler
                            </h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">RANK</th>
                                        <th>BUKU</th>
                                        <th style="width: 120px;" class="text-center">PEMINJAMAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    if ($buku_populer && $buku_populer->num_rows > 0):
                                        while ($row = $buku_populer->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="book-rank"><?= $rank ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="item-title"><?= htmlspecialchars($row['judul']) ?></div>
                                            <div class="item-subtitle"><?= htmlspecialchars($row['pengarang']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill fs-6"><?= $row['total_pinjam'] ?></span>
                                        </td>
                                    </tr>
                                    <?php 
                                        $rank++;
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-book mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                                <div>Belum ada data peminjaman</div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-user-graduate me-2"></i>
                                Siswa Teraktif
                            </h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">RANK</th>
                                        <th>SISWA</th>
                                        <th style="width: 120px;" class="text-center">PEMINJAMAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    if ($siswa_aktif && $siswa_aktif->num_rows > 0):
                                        while ($row = $siswa_aktif->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="book-rank"><?= $rank ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="item-title"><?= htmlspecialchars($row['nama_siswa']) ?></div>
                                            <div class="item-subtitle"><?= htmlspecialchars($row['kelas']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success rounded-pill fs-6"><?= $row['total_pinjam'] ?></span>
                                        </td>
                                    </tr>
                                    <?php 
                                        $rank++;
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-user-graduate mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                                <div>Belum ada data siswa aktif</div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateReport(type) {
            // Open report generation page in new window
            window.open('generate_report.php?type=' + type, '_blank', 'width=1024,height=768,scrollbars=yes');
        }

        function exportData() {
            // You can implement CSV/Excel export here
            const reportTypes = ['buku', 'siswa', 'peminjaman', 'denda'];
            const exportMenu = reportTypes.map(type => 
                `<a href="generate_report.php?type=${type}" target="_blank" style="display: block; padding: 5px 10px; text-decoration: none; color: #333;">${type.charAt(0).toUpperCase() + type.slice(1)}</a>`
            ).join('');
            
            const popup = window.open('', '_blank', 'width=300,height=200');
            popup.document.write(`
                <html>
                    <head><title>Export Data</title></head>
                    <body style="font-family: Arial, sans-serif; padding: 20px;">
                        <h3>Pilih Laporan untuk Export:</h3>
                        ${exportMenu}
                        <br>
                        <button onclick="window.close()" style="padding: 5px 15px;">Tutup</button>
                    </body>
                </html>
            `);
        }
    </script>
</body>
</html>
